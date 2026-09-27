<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Company\Http\Requests\UpdateCompanyRequest;
use Modules\Company\Models\Company;
use Modules\Core\Support\BanglaNumber;
use Modules\Product\Models\Category;

/**
 * "My Company": the company owner's (or a company admin's) page for the
 * company's details, its shops and their admins.
 */
class CompanySettingsController extends Controller
{
    public function edit(): View
    {
        $company = static::manageableCompany();
        $company->load(['shops' => fn ($shops) => $shops->orderBy('id')->with(['users' => fn ($users) => $users->orderBy('name')])]);

        return view('company::settings', [
            'company' => $company,
            'owner' => $company->owner(),
            'members' => static::shopUsers($company),
            'companyUsers' => $company->companyUsers()->orderByDesc('company_user.is_owner')->orderBy('name')->get(),
            'categories' => Category::withoutGlobalScopes()
                ->where(fn ($query) => $query->whereNull('company_id')->orWhere('company_id', $company->id))
                ->where('type', 'product')
                ->whereNull('parent_id')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $company = static::manageableCompany();

        $company->update($request->safe()->except('status'));

        return redirect()->route('company-settings.edit')->with('status', 'কোম্পানির তথ্য হালনাগাদ করা হয়েছে');
    }

    /**
     * Add a company-level user: a company admin, or a company employee
     * (both log in to the company workspace; neither uses the POS).
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $company = static::manageableCompany();

        if ($request->filled('phone')) {
            $request->merge(['phone' => BanglaNumber::toEn(trim((string) $request->input('phone')))]);
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in([Company::ROLE_ADMIN, Company::ROLE_EMPLOYEE])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);
        $company->users()->attach($user->id, ['role' => $validated['role'], 'is_owner' => false]);

        return back()->with('status', "{$user->name} — কোম্পানি ইউজার তৈরি হয়েছে");
    }

    public function destroyUser(User $user): RedirectResponse
    {
        $company = static::manageableCompany();

        if ($company->owner()?->id === $user->id || $user->id === auth()->id()) {
            return back()->withErrors(['user' => 'মালিক বা নিজেকে সরানো যায় না (The owner, or yourself, cannot be removed)।']);
        }

        $company->users()->detach($user->id);

        return back()->with('status', "{$user->name} কে কোম্পানি থেকে সরানো হয়েছে");
    }

    /**
     * Login users of the company's shops (shop admins and shop users).
     *
     * @return Collection<int, User>
     */
    public static function shopUsers(Company $company): Collection
    {
        $shopIds = $company->shops()->pluck('id');

        return User::query()
            ->where(fn ($query) => $query->whereIn('shop_id', $shopIds)->orWhereHas('shops', fn ($shops) => $shops->whereIn('shops.id', $shopIds)))
            ->orderBy('name')
            ->get();
    }

    /**
     * The company the user runs (as its owner or a company admin).
     */
    public static function manageableCompany(): Company
    {
        /** @var User $user */
        $user = auth()->user();
        $company = $user->shop?->company?->isBusiness() ? $user->shop->company : $user->companyLevelCompany();

        abort_unless($company, 404);
        abort_unless($user->isCompanyAdmin($company), 403, 'শুধু কোম্পানির মালিক/এডমিন এটি পরিচালনা করতে পারেন (Only the company owner or an admin can manage the company)।');

        return $company;
    }
}
