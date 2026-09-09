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
        // 1. Provision all dashboard permissions
        $dashboardPermissions = Permissions::for('dashboard');
        foreach ($dashboardPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // 3. Grant to existing Admin roles
        $adminRoles = Role::where('guard_name', 'web')
            ->whereIn('name', ['Admin', 'Shop Admin', 'Owner', 'Shop Owner'])
            ->get();

        foreach ($adminRoles as $role) {
            $role->givePermissionTo($dashboardPermissions);
        }

        // 4. For other roles, intelligently map based on existing capabilities
        $otherRoles = Role::where('guard_name', 'web')
            ->whereNotIn('name', ['Admin', 'Shop Admin', 'Owner', 'Shop Owner'])
            ->get();

        foreach ($otherRoles as $role) {
            $rolePerms = $role->permissions->pluck('name')->toArray();
            if (empty($rolePerms)) {
                continue;
            }

            $toGrant = ['dashboard.view'];

            if (in_array('sales.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-sales';
            }
            if (in_array('purchase.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-purchase';
            }
            if (in_array('expense.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-expense';
            }
            if (in_array('sales.view', $rolePerms, true) && in_array('income.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-product-profit';
                $toGrant[] = 'dashboard.stat-total-profit';
            }
            if (in_array('stock.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-stock-qty';
                $toGrant[] = 'dashboard.stat-stock-value';
            }
            if (in_array('customers.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-receivable';
            }
            if (in_array('suppliers.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-payable';
            }
            if (in_array('accounts.view', $rolePerms, true) || in_array('cashbox.view', $rolePerms, true)) {
                $toGrant[] = 'dashboard.stat-balance';
                $toGrant[] = 'dashboard.stat-cash';
                $toGrant[] = 'dashboard.stat-bank';
                $toGrant[] = 'dashboard.stat-mfs';
            }

            $role->givePermissionTo(array_unique($toGrant));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $dashboardPermissions = Permissions::for('dashboard');
        $permissionIds = Permission::whereIn('name', $dashboardPermissions)->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('role_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('model_has_permissions')->whereIn('permission_id', $permissionIds)->delete();
            Permission::whereIn('id', $permissionIds)->delete();
        }
    }
};
