<?php

namespace Alimianesa\SmartAuth\Facades;

use Illuminate\Support\Facades\Facade;

class SmartAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'smartauth';
    }
}
