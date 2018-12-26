<?php

namespace App\Http\Requests\Api;


class AppointmentRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'age' => 'required|numeric',
            'phone' => ['required', 'regex:/^1\d{10}/'],
            'meet_date' => "required|string",
            'hobbies' => ['required', 'array'],
            'gender' => 'numeric|required|in:0,1',
            'courses' => ['required', 'array'],
        ];
    }

    public function attributes()
    {

        return [
            'name' => '姓名',
            'age' => '年龄',
            'phone' => '手机',
            'meet_date' => '试课时间',
            'hobbies' => '爱好及特长',
            'courses' => '试课班别',
            'gender' => '性别',
        ];
    }

    public function messages()
    {
        return [
            'hobbies.required' => '请选择特长及爱好',
            'courses' => '请选择试课班别',
        ];
    }
}
