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
            'meet_date' => ['required', 'date'],
            'degree' => ['required', 'string'],
            'gender' => 'numeric',
        ];
    }

    public function attributes()
    {

        return [
            'name' => '姓名',
            'age' => '年龄',
            'phone' => '手机',
            'meet_date' => '预约时间',
            'digit' => '学员程度',
        ];
    }
}
