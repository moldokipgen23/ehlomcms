<?php

use App\Models\Theme;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Built-in Business Classic theme, matching the Info/Shop/Restaurant
        // Classic seeds. base_template 'business' maps to
        // resources/views/tenant-templates/business/index.blade.php.
        Theme::firstOrCreate(
            ['key' => 'business'],
            [
                'name' => 'Business Classic',
                'description' => 'A services-first layout with a portfolio of services, client testimonials, and a blog. Great for consultants, agencies, and professional services.',
                'thumbnail' => 'images/themes/business-preview.png',
                'base_template' => 'business',
                'industries' => ['business'],
                'public' => true,
            ]
        );
    }

    public function down(): void
    {
        Theme::where('key', 'business')->delete();
    }
};
