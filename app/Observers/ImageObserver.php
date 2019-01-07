<?php

namespace App\Observers;

use App\Handlers\ImageUploadHandler;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageObserver
{
    public function deleted(Image $image)
    {
        // 删除图片
        if (Storage::disk('admin')->exists($image->url) && !Image::query()->where('url', $image->url)->exists()) {
            Storage::disk('admin')->delete($image->url);
        }
    }

    public function saved(Image $image)
    {
        if (Storage::disk('admin')->exists($image->url)) {
            $path = Storage::disk('admin')->path($image->url);
            app(ImageUploadHandler::class)->reduceSize($path, 676);
        }
    }
}
