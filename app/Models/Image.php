<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Image extends Model
{

    public static $categoryMap = [
        'age-3-5' => '音乐进阶 3-5 岁',
        'age-5-7' => '音乐进阶 5-7 岁',
        'age-7-9' => '音乐进阶 7-9 岁',
        'age-9-12' => '音乐进阶 9-12 岁',
        'day-1-2' => '音乐剧 1-2 天',
        'day-3-4' => '音乐剧 3-4 天',
        'day-5-6' => '音乐剧 5-6 天',
    ];

    public function getUrlInfoAttribute()
    {
        if (Str::startsWith($this->attributes['url'], ['http://', 'https://'])) {
            return $this->attributes['url'];
        }
        return Storage::disk('admin')->url($this->attributes['url']);
    }

    public function setPicturesAttribute($pictures)
    {
        if (is_array($pictures)) {
            $this->attributes['pictures'] = json_encode($pictures);
        }
    }
}
