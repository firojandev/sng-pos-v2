<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $backupPermissions = Permissions::for('backup');

        foreach ($backupPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Grant all backup permissions to Super Admin role
        $superAdminRole = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($superAdminRole) {
            $superAdminRole->givePermissionTo($backupPermissions);
        }

        // Grant to existing Admin roles
        $adminRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Shop Admin', 'Owner', 'Shop Owner'])
            ->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($backupPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $backupPermissions = Permissions::for('backup');
        $permissionIds = Permission::whereIn('name', $backupPermissions)->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::whereIn('id', $permissionIds)->delete();
        }
    }
};
