<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Company\Models\Company;
use Modules\Shop\Models\Shop;
use Modules\Shop\Services\ShopProvisioner;

/**
 * The Default Company: every standalone shop (shop owners without a
 * company). Its admins see them all and can open one to help its owner.
 */
class DefaultCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $company = $this->defaultCompany();

        // Coming back from a standalone shop: leave it for the company level.
        $user = $request->user();
        if ($user->shop_id && ! $user->isSuperAdmin()) {
            $user->forceFill(['shop_id' => null])->save();
            $request->session()->forget('current_shop_id');
        }

        $shops = $company->standaloneShops()
            ->with(['company.activeSubscription.plan', 'users' => fn ($users) => $users->wherePivot('is_owner', true)])
            ->when($request->filled('q'), fn ($query) => $query->where(fn ($search) => $search
                ->where('shops.name', 'like', '%'.$request->input('q').'%')
                ->orWhere('shops.phone', 'like', '%'.$request->input('q').'%')
                ->orWhere('shops.store_code', 'like', '%'.$request->input('q').'%')))
            ->when($request->filled('status'), fn ($query) => $query->where('shops.status', $request->input('status')))
            ->latest('shops.id')
            ->paginate(25)
            ->withQueryString();

        return view('company::default-company', [
            'company' => $company,
            'shops' => $shops,
            'totals' => [
                'shops' => $company->standaloneShops()->count(),
                'active' => $company->standaloneShops()->where('shops.status', 'active')->count(),
            ],
        ]);
    }

    /**
     * Open a standalone shop: the admin joins it as a shop admin and
     * switches to it.
     */
    public function open(Shop $shop, ShopProvisioner $provisioner): RedirectResponse
    {
        $company = $this->defaultCompany();
        $user = auth()->user();

        abort_unless($shop->company?->isStandalone() && (int) $shop->company->parent_id === (int) $company->id, 404);

        if ($shop->status !== 'active') {
            return back()->withErrors(['shop' => 'এই দোকানটি নিষ্ক্রিয় (This shop is inactive)।']);
        }

        if (! $user->isSuperAdmin()) {
            $provisioner->assignAdmin($shop, $user);
        }

        $user->switchShop($shop);

        return redirect()->route('dashboard')->with('status', "\"{$shop->name}\" দোকানে প্রবেশ করেছেন");
    }

    private function defaultCompany(): Company
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->isDefaultCompanyAdmin(), 403);

        return Company::defaultCompany();
    }
}
