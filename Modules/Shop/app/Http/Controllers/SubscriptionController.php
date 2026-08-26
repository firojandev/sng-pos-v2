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
        $subscription = $shop?->subscription()->with('plan', 'payments')->first();

        $usage = [
            'max_users' => User::where('shop_id', $shop?->id)->count(),
            'max_branches' => Branch::count(),
            'max_warehouses' => Warehouse::count(),
            'max_products' => Product::count(),
        ];

        return view('shop::subscription.show', compact('shop', 'subscription', 'usage'));
    }
}
