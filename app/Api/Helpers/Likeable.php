<?php

namespace App\Api\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Overtrue\LaravelLike\Traits\Likeable as BaseLikeable;

trait Likeable
{
    use BaseLikeable;

    /*
    public function hasLiked(Model $object)
    {
        $value = $object->getMorphClass() . ':' . $object->getKey() . ':' . $this->getKey();
        return (bool)Redis::sIsMember(RedisKey::LIKES_TABLE, $value);
    }

    public function hadLiked($model, $model_id)
    {
        $value = $model . ':' . $model_id . ':' . $this->getKey();
        return (bool)Redis::sIsMember(RedisKey::LIKES_TABLE, $value);
    }
    */

    /**
     * 当前登录用户和动态的点赞关系
     * @author: Onetobig
     * @Time: 2021/9/11 16:20
     */
    public function curLiker()
    {
        $user_id = optional(request()->user())->id;
        return $this->hasOneThrough(
            config('auth.providers.users.model'),
            config('like.like_model'),
            'likeable_id',
            'id', // users.id
            'id', // where likeable_id in ()
            'user_id', // likes.user_id
        )
            ->where('likeable_type', $this->getMorphClass())
            ->where('user_id', $user_id);
    }
}
