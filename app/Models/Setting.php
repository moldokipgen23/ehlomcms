<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * Hardcoded default values for email templates.
     */
    public const TEMPLATE_DEFAULTS = [
        'email_invoice_subject' => 'Invoice {invoice_number} from Ehlom Digital',
        'email_invoice_body' => "Dear {client_name},\n\nPlease find your invoice {invoice_number} attached.\n\nCharges:\n{items}\n\nAmount Due: ₹{amount}\nDue Date: {due_date}\n\nThe full breakdown is in the attached PDF. Thank you for your business.\n\nEhlom Digital",
        'email_renewal_subject' => 'Renewal Reminder — {product_name}',
        'email_renewal_body' => "Dear {client_name},\n\nYour {product_name} is due for renewal on {expiry_date}.\n\nRenewal Amount: ₹{renewal_amount}\n\nPlease contact us to proceed.\n\nEhlom Digital",
        'email_payment_subject' => 'Payment Received — Invoice {invoice_number}',
        'email_payment_body' => "Dear {client_name},\n\nWe have received your payment for invoice {invoice_number}. Your account is now settled.\n\nAmount Paid: ₹{amount}\nPayment Date: {payment_date}\n\nThank you for your business.\n\nEhlom Digital",
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
     * Return a stored image as a base64 data URI for embedding in PDFs.
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
}
