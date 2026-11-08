<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Loyalty: a company runs one programme (how much spending earns how many
 * points, what a point is worth, optional expiry); customers join it as
 * members; every earned, redeemed, expired or reversed point is a row in
 * the points ledger. Each shop can switch the programme off.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->decimal('spend_amount', 12, 2)->default(100);
            $table->unsignedInteger('points_per_spend')->default(1);
            $table->decimal('point_value', 12, 2)->default(1);
            $table->unsignedInteger('min_redeem_points')->default(0);
            $table->unsignedTinyInteger('max_redeem_percent')->default(100);
            $table->unsignedInteger('points_expire_after_days')->nullable();
            $table->timestamps();
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->boolean('loyalty_enabled')->default(true)->after('business_type');
        });

        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('card_no');
            $table->date('joined_at');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['company_id', 'card_no']);
        });

        Schema::create('loyalty_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['earn', 'redeem', 'expire', 'adjust', 'reverse']);
            $table->integer('points');
            $table->decimal('value', 12, 2)->default(0);
            $table->unsignedInteger('remaining')->default(0);
            $table->date('expires_at')->nullable();
            $table->nullableMorphs('source');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['customer_id', 'type']);
            $table->index(['company_id', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_point_transactions');
        Schema::dropIfExists('customer_memberships');

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('loyalty_enabled');
        });

        Schema::dropIfExists('loyalty_programs');
    }
};
