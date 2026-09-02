<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\Shop\DataTables\ShopsDataTable;
use Modules\Shop\Http\Requests\StoreShopAdminRequest;
use Modules\Shop\Http\Requests\StoreShopRequest;
use Modules\Shop\Http\Requests\UpdateShopRequest;
use Modules\Shop\Http\Requests\UpdateShopSubscriptionRequest;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Revoltify\Subscriptionify\Enums\SubscriptionStatus;
use Spatie\Permission\Models\Role;

class ShopController extends Controller
{
    public function index(ShopsDataTable $dataTable)
    {
        return $dataTable->render('shop::index');
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $slug = trim((string) $request->query('slug', ''));
        $storeCode = trim((string) $request->query('store_code', ''));
        $ignoreId = $request->query('ignore_id');

        $slugAvailable = true;
        if ($slug !== '') {
            $slugQuery = Shop::where('slug', $slug);
            if ($ignoreId) {
                $slugQuery->where('id', '!=', $ignoreId);
            }
            $slugAvailable = ! $slugQuery->exists();
        }

        $storeCodeAvailable = true;
        if ($storeCode !== '') {
            $codeQuery = Shop::where('store_code', $storeCode);
            if ($ignoreId) {
                $codeQuery->where('id', '!=', $ignoreId);
            }
            $storeCodeAvailable = ! $codeQuery->exists();
        }

        return response()->json([
            'slug' => $slug,
            'slug_available' => $slugAvailable,
            'store_code' => $storeCode,
            'store_code_available' => $storeCodeAvailable,
        ]);
    }

    public function show(Shop $shop): JsonResponse|RedirectResponse
    {
        $shop->load(['admins.roles', 'activeSubscription.plan']);

        if (request()->wantsJson() || request()->ajax()) {
            $subscription = $shop->activeSubscription;
            $plan = $subscription?->plan;

            return response()->json([
                'id' => $shop->id,
                'name' => $shop->name,
                'slug' => $shop->slug,
                'store_code' => $shop->store_code,
                'phone' => $shop->phone,
                'address' => $shop->address,
                'status' => $shop->status,
                'enabled_features' => $shop->enabled_features ?? [],
                'created_at' => $shop->created_at?->format('d M, Y (h:i A)'),
                'edit_url' => route('shops.edit', $shop),
                'subscription' => $subscription ? [
                    'plan_name' => $plan?->name ?? 'Custom Plan',
                    'price' => $plan ? '৳'.number_format($plan->price, 0) : null,
                    'billing_cycle' => $plan?->billing_cycle === 'yearly' ? 'Yearly' : 'Monthly',
                    'status' => $subscription->status instanceof SubscriptionStatus
                        ? $subscription->status->value
                        : (string) $subscription->status,
                    'status_label' => $subscription->statusLabel()['bn'] ?? (string) $subscription->status,
                    'current_period_end' => $subscription->ends_at?->format('d M, Y'),
                    'trial_ends_at' => $subscription->trial_ends_at?->format('d M, Y'),
                ] : null,
                'admins' => $shop->admins->map(fn ($admin) => [
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'roles' => $admin->roles->pluck('name')->toArray(),
                ]),
            ]);
        }

        return redirect()->route('shops.edit', $shop);
    }

    public function create(): View
    {
        return view('shop::create', [
            'shop' => new Shop,
            'nextStoreCode' => Shop::generateNextStoreCode(),
            'roles' => Role::whereNull('shop_id')->where('name', '!=', 'Super Admin')->orderBy('name')->get(),
            'features' => Features::all(),
            'plans' => Plan::where('is_active', true)->orWhere('status', 'active')->orderBy('sort_order')->orderBy('price')->get(),
            'existingOwners' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'Super Admin'))
                ->with(['shop', 'roles'])
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreShopRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $storeCode = $request->validated('store_code') ?: Shop::generateNextStoreCode();

            $shop = Shop::create([
                'name' => $request->validated('name'),
                'slug' => $request->validated('slug'),
                'store_code' => $storeCode,
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'status' => $request->validated('status'),
                'enabled_features' => $request->validated('features', []),
            ]);

            $ownerType = $request->input('owner_type', 'new');

            if ($ownerType === 'existing' && $request->filled('existing_user_id')) {
                $admin = User::findOrFail($request->validated('existing_user_id'));
                if (! $admin->shop_id) {
                    $admin->shop_id = $shop->id;
                    $admin->save();
                }
            } else {
                $adminEmail = $request->validated('admin_email');
                $admin = User::where('email', $adminEmail)->first();

                if (! $admin) {
                    $admin = User::create([
                        'shop_id' => $shop->id,
                        'name' => $request->validated('admin_name'),
                        'email' => $adminEmail,
                        'password' => Hash::make($request->validated('admin_password')),
                    ]);
                } else {
                    if (! $admin->shop_id) {
                        $admin->shop_id = $shop->id;
                        $admin->save();
                    }
                }
            }

            $roleName = $request->validated('admin_role');
            $admin->assignRole($roleName);

            $shop->users()->syncWithoutDetaching([
                $admin->id => [
                    'role' => $roleName,
                    'is_owner' => true,
                ],
            ]);

            if ($request->filled('plan_id')) {
                $plan = Plan::find($request->validated('plan_id'));
                if ($plan) {
                    $billingCycle = $plan->billing_cycle ?? ($plan->billing_interval?->value ?? 'month');
                    $startDate = $request->validated('current_period_start')
                        ? Carbon::parse($request->validated('current_period_start'))
                        : now();

                    if ($request->filled('current_period_end')) {
                        $endDate = Carbon::parse($request->validated('current_period_end'));
                    } elseif (in_array(strtolower((string) $billingCycle), ['yearly', 'year', 'annual'])) {
                        $endDate = $startDate->copy()->addDays(365);
                    } else {
                        $endDate = $startDate->copy()->addDays(30);
                    }

                    $trialEndsAt = $request->filled('trial_ends_at')
                        ? Carbon::parse($request->validated('trial_ends_at'))
                        : null;

                    $shop->subscriptions()->create([
                        'subscribable_type' => Shop::class,
                        'subscribable_id' => $shop->id,
                        'plan_id' => $plan->id,
                        'status' => $request->validated('subscription_status', 'active'),
                        'trial_ends_at' => $trialEndsAt,
                        'starts_at' => $startDate,
                        'ends_at' => $endDate,
                        'current_period_start' => $startDate,
                        'current_period_end' => $endDate,
                    ]);
                    $shop->clearSubscriptionCache();
                }
            }
        });

        return redirect()->route('shops.index')->with('status', 'দোকান ও এডমিন সফলভাবে তৈরি করা হয়েছে');
    }

    public function edit(Shop $shop): View
    {
        return view('shop::edit', [
            'shop' => $shop,
            'roles' => Role::whereNull('shop_id')->where('name', '!=', 'Super Admin')->orderBy('name')->get(),
            'features' => Features::all(),
            'admins' => $shop->admins()->with('roles')->get(),
            'subscription' => $shop->subscription(),
            'plans' => Plan::where('is_active', true)->orWhere('status', 'active')->orderBy('price')->get(),
        ]);
    }

    public function updateSubscription(UpdateShopSubscriptionRequest $request, Shop $shop): RedirectResponse
    {
        $plan = Plan::find($request->validated('plan_id'));
        if ($plan) {
            $billingCycle = $plan->billing_cycle ?? ($plan->billing_interval?->value ?? 'month');
            $startDate = $request->validated('current_period_start')
                ? Carbon::parse($request->validated('current_period_start'))
                : now();

            if ($request->filled('current_period_end')) {
                $endDate = Carbon::parse($request->validated('current_period_end'));
            } elseif (in_array(strtolower((string) $billingCycle), ['yearly', 'year', 'annual'])) {
                $endDate = $startDate->copy()->addDays(365);
            } else {
                $endDate = $startDate->copy()->addDays(30);
            }

            $trialEndsAt = $request->filled('trial_ends_at')
                ? Carbon::parse($request->validated('trial_ends_at'))
                : null;

            $shop->subscriptions()->updateOrCreate(
                [
                    'subscribable_type' => Shop::class,
                    'subscribable_id' => $shop->id,
                ],
                [
                    'plan_id' => $plan->id,
                    'status' => $request->validated('status', 'active'),
                    'trial_ends_at' => $trialEndsAt,
                    'starts_at' => $startDate,
                    'ends_at' => $endDate,
                    'current_period_start' => $startDate,
                    'current_period_end' => $endDate,
                ]
            );
            $shop->clearSubscriptionCache();
        }

        return redirect()->route('shops.edit', $shop)->with('status', 'সাবস্ক্রিপশন হালনাগাদ করা হয়েছে');
    }

    public function update(UpdateShopRequest $request, Shop $shop): RedirectResponse
    {
        $shop->update([
            'name' => $request->validated('name'),
            'slug' => $request->validated('slug'),
            'store_code' => $request->validated('store_code'),
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
        $email = $request->validated('email');
        $admin = User::where('email', $email)->first();

        if (! $admin) {
            $admin = User::create([
                'shop_id' => $shop->id,
                'name' => $request->validated('name'),
                'email' => $email,
                'password' => Hash::make($request->validated('password')),
            ]);
            $admin->assignRole($request->validated('role'));
        } else {
            if (! $admin->shop_id) {
                $admin->shop_id = $shop->id;
                $admin->save();
            }
        }

        $shop->users()->syncWithoutDetaching([
            $admin->id => [
                'role' => $request->validated('role'),
                'is_owner' => false,
            ],
        ]);

        return redirect()->route('shops.edit', $shop)->with('status', 'নতুন এডমিন যোগ করা হয়েছে');
    }

    public function destroyAdmin(Shop $shop, User $admin): RedirectResponse
    {
        $belongsToShop = $shop->users()->where('users.id', $admin->id)->exists() || $admin->shop_id === $shop->id;
        if (! $belongsToShop) {
            abort(404);
        }

        $shop->users()->detach($admin->id);

        if ($admin->shop_id === $shop->id) {
            $nextShop = $admin->shops()->first();
            $admin->shop_id = $nextShop?->id;
            $admin->save();
        }

        if ($admin->shops()->count() === 0 && ! $admin->isSuperAdmin()) {
            $admin->delete();
        }

        return redirect()->route('shops.edit', $shop)->with('status', 'এডমিন মুছে ফেলা হয়েছে');
    }
}
