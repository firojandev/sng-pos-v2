<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Modules\Shop\Models\Shop;

class ShopSelectionController extends Controller
{
    /**
     * Show the shop selection page.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            $shops = Shop::where('status', 'active')->latest()->get();
        } else {
            $shops = $user->activeShops()->get();

            if ($shops->isEmpty() && $user->shop_id && $user->shop && $user->shop->status === 'active') {
                $user->shops()->syncWithoutDetaching([
                    $user->shop_id => [
                        'role' => $user->roles->first()?->name ?? 'Admin',
                        'is_owner' => true,
                    ],
                ]);
                $shops = collect([$user->shop]);
            }
        }

        $currentShopId = $user->shop_id ?? session('current_shop_id');

        // Group by company only when some company runs several shops; a list of
        // single-shop companies stays a plain list of shops.
        $shops->loadMissing(['company', 'activeSubscription.plan']);
        $shopGroups = $shops->groupBy('company_id');
        $showCompanies = $shopGroups->contains(fn ($group) => $group->count() > 1);

        return view('shop::select', [
            'shops' => $shops,
            'shopGroups' => $showCompanies ? $shopGroups : collect([$shops]),
            'showCompanies' => $showCompanies,
            'currentShopId' => $currentShopId,
            'user' => $user,
        ]);
    }

    /**
     * Select / switch to a specific shop.
     */
    public function select(Request $request, Shop $shop): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->belongsToShop($shop)) {
            abort(403, 'এই দোকানে প্রবেশের অনুমতি আপনার নেই।');
        }

        // Company users work at company level; the POS is for shop logins.
        if (! $user->isSuperAdmin() && $user->isCompanyLevelUser()) {
            abort(403, 'কোম্পানি ইউজার দোকানের POS ব্যবহার করেন না; দোকান এডমিন হিসেবে লগইন করুন (Company users do not use a shop POS; log in as a shop admin)।');
        }

        if ($shop->status !== 'active') {
            return back()->with('error', 'এই দোকানটি বর্তমানে নিষ্ক্রিয় রয়েছে।');
        }

        $user->switchShop($shop);

        $shopName = $shop->name;

        return redirect()->intended(route('dashboard'))->with('status', "স্বাগতম! আপনি \"{$shopName}\"-এ সফলভাবে প্রবেশ করেছেন।");
    }
}
