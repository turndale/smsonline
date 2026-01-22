<?php

namespace Turndale\SmsOnline\Facades;

use Illuminate\Support\Facades\Facade;

class SmsOnline extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'smsonline';
    }
}
