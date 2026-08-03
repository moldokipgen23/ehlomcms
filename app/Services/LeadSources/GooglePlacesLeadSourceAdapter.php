<?php

namespace App\Services\LeadSources;

use App\Models\LeadSource;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GooglePlacesLeadSourceAdapter
{
    public function fetch(LeadSource $source): array
    {
        $apiKey = data_get($source->credentials, 'api_key');
        if (!$apiKey) throw new RuntimeException('Google Places requires an API key.');

        $settings = $source->settings ?: [];
        $queries = array_values(array_filter((array) data_get($settings, 'queries', [])));
        if (!$queries && filled(data_get($settings, 'query'))) $queries = [(string) data_get($settings, 'query')];
        if (!$queries) throw new RuntimeException('Add at least one Google Places search query.');

        $fieldMask = data_get($settings, 'field_mask', 'places.id,places.displayName,places.formattedAddress,places.nationalPhoneNumber,places.websiteUri,places.googleMapsUri,places.types');
        $items = [];

        foreach (array_slice($queries, 0, 20) as $query) {
            $body = ['textQuery' => $query, 'pageSize' => min(20, max(1, (int) data_get($settings, 'page_size', 20)))];
            if ($region = data_get($settings, 'region_code')) $body['regionCode'] = strtoupper($region);

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Goog-Api-Key' => $apiKey,
                'X-Goog-FieldMask' => $fieldMask,
            ])->timeout(30)->post('https://places.googleapis.com/v1/places:searchText', $body);

            if ($response->failed()) {
                $message = trim((string) data_get($response->json(), 'error.message'));
                throw new RuntimeException($message ?: 'Google Places returned HTTP ' . $response->status() . '.');
            }
            foreach ((array) data_get($response->json(), 'places', []) as $place) {
                $items[] = $this->normalize((array) $place, (string) $query);
            }
        }

        return $items;
    }

    private function normalize(array $place, string $query): array
    {
        $types = data_get($place, 'types', []);
        $name = data_get($place, 'displayName.text') ?: 'Unnamed business';
        $address = data_get($place, 'formattedAddress');

        return [
            'external_id' => (string) data_get($place, 'id'),
            'name' => $name,
            'business_name' => $name,
            'phone' => data_get($place, 'nationalPhoneNumber'),
            'website_url' => data_get($place, 'websiteUri'),
            'description' => trim(implode(' · ', array_filter([$address, $query]))),
            'features' => is_array($types) ? implode(', ', $types) : '',
            'external_metadata' => [
                'place_id' => data_get($place, 'id'),
                'maps_url' => data_get($place, 'googleMapsUri'),
                'address' => $address,
                'types' => $types,
                'query' => $query,
            ],
        ];
    }
}
