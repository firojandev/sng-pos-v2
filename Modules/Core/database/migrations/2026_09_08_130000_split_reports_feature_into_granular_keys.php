<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Support\Features;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * The granular report feature keys that replace the single 'reports' key.
     *
     * @var list<string>
     */
    private array $newKeys = [
        'report-sales',
        'report-purchase',
        'report-stock',
        'report-products',
        'report-profit-loss',
        'report-income',
        'report-expense',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $labels = Features::all();

        // 1. Provision the 7 new Feature rows.
        $newFeatureIds = collect($this->newKeys)->map(fn (string $slug) => Feature::firstOrCreate(
            ['slug' => $slug],
            ['name' => $labels[$slug]['en'] ?? $slug, 'type' => FeatureType::Toggle],
        )->id);

        // 2. Carry every plan that had 'reports' over to all 7 new keys.
        $oldFeature = Feature::where('slug', 'reports')->first();
        if ($oldFeature) {
            foreach ($oldFeature->plans as $plan) {
                $plan->features()->syncWithoutDetaching(
                    $newFeatureIds->mapWithKeys(fn ($id) => [$id => ['value' => '0']])
                );
            }

            $oldFeature->plans()->detach();
            $oldFeature->delete();
        }

        // 3. Provision the 14 new permissions and carry over role grants.
        $newPermissionNames = collect($this->newKeys)
            ->flatMap(fn (string $key) => ["{$key}.view", "{$key}.print"]);

        foreach ($newPermissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $rolesWithOldReportsAccess = Role::whereHas('permissions', fn ($q) => $q->where('name', 'reports.view'))
            ->whereHas('permissions', fn ($q) => $q->where('name', 'reports.print'))
            ->get();

        foreach ($rolesWithOldReportsAccess as $role) {
            $role->givePermissionTo($newPermissionNames->all());
        }

        // 4. Retire the old 'reports' permissions (cascades to role/model pivots).
        Permission::whereIn('name', ['reports.view', 'reports.print'])->delete();
    }

    /**
     * Reverse the migrations (best-effort).
     */
    public function down(): void
    {
        $oldFeature = Feature::firstOrCreate(
            ['slug' => 'reports'],
            ['name' => 'রিপোর্ট ও অ্যানালিটিক্স (Reports)', 'type' => FeatureType::Toggle],
        );

        Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reports.print', 'guard_name' => 'web']);

        $newFeatures = Feature::whereIn('slug', $this->newKeys)->get();

        foreach ($newFeatures as $feature) {
            foreach ($feature->plans as $plan) {
                $plan->features()->syncWithoutDetaching([$oldFeature->id => ['value' => '0']]);
            }
        }

        $rolesWithNewReportsAccess = Role::query();
        foreach ($this->newKeys as $key) {
            $rolesWithNewReportsAccess->whereHas('permissions', fn ($q) => $q->where('name', "{$key}.view"));
        }

        foreach ($rolesWithNewReportsAccess->get() as $role) {
            $role->givePermissionTo(['reports.view', 'reports.print']);
        }

        Feature::whereIn('slug', $this->newKeys)->each(function (Feature $feature) {
            $feature->plans()->detach();
            $feature->delete();
        });

        $newPermissionNames = collect($this->newKeys)
            ->flatMap(fn (string $key) => ["{$key}.view", "{$key}.print"]);
        Permission::whereIn('name', $newPermissionNames)->delete();
    }
};
