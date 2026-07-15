<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /** Plain (non-file) setting keys handled by the form. */
    private const TEXT_KEYS = [
        'email_invoice_subject', 'email_invoice_body',
        'email_renewal_subject', 'email_renewal_body',
        'email_payment_subject', 'email_payment_body',
        'email_completion_subject', 'email_completion_body',
        'smtp_host', 'smtp_port', 'smtp_username',
        'smtp_encryption', 'smtp_from_address', 'smtp_from_name',
        'platform_razorpay_key_id',
    ];

    public function edit()
    {
        return view('settings.edit', [
            'logo' => Setting::get('company_logo'),
            'signature' => Setting::get('company_signature'),
            'templates' => [
                'email_invoice_subject' => Setting::template('email_invoice_subject'),
                'email_invoice_body' => Setting::template('email_invoice_body'),
                'email_renewal_subject' => Setting::template('email_renewal_subject'),
                'email_renewal_body' => Setting::template('email_renewal_body'),
                'email_payment_subject' => Setting::template('email_payment_subject'),
                'email_payment_body' => Setting::template('email_payment_body'),
                'email_completion_subject' => Setting::template('email_completion_subject'),
                'email_completion_body' => Setting::template('email_completion_body'),
            ],
            'smtp' => [
                'smtp_host' => Setting::get('smtp_host'),
                'smtp_port' => Setting::get('smtp_port', '587'),
                'smtp_username' => Setting::get('smtp_username'),
                'smtp_encryption' => Setting::get('smtp_encryption', 'tls'),
                'smtp_from_address' => Setting::get('smtp_from_address'),
                'smtp_from_name' => Setting::get('smtp_from_name', 'Ehlom Digital'),
            ],
            'smtp_password_set' => (bool) Setting::get('smtp_password'),
            'brevo_api_key_set' => (bool) Setting::get('brevo_api_key'),
            'brevo_api_key_preview' => self::maskSecret(Setting::get('brevo_api_key')),
            'smtp_password_preview' => self::maskSecret(Setting::get('smtp_password')),
            'platform_razorpay_key_id' => Setting::get('platform_razorpay_key_id'),
            'platform_razorpay_key_secret_set' => (bool) Setting::get('platform_razorpay_key_secret'),
            'platform_razorpay_key_secret_preview' => self::maskSecret(Setting::get('platform_razorpay_key_secret')),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'signature' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'email_invoice_subject' => 'nullable|string|max:255',
            'email_invoice_body' => 'nullable|string|max:5000',
            'email_renewal_subject' => 'nullable|string|max:255',
            'email_renewal_body' => 'nullable|string|max:5000',
            'email_payment_subject' => 'nullable|string|max:255',
            'email_payment_body' => 'nullable|string|max:5000',
            'email_completion_subject' => 'nullable|string|max:255',
            'email_completion_body' => 'nullable|string|max:5000',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'brevo_api_key' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:255',
            'smtp_from_name' => 'nullable|string|max:255',
            'platform_razorpay_key_id' => 'nullable|string|max:255',
            'platform_razorpay_key_secret' => 'nullable|string|max:255',
        ]);

        foreach (self::TEXT_KEYS as $key) {
            Setting::put($key, $request->input($key));
        }

        // Secrets: only overwrite when a new value is supplied (form never echoes them back).
        // Trim whitespace/newlines that often sneak in when copy-pasting keys.
        if (filled($request->input('smtp_password'))) {
            Setting::put('smtp_password', trim((string) $request->input('smtp_password')));
        }
        if (filled($request->input('brevo_api_key'))) {
            Setting::put('brevo_api_key', trim((string) $request->input('brevo_api_key')));
        }
        if (filled($request->input('platform_razorpay_key_secret'))) {
            Setting::put('platform_razorpay_key_secret', trim((string) $request->input('platform_razorpay_key_secret')));
        }

        foreach (['logo' => 'company_logo', 'signature' => 'company_signature'] as $field => $key) {
            if ($request->boolean('remove_' . $field)) {
                $old = Setting::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                Setting::put($key, null);
                continue;
            }

            if ($request->hasFile($field)) {
                $old = Setting::get($key);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $path = $request->file($field)->store('settings', 'public');
                Setting::put($key, $path);
            }
        }

        return redirect()->route('settings.edit')->with('success', 'Settings saved.');
    }

    /**
     * Send a test email to the configured "from" address to verify SMTP.
     */
    public function testEmail(Request $request)
    {
        $to = $request->input('test_email') ?: Setting::get('smtp_from_address');

        if (! $to) {
            return back()->with('error', 'Enter a test recipient or set a "from" address first.');
        }

        if (! MailConfigService::configured()) {
            return back()->with('error', 'Email is not configured. Add a Brevo API key (or SMTP host) and a from-address, then save.');
        }

        try {
            MailConfigService::apply();
            Mail::to($to)->send(new \App\Mail\TestMail);
        } catch (\Throwable $e) {
            Log::error('SMTP test email failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Test email failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Test email sent to ' . $to . '. Check the inbox.');
    }

    /**
     * Mask a stored secret for the settings UI so the admin can confirm what
     * the database actually holds without exposing the full value. Returns
     * null when nothing is stored.
     */
    private static function maskSecret(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $len = strlen($value);
        $head = substr($value, 0, 6);
        $tail = $len > 10 ? substr($value, -4) : '';

        return $tail
            ? sprintf('%s…%s  (%d chars)', $head, $tail, $len)
            : sprintf('%s…  (%d chars)', $head, $len);
    }
}
