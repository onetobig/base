<?php

namespace App\Api\Helpers;

use ArrayAccess;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

trait SortableTrait
{
    public static function bootSortableTrait()
    {
        static::created(function ($model) {
            $orderColumnName = $model->determineOrderColumnName();
            if (!$model->$orderColumnName) {
                $model->$orderColumnName = $model->id;
                $model->save();
            }
        });
    }

    public function setHighestOrderNumber()
    {
        $orderColumnName = $this->determineOrderColumnName();
        $this->$orderColumnName = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        switch ($this->getDirection()) {
            case 'asc':
                return (int)$this->buildSortQuery()->max($this->determineOrderColumnName());
            case 'desc':
                return (int)$this->buildSortQuery()->min($this->determineOrderColumnName());
        }
    }

    /**
     * 默认倒序
     * @return string
     * @author: Onetobig
     * @Time: 2021/8/13 11:14
     */
    public function getDirection()
    {
        if (isset($this->sortable['direction']) &&
            !empty($this->sortable['direction'])
        ) {
            return $this->sortable['direction'];
        }
        return 'desc';
    }

    public function scopeOrdered(Builder $query, string $direction = null)
    {
        if ($direction === null) {
            $direction = $this->getDirection();
        }

        return $query->orderBy($this->determineOrderColumnName(), $direction);
    }

    public static function setNewOrder($ids, int $startOrder = 1, string $primaryKeyColumn = null)
    {
        if (!is_array($ids) && !$ids instanceof ArrayAccess) {
            throw new InvalidArgumentException('You must pass an array or ArrayAccess object to setNewOrder');
        }

        $model = new static;

        $orderColumnName = $model->determineOrderColumnName();

        if (is_null($primaryKeyColumn)) {
            $primaryKeyColumn = $model->getKeyName();
        }

        $orders = $model->whereIn('id', $ids)
            ->select([
                $orderColumnName
            ])
            ->ordered()
            ->get()
            ->pluck($orderColumnName)
            ->toArray();
        if (count($orders) !== count($ids)) {
            throw new InvalidArgumentException('Some of ids not exists in table');
        }

        foreach ($ids as $id) {
            $newOrder = array_shift($orders);
            static::withoutGlobalScope(SoftDeletingScope::class)
                ->where($primaryKeyColumn, $id)
                ->update([$orderColumnName => $newOrder]);
        }
    }

    public static function setNewOrderByCustomColumn(string $primaryKeyColumn, $ids, int $startOrder = 1)
    {
        self::setNewOrder($ids, $startOrder, $primaryKeyColumn);
    }

    public function determineOrderColumnName(): string
    {
        if (isset($this->sortable['order_column_name']) &&
            !empty($this->sortable['order_column_name'])
        ) {
            return $this->sortable['order_column_name'];
        }

        return 'order_column';
    }

    protected function determineFilterColumnColumnName()
    {
        if (isset($this->sortable['filter_column_name']) &&
            !empty($this->sortable['filter_column_name'])
        ) {
            return $this->sortable['filter_column_name'];
        }

        return '';
    }

    protected function determineMoveColumnColumnName()
    {
        if (isset($this->sortable['move_column_name']) &&
            !empty($this->sortable['move_column_name'])
        ) {
            return $this->sortable['move_column_name'];
        }

        return '';
    }

    /**
     * Determine if the order column should be set when saving a new model instance.
     */
    public function shouldSortWhenCreating(): bool
    {
        return $this->sortable['sort_when_creating'] ?? true;
    }

    public function moveOrderDown()
    {
        $orderColumnName = $this->determineOrderColumnName();

        $moveColumnName = $this->determineMoveColumnColumnName();

        switch ($this->getDirection()) {
            case 'desc':
                $query = $this->buildSortQuery()->limit(1)
                    ->ordered('desc')
                    ->where($orderColumnName, '<', $this->$orderColumnName);
                break;
            case 'asc':
                $query = $this->buildSortQuery()->limit(1)
                    ->ordered('asc')
                    ->where($orderColumnName, '>', $this->$orderColumnName);
                break;
        }

        if ($moveColumnName) {
            if (is_array($moveColumnName)) {
                foreach ($moveColumnName as $column) {
                    $query->where($column, $this->$column);
                }
            } else {
                $query->where($moveColumnName, $this->$moveColumnName);
            }
        }

        $swapWithModel = $query->first();

        if (!$swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    public function moveOrderUp()
    {
        $orderColumnName = $this->determineOrderColumnName();
        $moveColumnName = $this->determineMoveColumnColumnName();

        switch ($this->getDirection()) {
            case 'desc':
                $query = $this->buildSortQuery()->limit(1)
                    ->ordered('asc')
                    ->where($orderColumnName, '>', $this->$orderColumnName);
                break;
            case 'asc':
                $query = $this->buildSortQuery()->limit(1)
                    ->ordered('desc')
                    ->where($orderColumnName, '<', $this->$orderColumnName);
                break;
        }

        if ($moveColumnName) {
            if (is_array($moveColumnName)) {
                foreach ($moveColumnName as $column) {
                    $query->where($column, $this->$column);
                }
            } else {
                $query->where($moveColumnName, $this->$moveColumnName);
            }
        }

        $swapWithModel = $query->first();

        if (!$swapWithModel) {
            return $this;
        }

        return $this->swapOrderWithModel($swapWithModel);
    }

    /**
     * @param Sortable $otherModel
     * @return $this
     * @author Administrator
     * @date 2021-04-20 17:57
     */
    public function swapOrderWithModel($otherModel)
    {
        $orderColumnName = $this->determineOrderColumnName();

        $oldOrderOfOtherModel = $otherModel->$orderColumnName;

        $otherModel->$orderColumnName = $this->$orderColumnName;
        $otherModel->save();

        $this->$orderColumnName = $oldOrderOfOtherModel;
        $this->save();

        return $this;
    }

    public static function swapOrder($model, $otherModel)
    {
        $model->swapOrderWithModel($otherModel);
    }

    public function moveToStart()
    {
        $firstModel = $this->buildSortQuery()
            ->limit(1)
            ->ordered()
            ->first();

        $orderColumnName = $this->determineOrderColumnName();

        if ($firstModel->$orderColumnName === $this->$orderColumnName) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $firstModel->$orderColumnName;
        $this->save();


        switch ($this->getDirection()) {
            case 'asc':
                $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->id)
                    ->where($orderColumnName, '<', $oldOrder)
                    ->increment($orderColumnName);
                break;
            case 'desc':
                $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->id)
                    ->where($orderColumnName, '>', $oldOrder)
                    ->decrement($orderColumnName);
                break;
        }

        return $this;
    }

    public function moveToEnd()
    {
        $maxOrder = $this->getHighestOrderNumber();

        $orderColumnName = $this->determineOrderColumnName();

        if ($this->$orderColumnName === $maxOrder) {
            return $this;
        }

        $oldOrder = $this->$orderColumnName;

        $this->$orderColumnName = $maxOrder;
        $this->save();

        switch ((new Static)->getDirection()) {
            case 'asc':
                $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->id)
                    ->where($orderColumnName, '>', $oldOrder)
                    ->decrement($orderColumnName);
                break;
            case 'desc':
                $this->buildSortQuery()->where($this->getKeyName(), '!=', $this->id)
                    ->where($orderColumnName, '<', $oldOrder)
                    ->increment($orderColumnName);
                break;
        }


        return $this;
    }

    public function buildSortQuery()
    {
        $query = static::query();
        $filter_column_name = $this->determineFilterColumnColumnName();
        if ($filter_column_name) {
            if (is_array($filter_column_name)) {
                foreach ($filter_column_name as $column) {
                    $query->where($column, $this->$column);
                }
            } else {
                $query->where($filter_column_name, $this->$filter_column_name);
            }
        }
        return $query;
    }
}
