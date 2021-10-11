<?php
/**
 * 模型基类
 */

namespace App\Models;

use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\BaseScopeTrait;
use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class BaseModel extends Model
{
    // 组合查询可以限制数量
    use HasEagerLimit;

    // 搜索辅助
    use BaseScopeTrait;

    // 基本配置
    use BaseModelTrait;

    protected $guarded = [];

    /**
     * 切换字段的 bool 值
     * @param $column
     * @param BackendUser|null $user
     * @return $this
     */
    public function toggleColumn($column, ?BackendUser $user): BaseModel
    {
        $data = [
            $column => !$this->$column,
        ];
        if (\Schema::hasColumn($this->getTable(), 'backend_user_id')) {
            $data['backend_user_id'] = optional($user)->id ?? 0;
        }
        $this->update($data);
        return $this;
    }
}
