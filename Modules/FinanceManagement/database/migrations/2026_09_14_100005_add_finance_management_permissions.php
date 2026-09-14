<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * The Finance Management feature keys introduced by this module.
     *
     * @var string[]
     */
    private array $features = ['assets', 'debts', 'lend', 'security-money'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = array_merge(...array_map(fn (string $feature) => Permissions::for($feature), $this->features));

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $adminRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Shop Admin', 'Owner', 'Shop Owner'])
            ->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permissions = array_merge(...array_map(fn (string $feature) => Permissions::for($feature), $this->features));
        $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::whereIn('id', $permissionIds)->delete();
        }
    }
};
