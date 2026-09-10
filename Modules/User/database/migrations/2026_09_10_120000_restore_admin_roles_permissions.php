<?php

use Illuminate\Database\Migrations\Migration;
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
        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::where('guard_name', 'web')->get();

        $adminRoles = Role::where('name', 'Admin')
            ->whereNotNull('shop_id')
            ->get();

        foreach ($adminRoles as $adminRole) {
            $adminRole->syncPermissions($allPermissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
