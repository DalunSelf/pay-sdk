<?php

namespace Ryan\Desktop;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Ryan\Desktop\Contracts\Factory;

class DesktopServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Factory::class, function ($app) {
            return new DesktopManager($app);
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'desktop-driver');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Factory::class];
    }
}
