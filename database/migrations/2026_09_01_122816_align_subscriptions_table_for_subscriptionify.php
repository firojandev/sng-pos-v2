<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('subscriptionify.tables.subscriptions', 'subscriptions');

        if (Schema::hasTable($table)) {
            // If MySQL / MariaDB, alter status column to VARCHAR and make shop_id nullable
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `{$table}` MODIFY `status` VARCHAR(255) NOT NULL DEFAULT 'active'");
                if (Schema::hasColumn($table, 'shop_id')) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `shop_id` BIGINT UNSIGNED NULL");
                }
                if (Schema::hasColumn($table, 'trial_ends_at')) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `trial_ends_at` TIMESTAMP NULL");
                }
            } else {
                Schema::table($table, function (Blueprint $table): void {
                    $table->string('status')->default('active')->change();
                    if (Schema::hasColumn($table->getTable(), 'shop_id')) {
                        $table->unsignedBigInteger('shop_id')->nullable()->change();
                    }
                    if (Schema::hasColumn($table->getTable(), 'trial_ends_at')) {
                        $table->timestamp('trial_ends_at')->nullable()->change();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        // No-op
    }
};
