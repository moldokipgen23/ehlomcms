<?php

use App\Models\AddonProduct;
use App\Models\BusinessTypeModule;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        AddonProduct::updateOrCreate(
            ['key' => 'jem_preloader'],
            [
                'module_key' => 'jem_preloader',
                'name' => 'Premium Store Preloader',
                'description' => 'Luxury animated storefront preloader and entrance effect for custom premium shopping themes.',
                'price' => 799,
                'icon' => 'ti-loader-3',
                'active' => true,
                'business_types' => ['shopping'],
            ],
        );

        BusinessTypeModule::updateOrCreate(
            ['business_type' => 'shopping', 'module_key' => 'jem_preloader'],
            ['status' => 'paid', 'price' => 799],
        );
    }

    public function down(): void
    {
        AddonProduct::where('key', 'jem_preloader')->delete();
        BusinessTypeModule::where('business_type', 'shopping')
            ->where('module_key', 'jem_preloader')
            ->delete();
    }
};
