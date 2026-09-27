<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\Core\Support\BanglaNumber;
use Modules\Shop\Models\Shop;
use Modules\Shop\Services\ShopProvisioner;

/**
 * The company owner (or a company admin) creates shop admins, or makes an
 * existing company user an admin, of one or more of the company's shops.
 */
class CompanyShopAdminController extends Controller
{
    public function __construct(private ShopProvisioner $provisioner) {}

    public function store(Request $request): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        $shopIds = $company->shops()->pluck('id')->all();

        if ($request->filled('phone')) {
            $request->merge(['phone' => BanglaNumber::toEn(trim((string) $request->input('phone')))]);
        }

        $isExisting = $request->input('admin_type') === 'existing';
        $validated = $request->validate([
            'admin_type' => ['required', 'in:new,existing'],
            'shop_ids' => ['required', 'array', 'min:1'],
            'shop_ids.*' => ['integer', Rule::in($shopIds)],
            'user_id' => [Rule::requiredIf($isExisting), 'nullable', 'integer', Rule::in($company->users()->pluck('users.id')->all())],
            'name' => [Rule::requiredIf(! $isExisting), 'nullable', 'string', 'max:255'],
            'phone' => [Rule::requiredIf(! $isExisting), 'nullable', 'string', 'max:30', Rule::unique('users', 'phone')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => [Rule::requiredIf(! $isExisting), 'nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $shops = Shop::whereIn('id', $validated['shop_ids'])->get();

        $admin = DB::transaction(function () use ($validated, $isExisting, $shops) {
            $admin = $isExisting
                ? User::findOrFail($validated['user_id'])
                : User::create([
                    'shop_id' => $shops->first()->id,
                    'name' => $validated['name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'username' => $validated['username'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'email_verified_at' => now(),
                ]);

            foreach ($shops as $shop) {
                $this->provisioner->assignAdmin($shop, $admin);
            }

            return $admin;
        });

        return back()->with('status', "{$admin->name} — {$shops->pluck('name')->implode(', ')} দোকানের এডমিন করা হয়েছে");
    }

    public function destroy(Shop $shop, User $user): RedirectResponse
    {
        $company = CompanySettingsController::manageableCompany();
        abort_unless((int) $shop->company_id === (int) $company->id, 404);

        if ((int) $company->owner()?->id === (int) $user->id) {
            return back()->withErrors(['admin' => 'কোম্পানির মালিককে সরানো যায় না (The company owner cannot be removed)।']);
        }

        $this->provisioner->removeAdmin($shop, $user);

        return back()->with('status', "{$user->name} — \"{$shop->name}\" থেকে সরানো হয়েছে");
    }
}
