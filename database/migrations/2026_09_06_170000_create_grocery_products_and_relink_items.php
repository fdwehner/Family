<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'grocery_catalog_seeded_at')) {
                $table->timestamp('grocery_catalog_seeded_at')->nullable()->after('remember_token');
            }
        });

        if (! Schema::hasTable('grocery_products')) {
            Schema::create('grocery_products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users', 'id', 'grocery_products_user_id_fk')->cascadeOnDelete();
                $table->string('slug', 64)->nullable();
                $table->string('name');
                $table->string('brand')->nullable();
                $table->string('unit', 32);
                $table->string('category', 32);
                $table->string('image_path')->nullable();
                $table->boolean('is_featured')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->unique(['user_id', 'slug'], 'grocery_products_user_slug_unique');
                $table->index(['user_id', 'is_featured'], 'grocery_products_user_featured_idx');
                $table->index(['user_id', 'category'], 'grocery_products_user_category_idx');
            });
        }

        if (Schema::hasTable('grocery_items') && ! Schema::hasColumn('grocery_items', 'grocery_product_id')) {
            Schema::table('grocery_items', function (Blueprint $table) {
                $table->foreignId('grocery_product_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('grocery_products', 'id', 'grocery_items_product_id_fk')
                    ->cascadeOnDelete();
            });

            $items = DB::table('grocery_items')->orderBy('id')->get();

            foreach ($items as $item) {
                $productId = DB::table('grocery_products')->insertGetId([
                    'user_id' => $item->user_id,
                    'slug' => null,
                    'name' => $item->name,
                    'brand' => null,
                    'unit' => $item->unit ?: 'pcs',
                    'category' => $item->category,
                    'image_path' => null,
                    'is_featured' => true,
                    'sort_order' => 500,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('grocery_items')->where('id', $item->id)->update([
                    'grocery_product_id' => $productId,
                    'quantity' => $item->quantity ?? 1,
                ]);
            }

            $orphanIds = DB::table('grocery_items')->whereNull('grocery_product_id')->pluck('id');
            if ($orphanIds->isNotEmpty()) {
                DB::table('grocery_items')->whereIn('id', $orphanIds)->delete();
            }

            Schema::table('grocery_items', function (Blueprint $table) {
                if (Schema::hasIndex('grocery_items', 'grocery_items_user_category_idx')) {
                    $table->dropIndex('grocery_items_user_category_idx');
                }

                foreach (['name', 'unit', 'category', 'notes'] as $column) {
                    if (Schema::hasColumn('grocery_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });

            Schema::table('grocery_items', function (Blueprint $table) {
                $table->unique(['user_id', 'grocery_product_id'], 'grocery_items_user_product_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('grocery_items', function (Blueprint $table) {
            if (Schema::hasIndex('grocery_items', 'grocery_items_user_product_unique')) {
                $table->dropUnique('grocery_items_user_product_unique');
            }

            if (Schema::hasColumn('grocery_items', 'grocery_product_id')) {
                $table->dropConstrainedForeignId('grocery_product_id');
            }

            if (! Schema::hasColumn('grocery_items', 'name')) {
                $table->string('name')->nullable();
                $table->string('unit', 32)->nullable();
                $table->string('category', 32)->nullable();
                $table->text('notes')->nullable();
            }
        });

        Schema::dropIfExists('grocery_products');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'grocery_catalog_seeded_at')) {
                $table->dropColumn('grocery_catalog_seeded_at');
            }
        });
    }
};
