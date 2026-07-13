<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Built-in Restaurant theme, matching the Info/Shop Classic seeds in
        // create_themes_table. Without this row the Restaurant vertical works
        // (site_type fallback renders the restaurant layout) but there is no
        // pickable template in the admin gallery, so a restaurant tenant looks
        // like it has no theme option. base_template 'restaurant' maps to
        // resources/views/tenant-templates/restaurant/index.blade.php.
        Theme::firstOrCreate(
            ['key' => 'restaurant'],
            [
                'name' => 'Restaurant Classic',
                'description' => 'A menu-first layout with dishes grouped by category, ordering buttons, and a table-reservation form. Great for restaurants, cafes, and bistros.',
                'thumbnail' => 'images/themes/restaurant-preview.png',
                'base_template' => 'restaurant',
                'industries' => ['restaurant'],
                'public' => true,
            ]
        );
    }

    public function down(): void
    {
        Theme::where('key', 'restaurant')->delete();
    }
};
