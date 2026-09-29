<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Company\Models\Company;
use Modules\Company\Models\CompanyRole;

/**
 * Company roles and their permissions over the company modules (HR,
 * payroll, accounting, tasks), for company employees.
 */
class CompanyRoleController extends Controller
{
    public function index(): View
    {
        $company = CompanySettingsController::manageableCompany();

        return view('company::roles.index', [
            'company' => $company,
            'roles' => $company->roles()->orderBy('name')->get()->map(fn (CompanyRole $role) => [
                'role' => $role,
                'users' => $company->companyUsers()->wherePivot('company_role_id', $role->id)->count(),
            ]),
        ]);
    }

    public function create(): View
    {
        CompanySettingsController::manageableCompany();

        return view('company::roles.form', ['role' => new CompanyRole(['permissions' => []]), 'grantable' => CompanyRole::grantable()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        $role = $company->roles()->create($this->validated($request, $company));

        return redirect()->route('company.roles.index')->with('status', "\"{$role->name}\" রোল তৈরি হয়েছে");
    }

    public function edit(CompanyRole $role): View
    {
        $this->ensureOwn($role);

        return view('company::roles.form', ['role' => $role, 'grantable' => CompanyRole::grantable()]);
    }

    public function update(Request $request, CompanyRole $role): RedirectResponse
    {
        $company = $this->ensureOwn($role);
        $role->update($this->validated($request, $company, $role));

        return redirect()->route('company.roles.index')->with('status', "\"{$role->name}\" রোল হালনাগাদ হয়েছে");
    }

    public function destroy(CompanyRole $role): RedirectResponse
    {
        $this->ensureOwn($role);
        $role->delete();

        return back()->with('status', 'রোল মুছে ফেলা হয়েছে');
    }

    /**
     * @return array{name: string, permissions: list<string>}
     */
    private function validated(Request $request, Company $company, ?CompanyRole $role = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('company_roles', 'name')->where('company_id', $company->id)->ignore($role)],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(CompanyRole::allPermissions())],
        ]);

        return ['name' => $validated['name'], 'permissions' => array_values(array_unique($validated['permissions'] ?? []))];
    }

    private function ensureOwn(CompanyRole $role): Company
    {
        $company = CompanySettingsController::manageableCompany();
        abort_unless((int) $role->company_id === (int) $company->id, 404);

        return $company;
    }
}
