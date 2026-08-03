<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_products', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_products', 'tenant_product_category_id')) {
                $table->unsignedBigInteger('tenant_product_category_id')->nullable()->after('tenant_id');
            }
            if (!Schema::hasColumn('tenant_products', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (!Schema::hasColumn('tenant_products', 'type')) {
                $table->string('type', 20)->default('simple')->after('slug');
            }
            if (!Schema::hasColumn('tenant_products', 'cover_image')) {
                $table->string('cover_image')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('tenant_products', 'sku')) {
                $table->string('sku', 100)->nullable()->after('price');
            }
            if (!Schema::hasColumn('tenant_products', 'stock')) {
                $table->unsignedInteger('stock')->default(0)->after('sku');
            }
            if (!Schema::hasColumn('tenant_products', 'material')) {
                $table->string('material')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tenant_products', 'weight')) {
                $table->string('weight', 100)->nullable()->after('material');
            }
            if (!Schema::hasColumn('tenant_products', 'care_instructions')) {
                $table->text('care_instructions')->nullable()->after('weight');
            }
            if (!Schema::hasColumn('tenant_products', 'heritage_note')) {
                $table->text('heritage_note')->nullable()->after('care_instructions');
            }
            if (!Schema::hasColumn('tenant_products', 'is_top_seller')) {
                $table->boolean('is_top_seller')->default(false)->after('heritage_note');
            }
            if (!Schema::hasColumn('tenant_products', 'is_featured')) {
                $table->boolean('is_featured')->default(false)->after('is_top_seller');
            }
            if (!Schema::hasColumn('tenant_products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_featured');
            }
            if (!Schema::hasColumn('tenant_products', 'sort_order')) {
                $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
            }
        });

        if (!Schema::hasTable('tenant_product_categories')) {
        Schema::create('tenant_product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });
        }

        if (!Schema::hasTable('tenant_product_collections')) {
        Schema::create('tenant_product_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });
        }

        if (!Schema::hasTable('tenant_product_collection_product')) {
        Schema::create('tenant_product_collection_product', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_product_id');
            $table->unsignedBigInteger('tenant_product_collection_id');
            $table->timestamps();
            $table->unique(['tenant_product_id', 'tenant_product_collection_id'], 'tenant_product_collection_unique');
            $table->foreign('tenant_product_id', 'tpcp_product_fk')->references('id')->on('tenant_products')->cascadeOnDelete();
            $table->foreign('tenant_product_collection_id', 'tpcp_collection_fk')->references('id')->on('tenant_product_collections')->cascadeOnDelete();
        });
        }

        if (!Schema::hasTable('tenant_product_colors')) {
        Schema::create('tenant_product_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_product_id')->constrained()->cascadeOnDelete();
            $table->string('color_name');
            $table->string('hex_code', 10)->default('#808080');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('tenant_product_images')) {
        Schema::create('tenant_product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_product_id');
            $table->unsignedBigInteger('tenant_product_color_id')->nullable();
            $table->string('image_path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_product_id', 'tpi_product_fk')->references('id')->on('tenant_products')->cascadeOnDelete();
            $table->foreign('tenant_product_color_id', 'tpi_color_fk')->references('id')->on('tenant_product_colors')->cascadeOnDelete();
        });
        }

        if (!Schema::hasTable('tenant_product_videos')) {
        Schema::create('tenant_product_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_product_id')->constrained()->cascadeOnDelete();
            $table->string('video_path');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('tenant_product_sizes')) {
        Schema::create('tenant_product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_product_id')->constrained()->cascadeOnDelete();
            $table->string('size_label', 50);
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
        }

        if (!Schema::hasTable('tenant_product_variants')) {
        Schema::create('tenant_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_product_id');
            $table->unsignedBigInteger('tenant_product_color_id')->nullable();
            $table->unsignedBigInteger('tenant_product_size_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('sku', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_product_id', 'tenant_product_color_id', 'tenant_product_size_id'], 'tenant_variant_combo_unique');
            $table->foreign('tenant_product_id', 'tpv_product_fk')->references('id')->on('tenant_products')->cascadeOnDelete();
            $table->foreign('tenant_product_color_id', 'tpv_color_fk')->references('id')->on('tenant_product_colors')->nullOnDelete();
            $table->foreign('tenant_product_size_id', 'tpv_size_fk')->references('id')->on('tenant_product_sizes')->nullOnDelete();
        });
        }

        if (!Schema::hasTable('tenant_product_attributes')) {
        Schema::create('tenant_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });
        }

        if (!Schema::hasTable('tenant_product_attribute_values')) {
        Schema::create('tenant_product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_product_attribute_id');
            $table->string('name');
            $table->string('slug');
            $table->string('hex_code', 10)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('tenant_product_attribute_id', 'tpav_attribute_fk')->references('id')->on('tenant_product_attributes')->cascadeOnDelete();
        });
        }

        Schema::table('tenant_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('shipping_phone');
            }
            if (!Schema::hasColumn('tenant_orders', 'notes')) {
                $table->text('notes')->nullable()->after('shipping_pincode');
            }
            if (!Schema::hasColumn('tenant_orders', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('tenant_orders', 'total')) {
                $table->decimal('total', 10, 2)->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('tenant_orders', 'payment_status')) {
                $table->string('payment_status', 30)->default('pending')->after('payment_method');
            }
            if (!Schema::hasColumn('tenant_orders', 'payment_id')) {
                $table->string('payment_id', 100)->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('tenant_orders', 'payment_order_id')) {
                $table->string('payment_order_id', 100)->nullable()->after('payment_id');
            }
            if (!Schema::hasColumn('tenant_orders', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('payment_order_id');
            }
        });

        Schema::table('tenant_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('tenant_order_items', 'tenant_product_variant_id')) {
                $table->foreignId('tenant_product_variant_id')->nullable()->after('tenant_product_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('tenant_order_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('tenant_product_variant_id');
            }
            if (!Schema::hasColumn('tenant_order_items', 'color_name')) {
                $table->string('color_name')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('tenant_order_items', 'size_label')) {
                $table->string('size_label')->nullable()->after('color_name');
            }
            if (!Schema::hasColumn('tenant_order_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('unit_price');
            }
        });

        DB::table('tenant_products')->orderBy('id')->each(function ($product) {
            $slug = $product->slug ?: Str::slug($product->name ?: 'product-' . $product->id);
            $base = $slug;
            $i = 2;
            while (DB::table('tenant_products')->where('tenant_id', $product->tenant_id)->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $base . '-' . $i++;
            }

            $categoryId = null;
            if (!empty($product->category)) {
                $categorySlug = Str::slug($product->category);
                $existing = DB::table('tenant_product_categories')->where('tenant_id', $product->tenant_id)->where('slug', $categorySlug)->first();
                $categoryId = $existing?->id ?: DB::table('tenant_product_categories')->insertGetId([
                    'tenant_id' => $product->tenant_id,
                    'name' => $product->category,
                    'slug' => $categorySlug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('tenant_products')->where('id', $product->id)->update([
                'slug' => $slug,
                'tenant_product_category_id' => $categoryId,
                'cover_image' => $product->cover_image ?: $product->photo,
                'updated_at' => now(),
            ]);

            if (!empty($product->photo) && !DB::table('tenant_product_images')->where('tenant_product_id', $product->id)->exists()) {
                DB::table('tenant_product_images')->insert([
                    'tenant_product_id' => $product->id,
                    'image_path' => $product->photo,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        DB::table('tenants')->where('site_type', 'shopping')->orderBy('id')->each(function ($tenant) {
            $modules = json_decode($tenant->modules ?? '[]', true) ?: [];
            $modules = array_values(array_unique(array_merge($modules, [
                'product_categories',
                'product_gallery',
            ])));

            DB::table('tenants')->where('id', $tenant->id)->update([
                'modules' => json_encode($modules),
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_product_attribute_values');
        Schema::dropIfExists('tenant_product_attributes');
        Schema::dropIfExists('tenant_product_variants');
        Schema::dropIfExists('tenant_product_sizes');
        Schema::dropIfExists('tenant_product_videos');
        Schema::dropIfExists('tenant_product_images');
        Schema::dropIfExists('tenant_product_colors');
        Schema::dropIfExists('tenant_product_collection_product');
        Schema::dropIfExists('tenant_product_collections');
        Schema::dropIfExists('tenant_product_categories');

        Schema::table('tenant_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_product_variant_id');
            $table->dropColumn(['product_name', 'color_name', 'size_label', 'total_price']);
        });

        Schema::table('tenant_orders', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'notes', 'subtotal', 'total', 'payment_status', 'payment_id', 'payment_order_id', 'admin_notes']);
        });

        Schema::table('tenant_products', function (Blueprint $table) {
            $table->dropColumn('tenant_product_category_id');
            $table->dropColumn([
                'slug',
                'type',
                'cover_image',
                'sku',
                'stock',
                'material',
                'weight',
                'care_instructions',
                'heritage_note',
                'is_top_seller',
                'is_featured',
                'is_active',
                'sort_order',
            ]);
        });
    }
};
