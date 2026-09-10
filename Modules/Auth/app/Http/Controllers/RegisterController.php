<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Modules\Auth\Http\Requests\RegisterShopOwnerRequest;
use Modules\Core\Models\Setting;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\AccountTransaction;
use Modules\Shop\Models\Branch;
use Modules\Shop\Models\Plan;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Show the multi-step shop registration page.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (! Setting::isRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('error', 'অনলাইন দোকান রেজিস্ট্রেশন বর্তমানে বন্ধ রয়েছে। প্রয়োজনে সাপোর্টে যোগাযোগ করুন।');
        }

        $freePlan = Plan::where('is_free', true)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->first() ?? Plan::where('slug', 'free')->first();

        return view('auth::register', [
            'freePlan' => $freePlan,
            'nextStoreCode' => Shop::generateNextStoreCode(),
        ]);
    }

    /**
     * Real-time availability check for slug, phone, email, and username.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        if (! Setting::isRegistrationEnabled()) {
            return response()->json([
                'error' => 'অনলাইন রেজিস্ট্রেশন বর্তমানে বন্ধ রয়েছে।',
                'registration_enabled' => false,
            ], 403);
        }

        $slug = trim((string) $request->query('slug', ''));
        $phone = trim((string) $request->query('phone', ''));
        $email = trim((string) $request->query('email', ''));
        $username = trim((string) $request->query('username', ''));

        $slugAvailable = true;
        if ($slug !== '') {
            $slugAvailable = ! Shop::where('slug', $slug)->exists();
        }

        $phoneAvailable = true;
        if ($phone !== '') {
            $phoneAvailable = ! User::where('phone', $phone)->exists();
        }

        $emailAvailable = true;
        if ($email !== '') {
            $emailAvailable = ! User::where('email', $email)->exists();
        }

        $usernameAvailable = true;
        if ($username !== '') {
            $usernameAvailable = ! User::where('username', $username)->exists();
        }

        return response()->json([
            'slug' => $slug,
            'slug_available' => $slugAvailable,
            'phone' => $phone,
            'phone_available' => $phoneAvailable,
            'email' => $email,
            'email_available' => $emailAvailable,
            'username' => $username,
            'username_available' => $usernameAvailable,
        ]);
    }

    /**
     * Handle the complete multi-step shop owner registration and onboarding.
     */
    public function register(RegisterShopOwnerRequest $request): RedirectResponse
    {
        if (! Setting::isRegistrationEnabled()) {
            return redirect()->route('login')
                ->with('error', 'অনলাইন দোকান রেজিস্ট্রেশন বর্তমানে বন্ধ রয়েছে।');
        }

        $validated = $request->validated();

        $user = DB::transaction(function () use ($validated) {
            // 1. Create the Owner User account
            $owner = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?: null,
                'username' => $validated['username'] ?: null,
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Generate Store Code & Create Shop
            $storeCode = Shop::generateNextStoreCode();
            $shop = Shop::create([
                'name' => $validated['shop_name'],
                'slug' => $validated['shop_slug'],
                'store_code' => $storeCode,
                'phone' => $validated['shop_phone'] ?: $validated['phone'],
                'address' => $validated['shop_address'] ?: null,
                'currency_symbol' => $validated['currency_symbol'] ?: '৳',
                'status' => 'active',
            ]);

            // 3. Link User to Shop
            $owner->shop_id = $shop->id;
            $owner->save();

            // Set Spatie Team Scope for the Shop & Assign Admin Role
            setPermissionsTeamId($shop->id);
            $adminRole = Role::firstOrCreate([
                'shop_id' => $shop->id,
                'name' => 'Admin',
                'guard_name' => 'web',
            ]);
            $owner->assignRole($adminRole);
            setPermissionsTeamId(null);

            // Sync Pivot Table
            $shop->users()->syncWithoutDetaching([
                $owner->id => [
                    'role' => 'Admin',
                    'is_owner' => true,
                ],
            ]);

            // 4. Create Default Branch
            $branchName = ! empty($validated['branch_name']) ? trim($validated['branch_name']) : 'প্রধান শাখা';
            $branch = Branch::create([
                'shop_id' => $shop->id,
                'name' => $branchName,
                'phone' => $shop->phone,
                'address' => $shop->address,
                'status' => 'active',
            ]);

            // 5. Create Default Warehouse
            $warehouseName = ! empty($validated['warehouse_name']) ? trim($validated['warehouse_name']) : 'প্রধান গুদাম';
            Warehouse::create([
                'shop_id' => $shop->id,
                'branch_id' => $branch->id,
                'name' => $warehouseName,
                'phone' => $shop->phone,
                'address' => $shop->address,
                'status' => 'active',
                'is_default' => true,
            ]);

            // 6. Create Primary Cash Account
            $openingCash = isset($validated['opening_cash_balance']) ? max(0, (float) $validated['opening_cash_balance']) : 0.00;
            $cashAccount = Account::withoutGlobalScopes()->firstOrCreate(
                [
                    'shop_id' => $shop->id,
                    'type' => 'cash',
                ],
                [
                    'name' => 'নগদ টাকা (Cash)',
                    'opening_balance' => $openingCash,
                    'current_balance' => $openingCash,
                    'is_default' => true,
                    'status' => 'active',
                    'note' => 'প্রধান ক্যাশ অ্যাকাউন্ট (সিস্টেম নির্ধারিত)',
                ]
            );

            if ($openingCash > 0 && $cashAccount->wasRecentlyCreated) {
                AccountTransaction::create([
                    'shop_id' => $shop->id,
                    'account_id' => $cashAccount->id,
                    'type' => 'in',
                    'amount' => $openingCash,
                    'balance_after' => $openingCash,
                    'source' => 'opening_balance',
                    'note' => 'প্রারম্ভিক ক্যাশ ব্যালেন্স (Opening Balance)',
                    'occurred_at' => now(),
                    'created_by' => $owner->id,
                ]);
            }

            // 7. Auto Assign Free Package
            $freePlan = Plan::where('is_free', true)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->first() ?? Plan::where('slug', 'free')->first();

            if ($freePlan) {
                $shop->subscriptions()->create([
                    'subscribable_type' => Shop::class,
                    'subscribable_id' => $shop->id,
                    'plan_id' => $freePlan->id,
                    'status' => 'active',
                    'trial_ends_at' => null,
                    'starts_at' => now(),
                    'ends_at' => null, // Lifetime free package
                    'current_period_start' => now(),
                    'current_period_end' => null,
                ]);
                $shop->clearSubscriptionCache();
            }

            return $owner;
        });

        // 8. Auto login owner and set current shop in session
        Auth::login($user);
        $request->session()->regenerate();
        session(['current_shop_id' => $user->shop_id]);

        return redirect()->route('dashboard')->with('status', 'অভিনন্দন! আপনার দোকান ও ফ্রি প্যাকেজ সফলভাবে চালু করা হয়েছে।');
    }
}
