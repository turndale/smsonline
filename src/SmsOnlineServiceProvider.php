<?php

namespace Turndale\SmsOnline;

use Illuminate\Support\ServiceProvider;

class SmsOnlineServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/smsonline.php', 'smsonline');

        $this->app->bind('smsonline', function ($app) {
            return new SmsOnline();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/smsonline.php' => config_path('smsonline.php'),
            ], 'config');
        }
    }
}
