<?php

namespace Shabayek\Sms\Providers;

use Shabayek\Sms\SmsManager;
use Illuminate\Support\ServiceProvider;

/**
 * SmsServiceProvider class.
 * @author Esmail Shabayek <esmail.shabayek@gmail.com>
 */
class SmsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/sms.php' => config_path('sms.php'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/sms.php', 'sms');

        $this->app->bind('sms', function () {
            return new SmsManager($this->app);
        });
    }
}
