<?php

namespace Alimianesa\SmartAuth\Jobs;

use Alive2212\LaravelMobilePassport\Http\Controllers\MobilePassportAuthController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPhoneNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;

    /**
     * Create a new job instance.
     *
     * @param $user
     */
    public function __construct($user )
    {
        $this->user = $user;
    }

    /**
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function handle()
    {
        return true;
    }
}
