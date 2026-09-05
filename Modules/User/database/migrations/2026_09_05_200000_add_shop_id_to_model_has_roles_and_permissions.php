<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamsKey = $columnNames['team_foreign_key'] ?? 'shop_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $morphKey = $columnNames['model_morph_key'] ?? 'model_id';

        // 1. Add shop_id to model_has_roles
        if (! Schema::hasColumn($tableNames['model_has_roles'], $teamsKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($teamsKey) {
                $table->unsignedBigInteger($teamsKey)->default(0)->after('model_id');
                $table->index($teamsKey, 'model_has_roles_team_foreign_key_index');
            });

            // Backfill shop_id from users table
            if (Schema::hasTable('users')) {
                DB::statement("
                    UPDATE {$tableNames['model_has_roles']} mhr
                    JOIN users u ON u.id = mhr.{$morphKey} AND mhr.model_type = 'App\\\\Models\\\\User'
                    SET mhr.{$teamsKey} = COALESCE(u.shop_id, 0)
                ");
            }

            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $teamsKey, $pivotRole, $morphKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([$pivotRole]);
                }
                $table->dropPrimary();

                $table->primary([$teamsKey, $pivotRole, $morphKey, 'model_type'], 'model_has_roles_role_model_type_primary');

                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign($pivotRole)
                        ->references('id')
                        ->on($tableNames['roles'])
                        ->cascadeOnDelete();
                }
            });
        }

        // 2. Add shop_id to model_has_permissions
        if (! Schema::hasColumn($tableNames['model_has_permissions'], $teamsKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($teamsKey) {
                $table->unsignedBigInteger($teamsKey)->default(0)->after('model_id');
                $table->index($teamsKey, 'model_has_permissions_team_foreign_key_index');
            });

            if (Schema::hasTable('users')) {
                DB::statement("
                    UPDATE {$tableNames['model_has_permissions']} mhp
                    JOIN users u ON u.id = mhp.{$morphKey} AND mhp.model_type = 'App\\\\Models\\\\User'
                    SET mhp.{$teamsKey} = COALESCE(u.shop_id, 0)
                ");
            }

            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $teamsKey, $pivotPermission, $morphKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([$pivotPermission]);
                }
                $table->dropPrimary();

                $table->primary([$teamsKey, $pivotPermission, $morphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');

                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign($pivotPermission)
                        ->references('id')
                        ->on($tableNames['permissions'])
                        ->cascadeOnDelete();
                }
            });
        }

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamsKey = $columnNames['team_foreign_key'] ?? 'shop_id';
        $pivotRole = $columnNames['role_pivot_key'] ?? 'role_id';
        $pivotPermission = $columnNames['permission_pivot_key'] ?? 'permission_id';
        $morphKey = $columnNames['model_morph_key'] ?? 'model_id';

        if (Schema::hasColumn($tableNames['model_has_roles'], $teamsKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $teamsKey, $pivotRole, $morphKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([$pivotRole]);
                }
                $table->dropPrimary();
                $table->dropColumn($teamsKey);
                $table->primary([$pivotRole, $morphKey, 'model_type'], 'model_has_roles_role_model_type_primary');
                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign($pivotRole)->references('id')->on($tableNames['roles'])->cascadeOnDelete();
                }
            });
        }

        if (Schema::hasColumn($tableNames['model_has_permissions'], $teamsKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $teamsKey, $pivotPermission, $morphKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([$pivotPermission]);
                }
                $table->dropPrimary();
                $table->dropColumn($teamsKey);
                $table->primary([$pivotPermission, $morphKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign($pivotPermission)->references('id')->on($tableNames['permissions'])->cascadeOnDelete();
                }
            });
        }
    }
};
