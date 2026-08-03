<?php

namespace App\Services;

use App\Models\AiPrototypeCatalog;
use App\Models\Lead;
use Illuminate\Support\Collection;

class PrototypeMatcher
{
    public function match(Lead|array $lead): array
    {
        $data = $lead instanceof Lead ? $lead->toArray() : $lead;
        $metadata = (array) ($data['external_metadata'] ?? []);
        $haystack = strtolower(implode(' ', array_filter([
            $data['project_type'] ?? null,
            $data['business_name'] ?? null,
            $data['description'] ?? null,
            $data['features'] ?? null,
            $metadata['query'] ?? null,
            is_array($metadata['types'] ?? null) ? implode(' ', $metadata['types']) : ($metadata['types'] ?? null),
        ])));

        $catalog = $this->catalog();
        $prototype = $catalog->first(function (AiPrototypeCatalog $item) use ($haystack, $data) {
            return $this->containsAny($haystack, $item->keywords ?? [])
                || ($item->business_type === 'shopping' && ($data['project_type'] ?? null) === 'ecommerce');
        }) ?: $catalog->firstWhere('key', 'business');

        $key = $prototype?->key ?? 'business';

        return [
            'key' => $key,
            'label' => $prototype?->name ?? ucfirst($key) . ' Demo',
            'theme_key' => $prototype?->theme_key,
            'preview_url' => filled($prototype?->preview_url) ? $prototype->preview_url : null,
            'offer_label' => $prototype?->recommended_offer,
            'available' => filled($prototype?->preview_url),
        ];
    }

    public function assign(Lead $lead): array
    {
        $prototype = $this->match($lead);

        $lead->forceFill([
            'prototype_key' => $prototype['key'],
            'prototype_url' => $prototype['preview_url'],
            'recommended_offer' => $prototype['offer_label'],
        ])->save();

        return $prototype;
    }

    private function containsAny(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($haystack, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function catalog(): Collection
    {
        try {
            $items = AiPrototypeCatalog::active()->orderBy('id')->get();
            if ($items->isNotEmpty()) {
                return $items;
            }
        } catch (\Throwable) {
            // The config catalog keeps lead matching available during deploys
            // before the catalog migration has reached every environment.
        }

        return collect(config('lead_prototypes', []))->map(function (array $item, string $key) {
            return new AiPrototypeCatalog([
                'key' => $key,
                'name' => $item['label'] ?? ucfirst($key) . ' Demo',
                'business_type' => $key,
                'theme_key' => $item['theme_key'] ?? null,
                'preview_url' => $item['demo_url'] ?? null,
                'recommended_offer' => $item['offer_label'] ?? null,
                'keywords' => $item['keywords'] ?? [],
                'status' => 'active',
            ]);
        })->values();
    }
}
