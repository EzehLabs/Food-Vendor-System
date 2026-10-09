<?php

namespace App\Providers;

use App\Mail\BrevoTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mail::extend('brevo', function (array $config = []): BrevoTransport {
            return new BrevoTransport(
                config('services.brevo.key'),
                config('mail.from.address'),
                config('mail.from.name'),
            );
        });

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
