<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('subscriptionify.tables.plans', 'plans');

        if (! Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('price', 10, 2)->default(0);
                $table->boolean('is_free')->default(false);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('trial_days')->default(0);
                $table->unsignedInteger('billing_period')->default(1);
                $table->string('billing_interval')->default('month');
                $table->unsignedInteger('grace_days')->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            Schema::table($table, function (Blueprint $table): void {
                if (! Schema::hasColumn($table->getTable(), 'description')) {
                    $table->text('description')->nullable()->after('slug');
                }
                if (! Schema::hasColumn($table->getTable(), 'is_free')) {
                    $table->boolean('is_free')->default(false)->after('price');
                }
                if (! Schema::hasColumn($table->getTable(), 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('is_free');
                }
                if (! Schema::hasColumn($table->getTable(), 'trial_days')) {
                    $table->unsignedInteger('trial_days')->default(0)->after('is_active');
                }
                if (! Schema::hasColumn($table->getTable(), 'billing_period')) {
                    $table->unsignedInteger('billing_period')->default(1)->after('trial_days');
                }
                if (! Schema::hasColumn($table->getTable(), 'billing_interval')) {
                    $table->string('billing_interval')->default('month')->after('billing_period');
                }
                if (! Schema::hasColumn($table->getTable(), 'grace_days')) {
                    $table->unsignedInteger('grace_days')->default(0)->after('billing_interval');
                }
                if (! Schema::hasColumn($table->getTable(), 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('grace_days');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(config('subscriptionify.tables.plans', 'plans'));
    }
};
