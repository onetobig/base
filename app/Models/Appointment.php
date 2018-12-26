<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    public static $genderMap = [
        self::GENDER_MALE => '男',
        self::GENDER_FEMALE => '女',
    ];

    protected $casts = [
        'hobbies' => 'array',
        'courses' => 'array',
    ];

    protected $fillable = ['name', 'age', 'phone', 'meet_date', 'hobbies', 'gender', 'courses', ];
}
