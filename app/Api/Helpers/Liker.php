<?php

namespace App\Api\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Overtrue\LaravelLike\Traits\Liker as BaseLiker;


trait Liker
{
    use BaseLiker;

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
}
