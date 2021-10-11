<?php

namespace App\Console\Commands;

use App\Services\Reading\ClockInService;
use Illuminate\Console\Command;

class FlushYesterdayClockCommand extends Command
{
    protected $signature = 'api:flush-yesterday-clock-cache-key';

    protected $description = 'flush yesterday clock cache key';

    public function handle()
    {
        app(ClockInService::class)->flushYesterdayKey();
    }
}
