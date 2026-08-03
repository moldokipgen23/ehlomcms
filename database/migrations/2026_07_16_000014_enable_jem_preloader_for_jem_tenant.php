<?php

use App\Models\Tenant;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tenant = Tenant::find(21);

        if (!$tenant) {
            return;
        }

        $modules = array_values(array_unique(array_merge($tenant->modules ?? [], [
            'jem_preloader',
        ])));

        $tenant->update(['modules' => $modules]);
    }

    public function down(): void
    {
        $tenant = Tenant::find(21);

        if (!$tenant) {
            return;
        }

        $modules = array_values(array_filter(
            $tenant->modules ?? [],
            fn (string $module) => $module !== 'jem_preloader'
        ));

        $tenant->update(['modules' => $modules]);
    }
};
