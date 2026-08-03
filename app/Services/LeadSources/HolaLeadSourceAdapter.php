<?php

namespace App\Services\LeadSources;

use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HolaLeadSourceAdapter
{
    public function fetch(LeadSource $source): array
    {
        $settings = $source->settings ?: [];
        $path = data_get($settings, 'businesses_path', 'api/v1/businesses');
        $url = rtrim((string) $source->base_url, '/') . '/' . ltrim($path, '/');
        $headers = ['Accept' => 'application/json'];

        if ($key = data_get($source->credentials, 'api_key')) {
            $headers['X-API-Key'] = $key;
        }
        if ($token = data_get($source->credentials, 'bearer_token')) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        $items = [];
        $page = 1;
        $maxPages = min(20, max(1, (int) data_get($settings, 'max_pages', 5)));

        do {
            $response = Http::withHeaders($headers)->timeout(30)->get($url, [
                'page' => $page,
                'per_page' => min(100, max(10, (int) data_get($settings, 'per_page', 50))),
                'is_active' => true,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Hola API returned HTTP ' . $response->status() . '.');
            }

            $payload = $response->json();
            $pageItems = $this->items($payload);
            $items = array_merge($items, $pageItems);
            $hasMore = (bool) (data_get($payload, 'meta.has_more') ?? data_get($payload, 'pagination.has_more') ?? (count($pageItems) >= (int) data_get($settings, 'per_page', 50)));
            $page++;
        } while ($hasMore && $page <= $maxPages);

        return array_map(fn (array $business) => $this->normalize($business), $items);
    }

    private function items(mixed $payload): array
    {
        if (!is_array($payload)) return [];
        if (array_is_list($payload)) return $payload;

        foreach (['data.data', 'data.businesses', 'data.results', 'data.items', 'businesses', 'results', 'items', 'data'] as $key) {
            $items = data_get($payload, $key);
            if (is_array($items) && array_is_list($items)) return $items;
        }

        return [];
    }

    private function normalize(array $business): array
    {
        $name = data_get($business, 'name') ?: data_get($business, 'business_name') ?: data_get($business, 'title') ?: 'Unnamed business';
        $address = data_get($business, 'address') ?: data_get($business, 'formatted_address') ?: data_get($business, 'location.address');
        $types = data_get($business, 'types') ?: data_get($business, 'categories') ?: data_get($business, 'category.name') ?: [];

        return [
            'external_id' => (string) (data_get($business, 'id') ?: data_get($business, 'slug') ?: sha1(strtolower($name . '|' . $address))),
            'name' => $name,
            'email' => data_get($business, 'email') ?: data_get($business, 'contact.email'),
            'phone' => data_get($business, 'phone') ?: data_get($business, 'phone_number') ?: data_get($business, 'contact.phone'),
            'website_url' => data_get($business, 'website') ?: data_get($business, 'website_url') ?: data_get($business, 'url'),
            'business_name' => $name,
            'description' => trim(implode(' · ', array_filter([$address, is_array($types) ? implode(', ', $types) : $types]))),
            'features' => is_array($types) ? implode(', ', $types) : (string) $types,
            'external_metadata' => $business,
        ];
    }
}
