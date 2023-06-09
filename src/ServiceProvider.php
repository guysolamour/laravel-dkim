<?php

namespace Guysolamour\Dkim;

use Illuminate\Mail\MailServiceProvider;


class ServiceProvider extends MailServiceProvider
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
        parent::register();

        $this->mergeConfigFrom(
            self::CONFIG_PATH,
            'dkim'
        );
    }

     /**
     * Register the Illuminate mailer instance.
     *
     * @return void
     */
    protected function registerIlluminateMailer()
    {
        $this->app->singleton('mail.manager', fn($app) => new MailManager($app));
        $this->app->bind('mailer', fn($app) => $app->make('mail.manager')->mailer());
    }
}

