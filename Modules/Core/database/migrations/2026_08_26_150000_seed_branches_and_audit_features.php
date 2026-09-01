<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * New feature keys introduced alongside this migration.
     * Kept as a literal list (not Modules\Core\Support\Features::keys()) so this
     * migration's behavior stays fixed even if that class gains more keys later.
     */
    private array $newKeys = ['branches', 'audit'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->newKeys as $key) {
            Permission::firstOrCreate(['name' => $key, 'guard_name' => 'web']);
        }

        // Any role that already holds every other permission is a "full access" role
        // (e.g. Admin) — extend it with the new keys too, so existing shops aren't
        // suddenly locked out of features their admins already have full access to.
        $existingPermissionCount = Permission::where('guard_name', 'web')
            ->whereNotIn('name', $this->newKeys)
            ->count();

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            $currentCount = $role->permissions()->whereNotIn('name', $this->newKeys)->count();

            if ($existingPermissionCount > 0 && $currentCount === $existingPermissionCount) {
                $role->givePermissionTo($this->newKeys);
            }
        }

        // Give every existing shop the new features too, so their Admins can use
        // them immediately without needing to revisit shop settings.
        foreach (DB::table('shops')->select('id', 'enabled_features')->get() as $shop) {
            $features = json_decode($shop->enabled_features ?? '[]', true) ?: [];
            $merged = array_values(array_unique(array_merge($features, $this->newKeys)));

            DB::table('shops')->where('id', $shop->id)->update([
                'enabled_features' => json_encode($merged),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::whereIn('name', $this->newKeys)->delete();
    }
};
