<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Read a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    /**
     * Create or update a setting value.
     */
    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public static function getEncrypted(string $key): ?string
    {
        $value = static::get($key);

        if (!filled($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }

    public static function putEncrypted(string $key, string $value): void
    {
        static::put($key, Crypt::encryptString($value));
    }

    /**
     * Global payment methods used when a client pays Ehlom for invoices,
     * hosting, and platform services. These are intentionally separate from
     * the payment methods configured inside an individual client store.
     */
    public static function billingPaymentMethods(): array
    {
        $hasRazorpayCredentials = filled(static::getEncrypted('billing_razorpay_key'))
            && filled(static::getEncrypted('billing_razorpay_secret'));

        return [
            'razorpay' => static::get('billing_razorpay_enabled', $hasRazorpayCredentials ? '1' : '0') === '1'
                && $hasRazorpayCredentials,
            'bank' => static::get('billing_bank_enabled', '0') === '1',
            'cash' => static::get('billing_cash_enabled', '0') === '1',
            'bank_label' => static::get('billing_bank_label', 'Bank transfer / UPI'),
            'bank_instructions' => static::get('billing_bank_instructions'),
            'cash_instructions' => static::get('billing_cash_instructions'),
        ];
    }

    /**
     * Hardcoded default values for email templates.
     */
    public const TEMPLATE_DEFAULTS = [
        'email_invoice_subject' => 'Invoice {invoice_number} from Ehlom Digital',
        'email_invoice_body' => "Dear {client_name},\n\nPlease find your invoice {invoice_number} attached.\n\nCharges:\n{items}\n\nAmount Due: ₹{amount}\nDue Date: {due_date}\n\nPay securely online: {payment_link}\n\nThe full breakdown is in the attached PDF. Thank you for your business.\n\nEhlom Digital",
        'email_renewal_subject' => 'Renewal Reminder — {product_name}',
        'email_renewal_body' => "Dear {client_name},\n\nYour {product_name} is due for renewal on {expiry_date}.\n\nRenewal Amount: ₹{renewal_amount}\n\nPay securely online: {payment_link}\n\nEhlom Digital",
        'email_payment_subject' => 'Payment Received — Invoice {invoice_number}',
        'email_payment_body' => "Dear {client_name},\n\nWe have received your payment for invoice {invoice_number}. Your account is now settled.\n\nAmount Paid: ₹{amount}\nPayment Date: {payment_date}\n\nThank you for your business.\n\nEhlom Digital",
        'email_completion_subject' => 'Project Completed — {project_title}',
        'email_completion_body' => "Dear {client_name},\n\nYour project {project_title} has been completed and is ready for handover. A full summary is attached as a PDF, including:\n\n- What was delivered\n- Ongoing services and renewal dates\n- Recommended next steps\n\nIt has been a pleasure working with you. Please reach out if anything needs adjustment.\n\nEhlom Digital",
    ];

    /**
     * Read a template setting, falling back to the hardcoded default if empty.
     */
    public static function template(string $key): string
    {
        $value = static::get($key);

        if ($value === null || trim($value) === '') {
            return static::TEMPLATE_DEFAULTS[$key] ?? '';
        }

        return $value;
    }

    /**
     * Replace {placeholder} tokens in a template string with values from $data.
     * Unmatched placeholders are left blank. Never throws.
     */
    public static function renderTemplate(string $template, array $data): string
    {
        try {
            return preg_replace_callback('/\{([a-z_]+)\}/i', function ($m) use ($data) {
                $key = $m[1];

                return array_key_exists($key, $data) ? (string) $data[$key] : '';
            }, $template) ?? $template;
        } catch (\Throwable $e) {
            return $template;
        }
    }

    /**
     * Return a stored image as a base64 data URI for embedding in PDFs, at
     * full resolution (PDFs are viewed/printed, not size-constrained the
     * way email clients are).
     */
    public static function imageData(string $key): ?string
    {
        $path = static::get($key);

        if (! $path) {
            return null;
        }

        $full = storage_path('app/public/' . $path);

        if (! is_file($full)) {
            return null;
        }

        $mime = mime_content_type($full) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
    }

    /**
     * Same as imageData() but resized down before encoding, specifically for
     * embedding in email HTML. An uploaded logo embedded at full resolution
     * (e.g. 180KB+) pushes total email size over Gmail's ~102KB clipping
     * threshold, so the message arrives truncated with "View entire message"
     * and a blank-looking body - happened on every email this app sends
     * (invoices, renewal reminders, password resets), not just a one-off.
     * Resized copies are cached on disk next to the original so this only
     * costs a GD resize once per uploaded logo, not once per email sent.
     */
    public static function emailImageData(string $key, int $maxDimension = 160): ?string
    {
        $path = static::get($key);

        if (! $path) {
            return null;
        }

        $full = storage_path('app/public/' . $path);

        if (! is_file($full)) {
            return null;
        }

        $cachePath = storage_path('app/public/email-cache/' . md5($path . $maxDimension) . '.png');

        if (is_file($cachePath) && filemtime($cachePath) >= filemtime($full)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($cachePath));
        }

        [$width, $height] = @getimagesize($full) ?: [0, 0];

        if ($width <= 0 || $height <= 0 || ($width <= $maxDimension && $height <= $maxDimension)) {
            // Already small enough, or unreadable dimensions - fall back to
            // the original rather than risk a bad resize.
            $mime = mime_content_type($full) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
        }

        $source = match (true) {
            str_ends_with(strtolower($full), '.png') => @imagecreatefrompng($full),
            str_ends_with(strtolower($full), '.gif') => @imagecreatefromgif($full),
            default => @imagecreatefromjpeg($full),
        };

        if (! $source) {
            $mime = mime_content_type($full) ?: 'image/png';

            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full));
        }

        $ratio = min($maxDimension / $width, $maxDimension / $height);
        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($source);

        $dir = dirname($cachePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        imagepng($resized, $cachePath);
        imagedestroy($resized);

        return 'data:image/png;base64,' . base64_encode(file_get_contents($cachePath));
    }
}
