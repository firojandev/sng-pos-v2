<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Split coarse `write` permissions into granular action labels:
     * `create`, `edit`, `delete` (and custom actions such as receive, return, payment, etc.).
     */
    public function up(): void
    {
        // 1. Create all new permissions
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 2. Transfer existing role permissions
        foreach (Role::where('guard_name', 'web')->get() as $role) {
            foreach (Features::keys() as $feature) {
                $hasWrite = false;
                try {
                    $hasWrite = $role->hasPermissionTo("{$feature}.write");
                } catch (Throwable $e) {
                    $hasWrite = false;
                }

                $hasView = false;
                try {
                    $hasView = $role->hasPermissionTo("{$feature}.view");
                } catch (Throwable $e) {
                    $hasView = false;
                }

                if ($hasWrite) {
                    // Give all actions defined for this feature
                    $actions = Permissions::actionsFor($feature);
                    $newPerms = array_map(fn ($action) => "{$feature}.{$action}", $actions);
                    $role->givePermissionTo($newPerms);
                } elseif ($hasView) {
                    // If they had view, grant print permission if available for the feature
                    if (in_array('print', Permissions::actionsFor($feature))) {
                        $role->givePermissionTo("{$feature}.print");
                    }
                }
            }
        }

        // 3. Remove obsolete *.write permissions
        $oldWritePerms = Permission::where('name', 'like', '%.write')->pluck('id');
        if ($oldWritePerms->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $oldWritePerms)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $oldWritePerms)->delete();
            Permission::whereIn('id', $oldWritePerms)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (Features::keys() as $feature) {
            Permission::firstOrCreate(['name' => "{$feature}.write", 'guard_name' => 'web']);
        }

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            foreach (Features::keys() as $feature) {
                $hasCreateOrEdit = false;
                try {
                    $hasCreateOrEdit = $role->hasPermissionTo("{$feature}.create") || $role->hasPermissionTo("{$feature}.edit");
                } catch (Throwable $e) {
                    $hasCreateOrEdit = false;
                }

                if ($hasCreateOrEdit) {
                    $role->givePermissionTo("{$feature}.write");
                }
            }
        }
    }
};
