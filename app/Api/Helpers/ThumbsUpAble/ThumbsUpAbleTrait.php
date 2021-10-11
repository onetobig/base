<?php


namespace App\Api\Helpers\ThumbsUpAble;


use App\Api\Helpers\ThumbsUpAble\Jobs\ThumbsUpJob;
use App\Jobs\Common\ThumbsUp;
use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Redis;

/**
 * @param string $thumbsColumn
 * Trait ThumbsUpAbleTrait
 * @package App\Api\Helpers\ThumbsUpAble
 */
trait ThumbsUpAbleTrait
{
    /**
     * @var array
     */
//    protected $thumbsUpAble = [
//        'count_column' => 'like_num', // 记录点赞数量的字段
//        'init_count_column' => 'init_like_num', // 记录初始点赞数量的字段
//    ];

    /**
     * 点赞的缓存键
     * @return string
     */
    public function getThumbsUpKey()
    {
        $key = 'new_thumbs_up:' . static::getTable() . ':' . $this->getKey();
        return $key;
    }

    /**
     * 点赞
     * @param $user_id
     * @return mixed
     */
    public function thumbsUp($user_id)
    {
        if (!$user_id) {
            return 0;
        }
        $new_key = $this->getThumbsUpKey();
        Redis::sadd($new_key, $user_id);

        dispatch(new ThumbsUpJob($this, $user_id));
        return $this->thumbsUpCount;
    }

    public function hasThumbsUp($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = $this->getThumbsUpKey();
        return (bool) Redis::sismember($key, $user_id);
    }

    /**
     * 取消点赞
     * @param $user_id
     * @return mixed
     */
    public function unThumbsUp($user_id)
    {
        if (!$user_id) {
            return 0;
        }
        $key = $this->getThumbsUpKey();

        Redis::srem($key, $user_id);
        dispatch(new ThumbsUpJob($this, $user_id));

        return $this->thumbsUpCount;
    }

    /**
     * 获取点赞数量，优先取缓存
     * @return int
     */
    public function getThumbsUpCountAttribute()
    {
        $count = 0;

        // 取缓存
        $count += Redis::scard($this->getThumbsUpKey());

        // 取初始化量
        $count += $this->attributes[$this->getInitThumbsUpCountName()] ?? 0;

        return $count;
    }

    /**
     * 点赞文字
     * @return int
     */
    public function getThumbsUpCountTextAttribute()
    {
        $count = $this->thumbs_up_count;
        if ($count > 1000) {
            $count = number_format($count, 1) . '万';
        }
        return $count;
    }
    /**
     * 获取点赞数量字段名
     * @return string
     */
    protected function getThumbsUpColumnName()
    {
        if (isset($this->thumbsUpAble['count_column']) && !empty($this->thumbsUpAble['count_column'])) {
            return $this->thumbsUpAble['count_column'];
        }
        return '';
    }

    /**
     * 获取点赞数量初始值
     * @return string
     */
    protected function getInitThumbsUpCountName()
    {
        if (isset($this->thumbsUpAble['init_count_column']) && !empty($this->thumbsUpAble['init_count_column'])) {
            return $this->thumbsUpAble['init_count_column'];
        }
        return null;
    }

    /**
     * 点赞数量缓存键
     * @return string
     */
    public function getThumbsUpCountKey()
    {
        return $this->getThumbsUpKey() . ':count';
    }

    /**
     * 同步点赞数量到数据库
     * @return bool
     * @throws \Exception
     */
    public function syncThumbsUpCountToDatabase()
    {
        $lock_key = ($this->getThumbsUpKey() . ':lock');
        try {
            $lock = cache()->lock($lock_key, 10);
            // 加锁
            $lock->block(10);
            // 重新缓存
            $count = Redis::scard($this->getThumbsUpKey());
            $column = $this->getThumbsUpColumnName();
            if ($column) {
                $this->update([
                    $column => $count,
                ]);
            }
            // 释放锁
            $lock->release();
        } catch (LockTimeoutException $e) {
            return false;
        } finally {
            optional($lock)->release();
        }
    }
}
