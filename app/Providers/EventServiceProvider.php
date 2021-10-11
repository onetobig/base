<?php

namespace App\Providers;

use App\Events\OrderPaid;
use App\Events\Reading\ClockInEndEvent;
use App\Events\Reading\ClockInSuccessEvent;
use App\Listeners\Order\BoughtExpCardListener;
use App\Listeners\Order\BoughtVipListener;
use App\Listeners\Order\PaidCompanyCustomCardListener;
use App\Listeners\Reading\ComputeClockInFundListener;
use App\Listeners\Reading\UpdateReadingTimeListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderPaid::class => [
            BoughtExpCardListener::class,
            BoughtVipListener::class,
            PaidCompanyCustomCardListener::class,
        ],
        ClockInSuccessEvent::class => [
            ComputeClockInFundListener::class,
        ],
        ClockInEndEvent::class => [
            UpdateReadingTimeListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
