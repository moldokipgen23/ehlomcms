<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Build a wa.me link for the given phone number and optional message.
     */
    public static function link(?string $phone, string $message = ''): ?string
    {
        $number = self::normalize($phone);

        if ($number === null) {
            return null;
        }

        $url = 'https://wa.me/' . $number;

        if ($message !== '') {
            $url .= '?text=' . rawurlencode($message);
        }

        return $url;
    }

    /**
     * Strip non-numeric characters and add India country code (91)
     * when the number is a bare 10-digit local number.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }

        return $digits;
    }
}
