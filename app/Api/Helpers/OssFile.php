<?php


namespace App\Api\Helpers;


use App\Rules\ImageLegal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

trait OssFile
{
    public function zipOssPng($value)
    {
        return app(OssHandler::class)->zipWebp($value);
    }

    public function pickOssVideoPoster($value)
    {
        return app(OssHandler::class)->getOssVideoPoster($value, 1000, 750);
    }
}
