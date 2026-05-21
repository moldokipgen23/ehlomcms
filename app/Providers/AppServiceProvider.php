<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind Bunny's CDN, TLS is terminated at the edge and the container
        // receives plain HTTP — without this, asset URLs render as http:// on
        // an https:// page and the browser blocks them as mixed content.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('layouts.app', function ($view) {
            $view->with('notifications', app(NotificationService::class)->alerts());
        });
    }
}
