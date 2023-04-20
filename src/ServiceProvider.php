<?php

namespace Guysolamour\Dkim;


class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    const CONFIG_PATH     = __DIR__ . '/../config/dkim.php';

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::CONFIG_PATH  => config_path('dkim.php'),
            ], 'dkim-config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'dkim'
        );
    }
}

