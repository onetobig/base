<?php
/**
 * 模型基本配置
 */

namespace App\Models\Traits;

use Carbon\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity as ActivityLog;
use Spatie\Activitylog\Traits\LogsActivity;

trait BaseModelTrait
{
    // 日志记录变化
    use LogsActivity;

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }

    /**
     * json 中文入库不编码
     *
     * @param [type] $value
     * @return false|string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }


    // 改写 fill 方法，用对应的函数过滤
    public function fill(array $attributes)
    {
        $attributes = $this->formatFillData($attributes);
        return parent::fill($attributes);
    }

    /**
     * 过滤 null ，按照 $cats 转为对应的类型
     * @param array $data
     * @return array
     */
    public function formatFillData(array $data)
    {
        $cats = $this->getCasts();
        if (!$cats) {
            return $data;
        }

        foreach ($cats as $k => $t) {
            if (!key_exists($k, $data)) {
                continue;
            }

            switch ($t) {
                case 'int':
                case 'integer':
                    $data[$k] = (int)$data[$k];
                    break;
                case 'string':
                    $data[$k] = (string)$data[$k];
                    break;
                case 'bool':
                case 'boolean':
                    $data[$k] = (bool)$data[$k];
                    break;
                case 'datetime':
                    if ($data[$k]) {
                        $data[$k] = Carbon::createFromTimestamp(strtotime($data[$k]));
                    }
                    break;
                case 'json':
                    if (!$data[$k]) {
                        $data[$k] = [];
                    }
                    break;
            }
        }
        return $data;
    }

    public function scopeEnabled($query)
    {
        $query->where('enabled', true);
    }

    public function toArray()
    {
        $res = parent::toArray();
        if (!isset($res['checked'])) {
            $res['checked'] = false;
        }
        $cats = $this->getCasts();
        $dates = $this->getDates();
        foreach ($res as $k => $v) {
            if ($v !== null) {
                continue;
            }
            if (in_array($k, $dates) || (isset($cats[$k]) && $cats[$k] === 'datetime')) {
                $res[$k] = (string) $v;
            }
        }
        return $res;
    }

    /**
     * 模型日志通用配置
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly([])
            ->useLogName('table_' . $this->getTable());
    }

    public function tapActivity(ActivityLog $activity, string $eventName)
    {
        if ($user = request()->user()) {
            $activity->causer()->associate($user);
        }
    }
}
