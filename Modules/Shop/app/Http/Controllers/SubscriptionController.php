<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Modules\Product\Models\Product;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Warehouse;

class SubscriptionController extends Controller
{
    public function show(): View
    {
        $shop = auth()->user()->shop;
        $subscription = $shop?->subscription();
        if ($subscription) {
            $subscription->loadMissing(['plan.features', 'payments']);
        }

        $subscriptionInfo = $shop?->subscriptionInfo();
        $features = $shop ? $shop->allFeatures() : collect();

        $usage = [
            'users' => User::where('shop_id', $shop?->id)->count(),
            'branches' => Branch::count(),
            'warehouses' => Warehouse::count(),
            'products' => Product::count(),
        ];

        return view('shop::subscription.show', compact('shop', 'subscription', 'subscriptionInfo', 'features', 'usage'));
    }
}
