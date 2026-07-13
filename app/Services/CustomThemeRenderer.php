<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;

/**
 * Renders a theme's raw pasted HTML by simple find-and-replace token
 * substitution. This is deliberately NOT Blade, NOT PHP eval, NOT any kind
 * of code execution - it is plain string replacement so that a non-technical
 * admin can paste HTML they already know how to write, without ever
 * learning Blade syntax, and without any risk of arbitrary code execution
 * from pasted content (Blade's {{ }} syntax compiles to real PHP; this
 * engine never compiles or evals anything, it only replaces text).
 *
 * Supported tokens, documented in resources/views/themes/form.blade.php:
 *   {{tenant.name}} {{tenant.logo}} {{tenant.banner}} {{tenant.about}}
 *   {{tenant.contact_email}} {{tenant.contact_phone}} {{tenant.contact_address}}
 *   {{tenant.whatsapp_number}}
 *   {{#products}} ... {{item.name}} {{item.price}} {{item.photo}}
 *   {{item.description}} {{item.buy_button}} ... {{/products}}
 */
class CustomThemeRenderer
{
    public function render(string $html, Tenant $tenant, $products): string
    {
        $html = $this->renderProductsBlock($html, $tenant, $products);

        return strtr($html, [
            '{{tenant.name}}' => e($tenant->name),
            '{{tenant.logo}}' => $tenant->logo ? e(Storage::url($tenant->logo)) : '',
            '{{tenant.banner}}' => $tenant->banner_image ? e(Storage::url($tenant->banner_image)) : '',
            '{{tenant.about}}' => nl2br(e($tenant->about_text ?? '')),
            '{{tenant.contact_email}}' => e($tenant->contact_email ?? ''),
            '{{tenant.contact_phone}}' => e($tenant->contact_phone ?? ''),
            '{{tenant.contact_address}}' => e($tenant->contact_address ?? ''),
            '{{tenant.whatsapp_number}}' => e($tenant->whatsapp_number ?? ''),
        ]);
    }

    private function renderProductsBlock(string $html, Tenant $tenant, $products): string
    {
        if (!preg_match('/\{\{#products\}\}(.*?)\{\{\/products\}\}/s', $html, $match)) {
            return $html;
        }

        [$fullBlock, $itemTemplate] = $match;

        $rendered = $products->map(function ($product) use ($tenant, $itemTemplate) {
            return strtr($itemTemplate, [
                '{{item.name}}' => e($product->name),
                '{{item.price}}' => number_format((float) $product->price, 2),
                '{{item.photo}}' => $product->photo ? e(Storage::url($product->photo)) : '',
                '{{item.description}}' => e($product->description ?? ''),
                '{{item.buy_button}}' => $this->buyButtonHtml($tenant, $product),
            ]);
        })->implode('');

        return str_replace($fullBlock, $rendered, $html);
    }

    /**
     * A plain-HTML equivalent of the tenant-action-button Blade component,
     * kept separate and deliberately simple since this output is embedded
     * into admin-authored HTML rather than compiled through Blade.
     */
    private function buyButtonHtml(Tenant $tenant, $product): string
    {
        if ($tenant->action_type === 'whatsapp') {
            $number = preg_replace('/[^0-9]/', '', $tenant->whatsapp_number ?? '');
            if (!$number) {
                return '<span>WhatsApp not configured</span>';
            }
            $message = urlencode($product->name . " — I'm interested");
            $url = e("https://wa.me/{$number}?text={$message}");

            return "<a href=\"{$url}\" target=\"_blank\" rel=\"noopener\">Buy Now</a>";
        }

        if ($tenant->action_type === 'razorpay') {
            $paymentSetting = $tenant->paymentSetting;
            if (!$paymentSetting) {
                return '<span>Payment not configured</span>';
            }

            return '<button type="button" class="razorpay-btn"'
                . ' data-key="' . e($paymentSetting->api_key) . '"'
                . ' data-amount="' . (int) ($product->price * 100) . '"'
                . ' data-currency="INR"'
                . ' data-name="' . e($tenant->name) . '"'
                . ' data-description="' . e($product->name) . '"'
                . ' data-product-id="' . $product->id . '"'
                . ' data-tenant-id="' . $tenant->id . '">Buy Now</button>';
        }

        return '';
    }
}
