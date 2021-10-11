<?php

namespace App\Console;

use App\Console\Commands\Api\CreateApiToken as CreateApiTokenCommand;
use App\Console\Commands\Api\GenerateRankCommand;
use App\Console\Commands\ChangeTimestampColumnCommand;
use App\Console\Commands\CreateApiToken;
use App\Console\Commands\FlushYesterdayClockCommand;
use App\Console\Commands\SendNewTopicMsgCommand;
use App\Console\Commands\SendUnReadNoticeMsgCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        CreateApiTokenCommand::class,
        ChangeTimestampColumnCommand::class,
        FlushYesterdayClockCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // 清除昨天的打卡时间缓存
        $schedule->command('api:flush-yesterday-clock-cache-key')->dailyAt("00:31");
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
