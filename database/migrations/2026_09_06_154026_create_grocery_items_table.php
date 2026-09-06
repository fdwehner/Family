<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('grocery_items')) {
            return;
        }

        Schema::create('grocery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id', 'grocery_items_user_id_fk')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('quantity', 8, 2)->nullable();
            $table->string('unit', 32)->nullable();
            $table->string('category', 32);
            $table->text('notes')->nullable();
            $table->boolean('is_purchased')->default(false);
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_purchased'], 'grocery_items_user_purchased_idx');
            $table->index(['user_id', 'category'], 'grocery_items_user_category_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grocery_items');
    }
};
