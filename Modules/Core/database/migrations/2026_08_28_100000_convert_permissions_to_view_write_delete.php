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
     * Replace the single all-or-nothing permission per feature (e.g. "sales")
     * with three granular permissions (e.g. "sales.view", "sales.write",
     * "sales.delete"), carrying over each role's existing access as full
     * view+write+delete so nobody loses access they already had.
     */
    public function up(): void
    {
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            $oldFeatureNames = $role->permissions()->whereIn('name', Features::keys())->pluck('name');

            foreach ($oldFeatureNames as $feature) {
                $role->givePermissionTo(Permissions::for($feature));
            }
        }

        $oldPermissionIds = Permission::whereIn('name', Features::keys())->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $oldPermissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $oldPermissionIds)->delete();
        Permission::whereIn('name', Features::keys())->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (Features::keys() as $feature) {
            Permission::firstOrCreate(['name' => $feature, 'guard_name' => 'web']);
        }

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            foreach (Features::keys() as $feature) {
                if ($role->hasPermissionTo("{$feature}.view")) {
                    $role->givePermissionTo($feature);
                }
            }
        }

        $newPermissionIds = Permission::whereIn('name', Permissions::all())->pluck('id');
        DB::table('role_has_permissions')->whereIn('permission_id', $newPermissionIds)->delete();
        DB::table('model_has_permissions')->whereIn('permission_id', $newPermissionIds)->delete();
        Permission::whereIn('name', Permissions::all())->delete();
    }
};
