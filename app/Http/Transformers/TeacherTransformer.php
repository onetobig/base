<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/26
 * Time: 15:35
 */

namespace App\Http\Transformers;


use App\Models\Teachers;
use League\Fractal\TransformerAbstract;

class TeacherTransformer extends TransformerAbstract
{
    public function transform(Teachers $teacher) {
        return [
            'name' => $teacher->name,
            'avatar' => $teacher->avatar_url,
            'introduce' => $teacher->introduce,
        ];
    }
}