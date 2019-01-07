<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 15:35
 */

namespace App\Http\Transformers;


use App\Models\Image;
use League\Fractal\TransformerAbstract;

class ImageTransformer extends TransformerAbstract
{
    public function transform(Image $image) {
        return [
            'url' => $image->url_info,
            'category' => $image->category,
        ];
    }
}