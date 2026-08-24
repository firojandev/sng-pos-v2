<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\Shop\Http\Requests\StoreShopAdminRequest;
use Modules\Shop\Http\Requests\StoreShopRequest;
use Modules\Shop\Http\Requests\UpdateShopRequest;
use Modules\Shop\Models\Shop;
use Spatie\Permission\Models\Role;

class ShopController extends Controller
{
    public function index(): View
    {
        $shops = Shop::withCount('admins')->latest()->paginate(10);

        return view('shop::index', compact('shops'));
    }

    public function create(): View
    {
        return view('shop::create', [
            'shop' => new Shop(),
            'roles' => Role::where('name', '!=', 'Super Admin')->orderBy('name')->get(),
            'features' => Features::all(),
        ]);
    }

    public function store(StoreShopRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $shop = Shop::create([
                'name' => $request->validated('name'),
                'slug' => $request->validated('slug'),
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'status' => $request->validated('status'),
                'enabled_features' => $request->validated('features', []),
            ]);

            $admin = User::create([
                'shop_id' => $shop->id,
                'name' => $request->validated('admin_name'),
                'email' => $request->validated('admin_email'),
                'password' => Hash::make($request->validated('admin_password')),
            ]);

            $admin->assignRole($request->validated('admin_role'));
        });

        return redirect()->route('shops.index')->with('status', 'দোকান ও এডমিন সফলভাবে তৈরি করা হয়েছে');
    }

    public function edit(Shop $shop): View
    {
        return view('shop::edit', [
            'shop' => $shop,
            'roles' => Role::where('name', '!=', 'Super Admin')->orderBy('name')->get(),
            'features' => Features::all(),
            'admins' => $shop->admins()->with('roles')->get(),
        ]);
    }

    public function update(UpdateShopRequest $request, Shop $shop): RedirectResponse
    {
        $shop->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'phone' => $request->validated('phone'),
            'address' => $request->validated('address'),
            'status' => $request->validated('status'),
            'enabled_features' => $request->validated('features', []),
        ]);

        return redirect()->route('shops.edit', $shop)->with('status', 'দোকানের তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Shop $shop): RedirectResponse
    {
        $shop->delete();

        return redirect()->route('shops.index')->with('status', 'দোকান মুছে ফেলা হয়েছে');
    }

    public function storeAdmin(StoreShopAdminRequest $request, Shop $shop): RedirectResponse
    {
        $admin = User::create([
            'shop_id' => $shop->id,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $admin->assignRole($request->validated('role'));

        return redirect()->route('shops.edit', $shop)->with('status', 'নতুন এডমিন যোগ করা হয়েছে');
    }

    public function destroyAdmin(Shop $shop, User $admin): RedirectResponse
    {
        if ($admin->shop_id !== $shop->id) {
            abort(404);
        }

        $admin->delete();

        return redirect()->route('shops.edit', $shop)->with('status', 'এডমিন মুছে ফেলা হয়েছে');
    }
}
