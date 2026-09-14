<?php

use Illuminate\Database\Migrations\Migration;
use Modules\Core\Support\Features;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Revoltify\Subscriptionify\Services\FeatureResolver;

return new class extends Migration
{
    /**
     * The report feature slugs introduced by this migration.
     *
     * @var string[]
     */
    private array $slugs = ['report-financial-position', 'report-balance-sheet'];

    /**
     * Run the migrations.
     *
     * Grants the new feature slugs to the Professional and Enterprise plans
     * by default (matching the other report-* features), without disturbing
     * any feature grants those plans already have.
     */
    public function up(): void
    {
        $labels = Features::all();

        $featureIds = collect($this->slugs)->mapWithKeys(fn (string $slug) => [
            $slug => Feature::firstOrCreate(
                ['slug' => $slug],
                ['name' => $labels[$slug]['en'] ?? $slug, 'type' => FeatureType::Toggle],
            )->id,
        ]);

        $plans = Plan::whereIn('slug', ['professional', 'enterprise'])->get();

        foreach ($plans as $plan) {
            $plan->features()->syncWithoutDetaching(
                $featureIds->mapWithKeys(fn ($id) => [$id => ['value' => '0']])
            );
        }

        app(FeatureResolver::class)->flush();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $featureIds = Feature::whereIn('slug', $this->slugs)->pluck('id');

        if ($featureIds->isEmpty()) {
            return;
        }

        $plans = Plan::whereIn('slug', ['professional', 'enterprise'])->get();

        foreach ($plans as $plan) {
            $plan->features()->detach($featureIds);
        }

        app(FeatureResolver::class)->flush();
    }
};
