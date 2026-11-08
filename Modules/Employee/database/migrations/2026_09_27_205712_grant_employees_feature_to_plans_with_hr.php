<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Modules\Core\Support\Features;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Revoltify\Subscriptionify\Services\FeatureResolver;

/**
 * Attendance, leave and payroll work on the employee list, so every plan
 * with an HR module also gets the Employees feature.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $employees = Feature::firstOrCreate(
            ['slug' => 'employees'],
            ['name' => Features::all()['employees']['en'] ?? 'Employees', 'type' => FeatureType::Toggle],
        );

        $hrFeatureIds = Feature::whereIn('slug', ['attendance', 'leave', 'hr-setup', 'payroll'])->pluck('id');
        $planIds = DB::table('feature_plan')->whereIn('feature_id', $hrFeatureIds)->distinct()->pluck('plan_id');
        $alreadyGranted = DB::table('feature_plan')->where('feature_id', $employees->id)->pluck('plan_id');

        foreach ($planIds->diff($alreadyGranted) as $planId) {
            DB::table('feature_plan')->insert(['plan_id' => $planId, 'feature_id' => $employees->id, 'value' => '0']);
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
        // Plans keep the feature; the super admin can take it off a plan.
    }
};
