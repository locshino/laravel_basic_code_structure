<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Foreign key to categories table (nullable allows products without category)
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('SET NULL');
            $table->string('name');
            $table->string('slug')->unique(); // Slug for friendly URLs, must be unique
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Price with 2 decimal places
            $table->unsignedInteger('stock')->default(0); // Stock quantity, non-negative
            $table->string('image_path')->nullable(); // Path to product image
            $table->boolean('is_active')->default(true); // Status to show/hide product
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
