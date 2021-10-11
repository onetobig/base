<?php


namespace App\Models\Traits;


trait BaseScopeTrait
{
    /**
     * 更新时间区间查询
     * @param $query
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function scopeUpdatedAtBetween($query, $start = '', $end = '')
    {
        return $this->scopeTimeBetween($query, 'updated_at', $start, $end);
    }


    /**
     * 创建时间查询
     * @param $query
     * @param string $start
     * @param string $end
     * @return mixed
     */
    public function scopeCreatedAtBetween($query, $start = '', $end = '')
    {
        return $this->scopeTimeBetween($query, 'created_at', $start, $end);
    }


    /**
     * 时间区间查询
     * @param $query
     * @param $column
     * @param string $start
     * @param string $end
     * @return mixed
     * @author: Onetobig
     * @Time: 2021/8/7   3:37 下午'
     */
    public function scopeTimeBetween($query, $column, $start = '', $end = '')
    {
        if ($start) {
            $start = get_format_date($start);
        }
        if ($end) {
            $end = $this->getEndTime($end);
        }
        return $this->scopeColumnBetween($query, $column, $start, $end);
    }

    /**
     * 字段区间查询
     * @param $query
     * @param $column
     * @param string $start
     * @param string $end
     * @param null $conversion
     * @return mixed
     * @author: Onetobig
     * @Time: 2021/8/7 3:39 下午
     */
    public function scopeColumnBetween($query, $column, $start = '', $end = '', $conversion = null)
    {
        switch ($conversion) {
            case 'float':
            case 'double':
                $start = $start === '' ? $start : (double)$start;
                $end = $end === '' ? $end : (double)$end;
                break;
            case 'int':
            case 'integer':
                $start = $start === '' ? $start : (int)$start;
                $end = $end === '' ? $end : (int)$end;
                break;
            case 'string':
                $start = $start === '' ? $start : (string)$start;
                $end = $end === '' ? $end : (string)$end;
                break;
        }
        if ($start !== '' && $end !== '') {
            return $query->whereBetween($column, [$start, $end]);
        }

        if ($start !== '') {
            $query->where($column, '>=', $start);
        }
        if ($end !== '') {
            $query->where($column, '<=', $end);
        }
        return $query;
    }

    /**
     * 结束时间格式化
     * @param $value
     * @return \Carbon\Carbon
     * @author: Onetobig
     * @Time: 2021/8/7 15:48
     */
    protected function getEndTime($value)
    {
        $value = get_format_date($value);
        $not_time = $value->minute + $value->second + $value->hour == 0;
        if ($not_time) {
            $value = $value->endOfDay();
        }
        return $value;
    }

    /**
     * 字段模糊查询，支持关系查询
     * @param $query
     * @param array $columns
     * @param $value
     * @return mixed
     */
    public function scopeColumnLike($query, array $columns, $value)
    {
        $value = "%{$value}%";
        return $query->where(function ($query) use($columns, $value) {
            $i = 0;
            $self_table = $this->getTable();
            foreach ($columns as $column) {
                ++$i;

                // 识别表和字段
                $column = explode('.', $column);
                $table = $column[1] ?? '';
                $column = $column[0];
                if ($table) {
                    $sw = $column;
                    $column = $table;
                    $table = $sw;
                }

                if ($table && $table !== $self_table) {
                    $query->orWhereHas($table, function ($qeury) use($column, $value) {
                        $qeury->where($column, 'like', $value);
                    });
                } else {
                    $query->orWhere($column, 'like', $value);
                }
            }
        });
    }
}
