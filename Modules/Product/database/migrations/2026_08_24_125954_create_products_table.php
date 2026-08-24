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
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('image_url')->nullable();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->text('short_description')->nullable();
            $table->unsignedInteger('alert_qty')->default(0);
            $table->boolean('is_vat')->default(false);
            $table->decimal('vat_percentage', 5, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('has_warranty')->default(false);
            $table->unsignedInteger('warranty_duration')->nullable();
            $table->enum('warranty_type', ['day', 'month', 'year'])->nullable();
            $table->boolean('has_expiry')->default(false);
            $table->date('expiry_date')->nullable();
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
