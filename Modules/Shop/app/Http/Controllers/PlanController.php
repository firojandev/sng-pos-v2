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
        Plan::create($request->validated());

        return redirect()->route('plans.index')->with('status', 'প্ল্যান সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Plan $plan): View
    {
        return view('shop::plans.edit', ['plan' => $plan, 'features' => Features::all()]);
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $plan->update($request->validated());

        return redirect()->route('plans.index')->with('status', 'প্ল্যান হালনাগাদ করা হয়েছে');
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
