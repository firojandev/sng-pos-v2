<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Product\Models\Product;
use Modules\Shop\Http\Requests\UpdateShopSettingsRequest;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Warehouse;

class ShopSettingsController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        // If user has no shop and is super admin, redirect to all shops list
        if (! $user->shop && $user->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        $shop = $user->shop;
        if (! $shop) {
            abort(403, 'কোনো দোকান নির্বাচন বা বরাদ্দ করা নেই (No shop assigned)।');
        }

        $subscription = $shop->subscription();
        if ($subscription) {
            $subscription->loadMissing(['plan']);
        }

        $stats = [
            'users_count' => User::where('shop_id', $shop->id)->count(),
            'branches_count' => Branch::count(),
            'warehouses_count' => Warehouse::count(),
            'products_count' => Product::count(),
        ];

        return view('shop::settings.index', compact('shop', 'subscription', 'stats'));
    }

    public function update(UpdateShopSettingsRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $shop = $user->shop;

        if (! $shop && $user->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        if (! $shop) {
            abort(403, 'কোনো দোকান নির্বাচন বা বরাদ্দ করা নেই (No shop assigned)।');
        }

        $shop->name = $request->validated('name');
        $shop->phone = $request->validated('phone');
        $shop->email = $request->validated('email');
        $shop->address = $request->validated('address');
        $shop->currency_symbol = $request->validated('currency_symbol') ?: '৳';
        $shop->invoice_footer = $request->validated('invoice_footer');

        if ($request->boolean('remove_logo')) {
            if ($shop->logo && Storage::disk('public')->exists($shop->logo)) {
                Storage::disk('public')->delete($shop->logo);
            }
            $shop->logo = null;
        } elseif ($request->hasFile('logo')) {
            if ($shop->logo && Storage::disk('public')->exists($shop->logo)) {
                Storage::disk('public')->delete($shop->logo);
            }
            $shop->logo = $request->file('logo')->store('shops/logos', 'public');
        }

        $shop->save();

        return redirect()
            ->route('settings.index')
            ->with('status', 'দোকানের সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে (Shop settings saved successfully)');
    }
}
