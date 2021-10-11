<?php
/**
 * 用户
 */

namespace App\Models;

use App\Api\Helpers\Liker;
use App\Api\Helpers\RedisKey;
use App\Models\Traits\BaseModelTrait;
use App\Models\Traits\BaseScopeTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Api\Helpers\HasApiTokens;
use Illuminate\Support\Facades\Redis;
use App\Models\Traits\Visitable\Visitor;

class User extends Authenticatable
{
    use Visitor;
    use SoftDeletes;
    use Liker;
    use Notifiable;
    use HasApiTokens;
    use HasFactory;
    use BaseScopeTrait;
    use BaseModelTrait;

    protected static $availableNoKey = 'user:available:no';

    protected $guarded = [];

    const GENDER_UNKONOW = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    public static $genderMap = [
        self::GENDER_UNKONOW => '未知',
        self::GENDER_MALE => '男',
        self::GENDER_FEMALE => '女',
    ];

    const TYPE_NORMAL = 10;
    const TYPE_SELLER = 20;
    const TYPE_AREA_PARTNER = 30;
    const TYPE_PROMOTER = 40;
    const TYPE_COMPANY_READER = 50;

    public static $typeMap = [
        self::TYPE_NORMAL => '普通用户',
        self::TYPE_SELLER => '销售员',
        self::TYPE_AREA_PARTNER => '区域合伙人',
        self::TYPE_PROMOTER => '图书推广大使',
        self::TYPE_COMPANY_READER => '企业读书员',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $casts = [
        'email_verified' => 'boolean',
        'phone' => 'string',
        'signing' => 'string',
        'enable' => 'boolean',
        'settings' => 'json',
        'bean' => 'float',
    ];

    protected $dates = ['vip_end_at'];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
        'openid',
        'union_id',
        'login_times',
        'max_login_times',
        'last_login_at',
        'login_at',
        'ip',
        'settings',
    ];

    public static function booted()
    {
        self::saved(function (User $user) {
            if (!$user->no) {
                $user->no = $user->id . '-u' . strtolower(str_random(5));
                $user->save();
            }
        });
    }
}
