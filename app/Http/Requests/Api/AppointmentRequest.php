<?php

namespace App\Http\Requests\Api;


class AppointmentRequest extends ApiRequest
{
    public function rules()
    {
        return [
            'phone' => ['required', 'regex:/^1\d{10}/'],
            'name' => 'required|string',
            'gender' => 'numeric|required|in:0,1',
            'age' => 'required|numeric',
            'hobbies' => ['required', 'array'],
            'meet_date' => "required|string",
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
            'name.required' => '请填写孩子姓名',
            'age.required' => '请选择孩子年龄',
            'phone.required' => '请填写手机号码',
            'meet_date.required' => '请选择试课时间',
            'hobbies.required' => '请选择特长及爱好',
            'courses.required' => '请选择试课班别',
            'gender' => '请选择孩子性别',
        ];
    }
}
