<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Features;
use Modules\Core\Support\Permissions;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Revoltify\Subscriptionify\Services\FeatureResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * The report feature keys introduced by this migration.
     *
     * @var string[]
     */
    private array $features = ['report-sales-vat'];

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

        // Grant the new feature to subscription plans
        $labels = Features::all();

        $featureIds = collect($this->features)->mapWithKeys(fn (string $slug) => [
            $slug => Feature::firstOrCreate(
                ['slug' => $slug],
                ['name' => $labels[$slug]['en'] ?? $slug, 'type' => FeatureType::Toggle],
            )->id,
        ]);

        $plans = Plan::all();

        foreach ($plans as $plan) {
            $plan->features()->syncWithoutDetaching(
                $featureIds->mapWithKeys(fn ($id) => [$id => ['value' => '0']])
            );
        }

        if (class_exists(FeatureResolver::class)) {
            app(FeatureResolver::class)->flush();
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

        $featureIds = Feature::whereIn('slug', $this->features)->pluck('id');

        if ($featureIds->isNotEmpty()) {
            $plans = Plan::all();

            foreach ($plans as $plan) {
                $plan->features()->detach($featureIds);
            }

            Feature::whereIn('id', $featureIds)->delete();
        }

        if (class_exists(FeatureResolver::class)) {
            app(FeatureResolver::class)->flush();
        }
    }
};
