<?php
namespace App\Api\Helpers;

use App\Models\ClassHour;
use Illuminate\Support\Facades\Redis;
use App\Jobs\ClassHour\TwiceNotice;
use App\Models\Institution;

trait ClassHourTwiceAbsenceNotice
{
    public function twiceNotice(ClassHour $hour, $limit = 2)
    {
        $month = now()->format('Ym');
        $month_key = "hour:absence:{$month}:" . $hour->id;
        $day_key = 'hour:absence:' . $hour->id;

        // 清除上个月
        $last_month = now()->addMonth(-1)->format('Ym');
        $last_month_key = "hour:absence:{$last_month}:" . $hour->id;
        Redis::del($last_month_key);

        $month_count = Redis::incr($month_key);
        $count = Redis::incr($day_key);

        if ($count === (int)$limit) {
            Redis::set($day_key, 0);
            // 发送通知
            dispatch(new TwiceNotice($hour, 1, $limit));
            return;
        }

        if ($month_count == $limit) {
            // 发送通知
            dispatch(new TwiceNotice($hour, 2, $limit));
        }
    }

    public function arrive(ClassHour $hour)
    {
        $day_key = 'hour:absence:' . $hour->id;
        Redis::set($day_key, 0);
    }
}
