<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTypeModule extends Model
{
    protected $fillable = [
        'business_type',
        'module_key',
    ];

    /**
     * All module keys currently assigned (free, by default) to a business
     * type, in the order config('modules') defines them.
     */
    public static function modulesFor(string $businessType): array
    {
        return static::where('business_type', $businessType)->pluck('module_key')->all();
    }

    /**
     * Replace a business type's module assignment wholesale - the tick-box
     * form on the Business Modules page always submits the full desired
     * set, so a delete-then-insert is simpler and safer than diffing.
     */
    public static function syncFor(string $businessType, array $moduleKeys): void
    {
        static::where('business_type', $businessType)->delete();

        $now = now();
        $rows = collect($moduleKeys)->unique()->values()->map(fn ($key) => [
            'business_type' => $businessType,
            'module_key' => $key,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($rows) {
            static::insert($rows);
        }
    }
}
