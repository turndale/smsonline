<?php

namespace Turndale\SmsOnline\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Turndale\SmsOnline\SmsOnlineServiceProvider;

class SmsOnlineTestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SmsOnlineServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'SmsOnline' => \Turndale\SmsOnline\Facades\SmsOnline::class,
        ];
    }
}
