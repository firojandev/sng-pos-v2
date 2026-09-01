<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('subscriptionify.tables.subscriptions', 'subscriptions');

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table): void {
                $table->id();
                $table->morphs('subscribable');
                $table->foreignId('plan_id');
                $table->string('status')->default('active');
                $table->timestamp('starts_at');
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamp('renewed_at')->nullable();
                $table->timestamps();

                $table->index(['subscribable_type', 'subscribable_id', 'status']);
                $table->index('plan_id');
                $table->index('status');
            });
        } else {
            Schema::table($table, function (Blueprint $table): void {
                if (! Schema::hasColumn($table->getTable(), 'subscribable_type')) {
                    $table->string('subscribable_type')->nullable()->after('id');
                }
                if (! Schema::hasColumn($table->getTable(), 'subscribable_id')) {
                    $table->unsignedBigInteger('subscribable_id')->nullable()->after('subscribable_type');
                }
                if (! Schema::hasColumn($table->getTable(), 'starts_at')) {
                    $table->timestamp('starts_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn($table->getTable(), 'ends_at')) {
                    $table->timestamp('ends_at')->nullable()->after('starts_at');
                }
                if (! Schema::hasColumn($table->getTable(), 'renewed_at')) {
                    $table->timestamp('renewed_at')->nullable()->after('cancelled_at');
                }
            });

            // Backfill polymorphic morphs from shop_id if present
            if (Schema::hasColumn($table, 'shop_id')) {
                DB::table($table)
                    ->whereNull('subscribable_type')
                    ->update([
                        'subscribable_type' => 'Modules\\Shop\\Models\\Shop',
                        'subscribable_id' => DB::raw('shop_id'),
                        'starts_at' => DB::raw('COALESCE(current_period_start, created_at)'),
                        'ends_at' => DB::raw('current_period_end'),
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(config('subscriptionify.tables.subscriptions', 'subscriptions'));
    }
};
