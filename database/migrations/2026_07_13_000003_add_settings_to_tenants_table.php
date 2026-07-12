<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('plan');
            $table->string('banner_image')->nullable()->after('logo');
            $table->string('whatsapp_number')->nullable()->after('banner_image');
            $table->string('contact_email')->nullable()->after('whatsapp_number');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->text('about_text')->nullable()->after('contact_phone');
            $table->text('contact_address')->nullable()->after('about_text');
            $table->string('contact_hours')->nullable()->after('contact_address');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo', 'banner_image', 'whatsapp_number', 'contact_email',
                'contact_phone', 'about_text', 'contact_address', 'contact_hours',
            ]);
        });
    }
};
