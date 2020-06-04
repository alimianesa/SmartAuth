<?php

namespace Alimianesa\SmartAuth\Listeners;


use Alimianesa\SmartAuth\Events\UserUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckValidation
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  UserUpdated  $event
     * @return void
     */
    public function handle(UserUpdated $event)
    {

        // Send SMS Job

//        $user = $event->user;
//
//        if (
//            $user->getOriginal('card_verified_at') !=
//            $user->card_verified_at &&
//            !is_null($user->card_verified_at)
//        ) {
//
//        }
//
//        elseif (
//            $user->getOriginal('video_verified_at') !=
//            $user->video_verified_at &&
//            !is_null($user->video_verified_at)
//        ) {
//
//        }
    }

}
