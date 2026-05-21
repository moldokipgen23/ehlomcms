<?php

namespace App\Services;

use App\Models\Setting;

class MailConfigService
{
    /**
     * Whether mail has been configured by the admin — either via the Brevo
     * HTTP API (preferred) or classic SMTP. A "from" address is required
     * either way.
     */
    public static function configured(): bool
    {
        if (! Setting::get('smtp_from_address')) {
            return false;
        }

        return (bool) Setting::get('brevo_api_key')
            || (bool) Setting::get('smtp_host');
    }

    /**
     * Apply the stored mail settings to the live mail config so subsequent
     * Mail::send() calls use them. Prefers the Brevo HTTP API (works from
     * container hosts that block outbound SMTP ports); falls back to SMTP.
     * Returns false (and leaves config untouched) if mail is not configured.
     */
    public static function apply(): bool
    {
        $fromAddress = Setting::get('smtp_from_address');
        $fromName = Setting::get('smtp_from_name', 'Ehlom Digital');

        $apiKey = Setting::get('brevo_api_key');

        if ($apiKey) {
            config([
                'mail.default' => 'brevo-api',
                'mail.mailers.brevo-api.key' => $apiKey,
                'mail.from.address' => $fromAddress ?: config('mail.from.address'),
                'mail.from.name' => $fromName ?: config('mail.from.name'),
            ]);

            return true;
        }

        $host = Setting::get('smtp_host');

        if (! $host) {
            return false;
        }

        $encryption = strtolower((string) Setting::get('smtp_encryption', 'tls'));
        $scheme = $encryption === 'ssl' ? 'smtps' : 'smtp';

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $scheme,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => (int) (Setting::get('smtp_port', 587) ?: 587),
            'mail.mailers.smtp.username' => Setting::get('smtp_username') ?: null,
            'mail.mailers.smtp.password' => Setting::get('smtp_password') ?: null,
            'mail.from.address' => $fromAddress ?: config('mail.from.address'),
            'mail.from.name' => $fromName ?: config('mail.from.name'),
        ]);

        return true;
    }
}
