<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('tenant_id')->constrained();
        });

        // Backfill BEFORE the admin.role middleware is ever wired to any
        // route: every existing agency user (tenant_id IS NULL - the only
        // kind of user that existed before this migration, since tenant
        // owner logins didn't get a role concept) becomes 'admin'. Without
        // this, the very first request from the person who's been running
        // this platform would get 403'd by their own new permission system.
        $adminRoleId = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'label' => 'Admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            'name' => 'staff',
            'label' => 'Staff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('users')->whereNull('tenant_id')->whereNull('role_id')->update(['role_id' => $adminRoleId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};
