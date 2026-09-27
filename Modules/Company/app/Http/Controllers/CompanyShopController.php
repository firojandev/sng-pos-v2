<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Shop\Models\Shop;
use Modules\Shop\Services\ShopProvisioner;

/**
 * A company owner (or company admin) opens another shop under the company.
 * It shares the company's subscription, customers, catalogue and accounts.
 */
class CompanyShopController extends Controller
{
    public function store(Request $request, ShopProvisioner $provisioner): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', 'unique:shops,slug'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', Rule::exists('categories', 'id')->where('type', 'product')->where(fn ($query) => $query->whereNull('company_id')->orWhere('company_id', $company->id))],
            'opening_cash' => ['nullable', 'numeric', 'min:0'],
        ]);

        $shop = DB::transaction(function () use ($company, $validated, $provisioner) {
            $shop = Shop::create([
                'company_id' => $company->id,
                'name' => $validated['name'],
                'slug' => ($validated['slug'] ?? null) ?: $this->uniqueSlug($validated['name']),
                'store_code' => Shop::generateNextStoreCode(),
                'phone' => $validated['phone'] ?? $company->phone,
                'address' => $validated['address'] ?? $company->address,
                'status' => 'active',
            ]);

            $provisioner->provision($shop, (float) ($validated['opening_cash'] ?? 0));
            $shop->categories()->sync($validated['category_ids'] ?? []);

            // The company owner/admin who opens it can run it and switch to it.
            $provisioner->assignAdmin($shop, auth()->user(), isOwner: $company->isAdministeredBy(auth()->user()));

            return $shop;
        });

        return redirect()->route('company-settings.edit')->with('status', "\"{$shop->name}\" দোকান খোলা হয়েছে");
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'shop';
        $slug = $base;
        $suffix = 2;

        while (Shop::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
