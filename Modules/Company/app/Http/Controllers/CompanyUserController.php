<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Company\Models\Company;
use Modules\Core\Support\BanglaNumber;

/**
 * Company users: company admins (everything in the company workspace) and
 * company employees (what their company role grants). Neither uses the POS.
 */
class CompanyUserController extends Controller
{
    public function index(): View
    {
        $company = CompanySettingsController::manageableCompany();

        return view('company::users.index', [
            'company' => $company,
            'users' => $company->companyUsers()->orderByDesc('company_user.is_owner')->orderBy('name')->get(),
            'roles' => $company->roles()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        $validated = $this->validated($request, $company);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'username' => $validated['username'] ?? null,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $company->users()->attach($user->id, $this->membership($validated));

        return back()->with('status', "{$user->name} — কোম্পানি ইউজার তৈরি হয়েছে");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        $this->ensureMember($company, $user);

        if ((int) $company->owner()?->id === (int) $user->id) {
            return back()->withErrors(['user' => 'মালিকের ভূমিকা পরিবর্তন করা যায় না (The owner\'s role cannot be changed)।']);
        }

        $validated = $this->validated($request, $company, $user);

        $user->fill(['name' => $validated['name'], 'phone' => $validated['phone'], 'email' => $validated['email'] ?? null, 'username' => $validated['username'] ?? null]);
        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        $company->users()->updateExistingPivot($user->id, $this->membership($validated));

        return back()->with('status', "{$user->name} — হালনাগাদ হয়েছে");
    }

    public function destroy(User $user): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        $this->ensureMember($company, $user);

        if ((int) $company->owner()?->id === (int) $user->id || $user->id === auth()->id()) {
            return back()->withErrors(['user' => 'মালিক বা নিজেকে সরানো যায় না (The owner, or yourself, cannot be removed)।']);
        }

        $company->users()->detach($user->id);

        return back()->with('status', "{$user->name} কে কোম্পানি থেকে সরানো হয়েছে");
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, Company $company, ?User $user = null): array
    {
        if ($request->filled('phone')) {
            $request->merge(['phone' => BanglaNumber::toEn(trim((string) $request->input('phone')))]);
        }

        return $request->validate([
            'role' => ['required', Rule::in([Company::ROLE_ADMIN, Company::ROLE_EMPLOYEE])],
            'company_role_id' => ['nullable', 'integer', Rule::exists('company_roles', 'id')->where('company_id', $company->id)],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * An admin has every company permission; an employee has their role's.
     *
     * @param  array<string, mixed>  $validated
     * @return array{role: string, is_owner: bool, company_role_id: ?int}
     */
    private function membership(array $validated): array
    {
        $isAdmin = $validated['role'] === Company::ROLE_ADMIN;

        return [
            'role' => $validated['role'],
            'is_owner' => false,
            'company_role_id' => $isAdmin ? null : ($validated['company_role_id'] ?? null),
        ];
    }

    private function ensureMember(Company $company, User $user): void
    {
        abort_unless($company->companyUsers()->where('users.id', $user->id)->exists(), 404);
    }
}
