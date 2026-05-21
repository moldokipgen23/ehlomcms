<?php

namespace App\Services;

use App\Models\Setting;

class MailConfigService
{
    /**
     * Whether SMTP has been configured by the admin.
     */
    public static function configured(): bool
    {
        return (bool) Setting::get('smtp_host')
            && (bool) Setting::get('smtp_from_address');
    }

    /**
     * Apply the SMTP settings stored in the settings table to the live
     * mail config so subsequent Mail::send() calls use them. Returns
     * false (and leaves config untouched) if SMTP is not configured.
     */
    public static function apply(): bool
    {
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
            'mail.from.address' => Setting::get('smtp_from_address') ?: config('mail.from.address'),
            'mail.from.name' => Setting::get('smtp_from_name') ?: config('mail.from.name'),
        ]);

        return true;
    }
}
