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

}
