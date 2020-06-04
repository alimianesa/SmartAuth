<?php

namespace Alimianesa\SmartAuth;

use Alimianesa\SmartAuth\Events\UserUpdated;
use Alimianesa\SmartAuth\Listeners\CheckValidation;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        UserUpdated::class => [
            CheckValidation::class,
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
