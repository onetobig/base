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
        'meet_date' => 'array',
    ];

    protected $guarded = [];
}
