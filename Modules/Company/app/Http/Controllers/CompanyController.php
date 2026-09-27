<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Company\DataTables\CompaniesDataTable;
use Modules\Company\Http\Requests\UpdateCompanyRequest;
use Modules\Company\Models\Company;
use Modules\Core\Support\BanglaNumber;
use Modules\Shop\Models\Plan;

/**
 * Super-admin management of companies: a company gets a plan that all its
 * shops share; each company (the Default Company included) has admins.
 */
class CompanyController extends Controller
{
    public function index(CompaniesDataTable $dataTable)
    {
        return $dataTable->render('company::index');
    }

    public function create(): View
    {
        return view('company::create', [
            'company' => new Company(['fiscal_year_start_month' => 7, 'currency' => 'BDT']),
            'plans' => Plan::where('status', 'active')->orderBy('sort_order')->orderBy('price')->get(),
        ]);
    }

    public function store(UpdateCompanyRequest $request): RedirectResponse
    {
        $extra = $request->validate([
            'plan_id' => ['nullable', 'integer', Rule::exists('plans', 'id')],
            'subscription_status' => ['nullable', 'in:active,trialing'],
            'current_period_end' => ['nullable', 'date', 'after:today'],
        ]);

        $company = DB::transaction(function () use ($request, $extra) {
            $company = Company::create($request->safe()->except('status') + [
                'slug' => Company::generateUniqueSlug($request->validated('name')),
                'type' => Company::TYPE_COMPANY,
                'status' => 'active',
            ]);

            if (! empty($extra['plan_id'])) {
                $plan = Plan::findOrFail($extra['plan_id']);
                $isYearly = in_array(strtolower((string) ($plan->billing_cycle ?? $plan->billing_interval?->value ?? 'month')), ['yearly', 'year', 'annual'], true);
                $endsAt = ! empty($extra['current_period_end'])
                    ? Carbon::parse($extra['current_period_end'])
                    : ($plan->is_free ? null : now()->addDays($isYearly ? 365 : 30));

                $company->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'status' => $extra['subscription_status'] ?? 'active',
                    'starts_at' => now(),
                    'ends_at' => $endsAt,
                    'current_period_start' => now(),
                    'current_period_end' => $endsAt,
                ]);
            }

            return $company;
        });

        return redirect()->route('companies.edit', $company)->with('status', 'কোম্পানি তৈরি হয়েছে; এখন এর দোকান তৈরি করুন');
    }

    public function edit(Company $company): View
    {
        $company->load(['shops' => fn ($shops) => $shops->orderBy('id'), 'owners']);

        return view('company::edit', [
            'company' => $company,
            'subscription' => $company->billingSubscription()?->loadMissing('plan'),
            'admins' => $company->users()->where(fn ($query) => $query->where('company_user.is_owner', true)->orWhereIn('company_user.role', [Company::ROLE_OWNER, Company::ROLE_ADMIN]))->orderBy('name')->get(),
            'standaloneShops' => $company->isDefault() ? $company->standaloneShops()->latest('shops.id')->limit(20)->get() : collect(),
            'standaloneCount' => $company->isDefault() ? $company->standaloneShops()->count() : 0,
        ]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('companies.edit', $company)->with('status', 'কোম্পানির তথ্য হালনাগাদ করা হয়েছে');
    }

    /**
     * Add a company admin: a new user, or an existing one by phone/email.
     * For the Default Company, its admins see every standalone shop.
     */
    public function storeAdmin(Request $request, Company $company): RedirectResponse
    {
        abort_if($company->isStandalone(), 404);

        if ($request->filled('phone')) {
            $request->merge(['phone' => BanglaNumber::toEn(trim((string) $request->input('phone')))]);
        }

        $existing = User::query()
            ->when($request->filled('phone'), fn ($query) => $query->where('phone', $request->input('phone')))
            ->when(! $request->filled('phone') && $request->filled('email'), fn ($query) => $query->where('email', $request->input('email')))
            ->when(! $request->filled('phone') && ! $request->filled('email'), fn ($query) => $query->whereRaw('1 = 0'))
            ->first();

        $validated = $request->validate([
            'role' => ['required', Rule::in([Company::ROLE_OWNER, Company::ROLE_ADMIN])],
            'name' => [Rule::requiredIf(! $existing), 'nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255', $existing ? Rule::unique('users', 'email')->ignore($existing->id) : Rule::unique('users', 'email')],
            'password' => [Rule::requiredIf(! $existing), 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $existing ?? User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $company->users()->syncWithoutDetaching([$user->id => ['role' => $validated['role'], 'is_owner' => $validated['role'] === Company::ROLE_OWNER]]);

        return back()->with('status', "{$user->name} — {$company->name} এর এডমিন করা হয়েছে");
    }

    public function destroyAdmin(Company $company, User $user): RedirectResponse
    {
        $company->users()->updateExistingPivot($user->id, ['role' => Company::ROLE_MEMBER, 'is_owner' => false]);

        return back()->with('status', "{$user->name} আর এই কোম্পানির এডমিন নন");
    }
}
