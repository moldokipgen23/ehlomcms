<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTypeModule extends Model
{
    protected $fillable = [
        'business_type',
        'module_key',
        'status',
        'price',
        'billing_cycle',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    /**
     * All module assignments for a business type, keyed by module_key, each
     * entry ['status' => 'free'|'paid', 'price' => float|null]. A
     * module_key absent from this array is "Off" for that type.
     */
    public static function assignmentsFor(string $businessType): array
    {
        return static::where('business_type', $businessType)
            ->get()
            ->keyBy('module_key')
            ->map(fn ($row) => [
                'status' => $row->status,
                'price' => $row->price,
                'billing_cycle' => $row->billing_cycle ?? 'one_time',
            ])
            ->all();
    }

    /**
     * Backward-compatible helper: module keys currently free for a type.
     */
    public static function modulesFor(string $businessType): array
    {
        return static::where('business_type', $businessType)->where('status', 'free')->pluck('module_key')->all();
    }

    /**
     * Replace a business type's module assignment wholesale - the form on
     * the Business Modules page always submits the full desired set, so a
     * delete-then-insert is simpler and safer than diffing.
     *
     * $assignments: [module_key => ['status' => 'free'|'paid'|'off', 'price' => float|null]]
     */
    public static function syncFor(string $businessType, array $assignments): void
    {
        static::where('business_type', $businessType)->delete();

        $now = now();
        $rows = [];

        foreach ($assignments as $moduleKey => $assignment) {
            $status = $assignment['status'] ?? 'off';
            if ($status === 'off') {
                continue;
            }

            $rows[] = [
                'business_type' => $businessType,
                'module_key' => $moduleKey,
                'status' => $status,
                'price' => $status === 'paid' ? ($assignment['price'] ?? null) : null,
                'billing_cycle' => $status === 'paid' ? ($assignment['billing_cycle'] ?? 'one_time') : 'one_time',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            static::insert($rows);
        }
    }
}
