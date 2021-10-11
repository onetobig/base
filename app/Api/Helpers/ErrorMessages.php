<?php


namespace App\Api\Helpers;


use App\Models\Activity;
use App\Models\BeanProduct;
use App\Models\Book;
use App\Models\Car;
use App\Models\Category;
use App\Models\ClockIn;
use App\Models\ClockInDynamic;
use App\Models\Coupon;
use App\Models\CustomZone;
use App\Models\Express;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Zone;

trait ErrorMessages
{
    /**
     * 资源 404 错误
     * @param $key
     * @return mixed|string
     */
    public function notFoundMessages($key)
    {
        $msg = [
            User::class => '用户信息不存在',
            Category::class => '分类信息不存在、或已被删除',
            Book::class => '图书已删除',
            Zone::class => '专区已删除',
            BeanProduct::class => '乐豆商品已删除',
            CustomZone::class => '自定义专区已删除',
            ClockInDynamic::class => '读书感悟已删除',
            ClockIn::class => '打卡已删除',
            Activity::class => '活动已删除',
            Coupon::class => '优惠券已删除',
            Express::class => '物流公司已删除',
            UserAddress::class => '收货地址已删除',
            Car::class => '书籍已从书袋删除',
        ];
        return $msg[$key] ?? '资源不存在';
    }


    public function throttleMessage($key)
    {
        $msg = [
            'praises.store' => '每分钟最多评论一次，请过一分钟后再操作',
            'studies.reviews.store' => '每分钟最多留言一次，请过一分钟后再操作',
        ];

        return $msg[$key] ?? '请求频率过高';
    }
}
