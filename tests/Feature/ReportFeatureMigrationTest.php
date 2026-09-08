<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportFeatureMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_schema_has_granular_report_keys_and_no_legacy_reports_key(): void
    {
        $this->assertTrue(Permission::where('name', 'report-sales.view')->exists());
        $this->assertTrue(Permission::where('name', 'report-expense.print')->exists());
        $this->assertFalse(Permission::where('name', 'reports.view')->exists());
        $this->assertFalse(Permission::where('name', 'reports.print')->exists());
        $this->assertFalse(Feature::where('slug', 'reports')->exists());
    }

    public function test_migration_carries_legacy_reports_feature_and_permissions_over_to_granular_keys(): void
    {
        // Simulate a pre-migration environment: a legacy 'reports' Feature
        // attached to a plan, plus legacy reports.view/print permissions
        // granted to a role.
        $legacyFeature = Feature::create([
            'name' => 'Reports (legacy)',
            'slug' => 'reports',
            'type' => FeatureType::Toggle,
        ]);

        $plan = Plan::create([
            'name' => 'Legacy Plan',
            'slug' => 'legacy-plan',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'status' => 'active',
        ]);
        $plan->features()->attach($legacyFeature->id, ['value' => '0']);

        Permission::firstOrCreate(['name' => 'reports.view', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'reports.print', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'Legacy Reporter', 'guard_name' => 'web']);
        $role->givePermissionTo(['reports.view', 'reports.print']);

        $migration = require base_path('Modules/Core/database/migrations/2026_09_08_130000_split_reports_feature_into_granular_keys.php');
        $migration->up();

        // Plan carried over to all 7 new keys.
        $plan->refresh();
        $this->assertEqualsCanonicalizing(
            ['report-sales', 'report-purchase', 'report-stock', 'report-products', 'report-profit-loss', 'report-income', 'report-expense'],
            $plan->features->pluck('slug')->all(),
        );

        // Role carried over to the 14 new permissions.
        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('report-sales.view'));
        $this->assertTrue($role->hasPermissionTo('report-expense.print'));

        // Legacy feature/permissions retired.
        $this->assertFalse(Feature::where('slug', 'reports')->exists());
        $this->assertFalse(Permission::where('name', 'reports.view')->exists());
        $this->assertFalse(Permission::where('name', 'reports.print')->exists());
    }
}
