<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/18
 * Time: 11:05
 */

namespace App\Http\Requests\Api;


use Dingo\Api\Http\FormRequest;

class ApiRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
}