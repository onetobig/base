<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/15
 * Time: 09:33
 */

namespace App\Api\Helpers;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

trait PaginateHelper
{
    /**
     * 自定义数据的分页
     * @param array $data
     * @param int $per_page
     * @return mixed
     */
    public function customData(array $data, $per_page = 10)
    {
        $per_page = (int)$per_page;
        if ($per_page <= 0) {
            $per_page = 10;
        }
        $page = request()->input('page', 1);
        $total = count($data);
        $codes = collect($data)->forPage($page, $per_page);
        $codes = new LengthAwarePaginator($codes, $total, $per_page);
        return $this->paginate($codes);
    }

    protected function paginate(Paginator $data, $resource = null, ?array $only = null, ?array $hidden = null, ?array $append = null)
    {
        if ($append && $only) {
            $only = array_merge($append, $only);
        }
        $res['data'] = [];
        // api 资源处理
        if ($resource && class_exists($resource) && new $resource([]) instanceof JsonResource) {
            $res['data'] = $resource::collection($data)->resolve();
            $data = $data->toArray();
        } else {
            if ($append) {
                $items = $data->items();
                foreach ($items as $item) {
                    if ($append && is_object($item) && method_exists($item, 'setAppends')) {
                        $item->setAppends($append);
                    }
                    $res['data'][] = $item->toArray();
                }
            } else {
                $res['data'] = $data->items();
            }
            $data = $data->toArray();
        }

        if ($only) {
            $res['data'] = [];
            foreach ($data['data'] as $item) {
                $res['data'][] = Arr::only($item, $only);
            }
        }

        if ($hidden) {
            $res['data'] = [];
            foreach ($data['data'] as $item) {
                $res['data'][] = Arr::except($item, $hidden);
            }
        }

        /*
        $res['links'] = [
            "first" => $data['first_page_url'],
            'last' => $data['last_page_url'],
            'prev' => $data['prev_page_url'],
            'next' => $data['next_page_url'],
        ];
        */
        $res['meta'] = [
            'form' => $data['from'],
            'to' => $data['to'],
            "current_page" => $data['current_page'],
            'last_page' => $data['last_page'],
            // 'path' => $data['path'],
            'total' => $data['total'],
            'per_page' => $data['per_page'],
        ];
        return $res;
    }

    /**
     * union 查询分页
     * @param Builder $query
     * @return LengthAwarePaginator
     */
    private function unionPaginate($query, $per_page = 20)
    {
        $count = $query->count();
        $page = request()->page;
        $res = new LengthAwarePaginator(
            $query->forPage($page, $per_page)->get(),
            $count,
            $per_page,
            $page
        );
        return $res;
    }
}
