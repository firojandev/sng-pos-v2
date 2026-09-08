<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\Shop\DataTables\PlansDataTable;
use Modules\Shop\Http\Requests\StorePlanRequest;
use Modules\Shop\Http\Requests\UpdatePlanRequest;
use Modules\Shop\Models\Plan;
use Revoltify\Subscriptionify\Enums\FeatureType;
use Revoltify\Subscriptionify\Models\Feature;
use Revoltify\Subscriptionify\Services\FeatureResolver;

class PlanController extends Controller
{
    public function index(PlansDataTable $dataTable)
    {
        return $dataTable->render('shop::plans.index');
    }

    public function create(): View
    {
        return view('shop::plans.create', ['plan' => new Plan, 'features' => Features::all()]);
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $featureSlugs = $data['features'] ?? [];
        unset($data['features']);

        $plan = Plan::create($data);
        $this->syncFeatures($plan, $featureSlugs);

        return redirect()->route('plans.index')->with('status', 'প্ল্যান সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Plan $plan): View
    {
        $plan->load('features');

        return view('shop::plans.edit', ['plan' => $plan, 'features' => Features::all()]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();
        $featureSlugs = $data['features'] ?? [];
        unset($data['features']);

        $plan->update($data);
        $this->syncFeatures($plan, $featureSlugs);

        return redirect()->route('plans.index')->with('status', 'প্ল্যান হালনাগাদ করা হয়েছে');
    }

    /**
     * Sync the plan's granted module/feature toggles against Subscriptionify's
     * real feature_plan pivot (the flat `plans.features` JSON column this
     * used to write to has been retired in favor of this relation).
     *
     * @param  string[]  $slugs
     */
    private function syncFeatures(Plan $plan, array $slugs): void
    {
        $labels = Features::all();

        $ids = collect($slugs)->map(fn (string $slug) => Feature::firstOrCreate(
            ['slug' => $slug],
            ['name' => $labels[$slug]['en'] ?? $slug, 'type' => FeatureType::Toggle],
        )->id);

        $plan->features()->sync($ids->mapWithKeys(fn ($id) => [$id => ['value' => '0']]));

        // Invalidate Subscriptionify's resolved-feature cache so any shop on
        // this plan sees the updated grants immediately within this process
        // (relevant under persistent workers; harmless per-request otherwise).
        app(FeatureResolver::class)->flush();
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->exists()) {
            return redirect()->route('plans.index')->with('status', 'এই প্ল্যানে সক্রিয় সাবস্ক্রিপশন আছে, মুছে ফেলা যাবে না');
        }

        $plan->delete();

        return redirect()->route('plans.index')->with('status', 'প্ল্যান মুছে ফেলা হয়েছে');
    }
}
