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
        if (! Schema::hasColumn('subscriptions', 'shop_id')) {
            return;
        }

        $hasShopForeignKey = collect(Schema::getForeignKeys('subscriptions'))
            ->contains(fn (array $foreignKey) => $foreignKey['columns'] === ['shop_id']);

        Schema::table('subscriptions', function (Blueprint $table) use ($hasShopForeignKey) {
            if ($hasShopForeignKey) {
                $table->dropForeign(['shop_id']);
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasIndex('subscriptions', 'subscriptions_shop_id_unique')) {
                $table->dropUnique('subscriptions_shop_id_unique');
            }
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('shop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('subscriptions', 'shop_id')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->foreignId('shop_id')->nullable()->after('subscribable_id')->constrained()->cascadeOnDelete();
            });
        }
    }
};
