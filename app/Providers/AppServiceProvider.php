<?php

namespace App\Providers;

use App\Mail\Transport\BrevoApiTransport;
use App\Services\MailConfigService;
use App\Services\NotificationService;
use App\Services\TenantContext;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
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
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Behind Bunny's CDN, TLS is terminated at the edge and the container
        // receives plain HTTP — without this, asset URLs render as http:// on
        // an https:// page and the browser blocks them as mixed content.
        // Forced for every environment except local development.
        if ($this->app->environment() !== 'local') {
            URL::forceScheme('https');
        }

        Mail::extend('brevo-api', function (array $config) {
            return new BrevoApiTransport((string) ($config['key'] ?? ''));
        });

        // Was only being applied ad-hoc before specific sends (invoices,
        // renewal reminders) - every controller had to remember to call it.
        // Doing it once here means ANY mail anywhere (including tenant
        // password resets) uses whatever the admin configured on the
        // Settings page, with no per-call-site duplication. Guarded by
        // Schema::hasTable so a fresh install running `migrate` before the
        // settings table exists doesn't crash on every artisan command.
        if (Schema::hasTable('settings') && MailConfigService::configured()) {
            MailConfigService::apply();
        }

        View::composer('layouts.app', function ($view) {
            $view->with('notifications', app(NotificationService::class)->alerts());
        });

        // Tenant users (tenant_id set) get a reset link on their own
        // subdomain's dashboard, not the agency portal - the two logins are
        // completely separate front doors, so the reset link has to send
        // them back to the one they actually use.
        ResetPassword::createUrlUsing(function ($notifiable, string $token) {
            if ($notifiable->tenant_id) {
                $subdomain = $notifiable->tenant?->subdomain;

                return route('tenant.password.reset', [
                    'subdomain' => $subdomain,
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ]);
            }

            return route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });
    }
}
