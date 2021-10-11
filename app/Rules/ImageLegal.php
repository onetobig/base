<?php

namespace App\Rules;

use App\Services\MiniProgramService;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Redis;

class ImageLegal implements Rule
{
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $key = app(MiniProgramService::class)->getImageCheckKey();
        $image_info = parse_url($value);
        return Redis::hGet($key, $image_info['path']);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '图片审查未通过！请确认图片不包含敏感内容！';
    }
}
