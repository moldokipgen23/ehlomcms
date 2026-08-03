<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadSource;
use App\Services\LeadSources\GooglePlacesLeadSourceAdapter;
use App\Services\LeadSources\HolaLeadSourceAdapter;
use RuntimeException;

class LeadSourceManager
{
    public function adapter(LeadSource $source): object
    {
        return match ($source->driver) {
            'hola' => app(HolaLeadSourceAdapter::class),
            'google_places' => app(GooglePlacesLeadSourceAdapter::class),
            default => throw new RuntimeException('Unsupported lead source driver: ' . $source->driver),
        };
    }

    public function sync(LeadSource $source): array
    {
        $records = $this->adapter($source)->fetch($source);
        $count = 0;

        $matcher = app(PrototypeMatcher::class);

        foreach ($records as $record) {
            $externalId = (string) ($record['external_id'] ?: sha1(strtolower(($record['business_name'] ?? $record['name'] ?? '') . '|' . ($record['website_url'] ?? '') . '|' . ($record['phone'] ?? ''))));
            $attributes = [
                'name' => $record['name'] ?: $record['business_name'] ?: 'Unnamed business',
                'email' => $record['email'] ?? null,
                'phone' => $record['phone'] ?? null,
                'website_url' => $record['website_url'] ?? null,
                'business_name' => $record['business_name'] ?? $record['name'] ?? null,
                'project_type' => data_get($source->settings, 'default_project_type', 'website'),
                'description' => $record['description'] ?? null,
                'features' => $record['features'] ?? null,
                'source' => $source->driver,
                'external_metadata' => $record['external_metadata'] ?? [],
                'last_synced_at' => now(),
            ];

            $lead = Lead::where('lead_source_id', $source->id)->where('external_id', $externalId)->first();
            if (!$lead && filled($attributes['website_url'])) {
                $lead = Lead::where('website_url', $attributes['website_url'])->first();
            }
            if (!$lead && filled($attributes['phone'])) {
                $digits = preg_replace('/\D+/', '', $attributes['phone']);
                $lead = Lead::where('phone', 'like', '%' . substr($digits, -10))->first();
            }
            if (!$lead) {
                $lead = new Lead();
                $attributes['status'] = 'new';
            }

            $prototype = $matcher->match($attributes);
            $attributes['prototype_key'] = $prototype['key'];
            $attributes['prototype_url'] = $prototype['preview_url'];
            $attributes['recommended_offer'] = $prototype['offer_label'];
            $lead->fill($attributes + ['lead_source_id' => $source->id, 'external_id' => $externalId]);
            $lead->save();
            $count++;
        }

        $source->update(['last_synced_at' => now(), 'last_sync_status' => 'success', 'last_imported_count' => $count, 'last_error' => null]);
        return ['imported' => $count];
    }
}
