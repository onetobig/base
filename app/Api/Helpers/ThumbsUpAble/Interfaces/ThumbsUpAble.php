<?php


namespace App\Api\Helpers\ThumbsUpAble\Interfaces;

use App\Jobs\Common\ThumbsUp;
use Illuminate\Support\Facades\Redis;

interface ThumbsUpAble
{
    public function getThumbsUpKey();
    public function thumbsUp($user_id);
    public function unThumbsUp($user_id);
    public function getThumbsUpCountAttribute();
    public function getThumbsUpCountTextAttribute();
    public function syncThumbsUpCountToDatabase();
    public function getThumbsUpCountKey();

    public function hasThumbsUp($user_id);
}
