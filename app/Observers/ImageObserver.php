<?php

namespace App\Observers;

use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    public function deleted(Image $image)
    {
        // 删除图片
        $driver = config('filesystems.admin');
        if (Storage::disk($driver)->exists($image->url) && !Image::query()->where('url', $image->url)->exists()) {
            Storage::disk($driver)->delete($image->url);
        }
    }
}
