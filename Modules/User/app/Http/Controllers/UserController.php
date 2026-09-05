<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Shop\Support\PlanLimits;
use Modules\User\DataTables\UsersDataTable;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UsersDataTable $dataTable): mixed
    {
        $shopId = auth()->user()->shop_id;
        $shop = auth()->user()->shop;

        $totalUsers = User::where('shop_id', $shopId)->count();
        $adminUsers = User::where('shop_id', $shopId)->whereHas('roles', fn ($q) => $q->where('name', 'Admin'))->count();
        $staffUsers = max(0, $totalUsers - $adminUsers);
        $verifiedUsers = User::where('shop_id', $shopId)->whereNotNull('email_verified_at')->count();

        // Plan capacity calculation
        $planName = 'ডিফল্ট (Default)';
        $userLimitText = 'সীমাহীন (Unlimited)';
        $remainingSlotsText = 'সকল অ্যাকাউন্টের অ্যাক্সেস সক্রিয়';

        if ($shop && $shop->subscribed()) {
            $subscription = $shop->subscription();
            $plan = $subscription?->plan;
            $planName = $plan?->name ?? 'স্ট্যান্ডার্ড (Standard)';

            $maxUsers = $plan?->max_users;
            if ($maxUsers !== null && (int) $maxUsers > 0) {
                $remaining = max(0, (int) $maxUsers - $totalUsers);
                $userLimitText = "{$totalUsers} / {$maxUsers} জন";
                $remainingSlotsText = "অবশিষ্ট খালি স্লট: {$remaining} টি ({$planName})";
            } else {
                $userLimitText = 'সীমাহীন (Unlimited)';
                $remainingSlotsText = "প্যাকেজ: {$planName}";
            }
        } else {
            $userLimitText = "{$totalUsers} জন সক্রিয়";
            $remainingSlotsText = "প্যাকেজ: {$planName}";
        }

        $metrics = [
            'totalUsers' => $totalUsers,
            'adminUsers' => $adminUsers,
            'staffUsers' => $staffUsers,
            'verifiedUsers' => $verifiedUsers,
            'userLimitText' => $userLimitText,
            'remainingSlotsText' => $remainingSlotsText,
        ];

        $roles = $this->assignableRoles();

        return $dataTable->render('user::index', compact('metrics', 'roles'));
    }

    public function create(): View
    {
        return view('user::create', ['user' => new User, 'roles' => $this->assignableRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse|JsonResponse
    {
        $shopId = auth()->user()->shop_id;
        $currentCount = User::where('shop_id', $shopId)->count();

        if ($message = PlanLimits::check($shopId, 'max_users', $currentCount)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return redirect()->route('users.index')->with('status', $message);
        }

        $role = $this->resolveRole($request->validated('role'));

        $user = User::create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
        ]);

        $user->assignRole($role);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ইউজার সফলভাবে তৈরি করা হয়েছে',
                'user' => $user->load('roles'),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'ইউজার সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Request $request, User $user): View|JsonResponse
    {
        $this->ensureSameShop($user);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->name ?? '',
                ],
                'roles' => $this->assignableRoles()->map(fn ($r) => ['id' => $r->id, 'name' => $r->name]),
                'update_url' => route('users.update', $user),
            ]);
        }

        return view('user::edit', ['user' => $user, 'roles' => $this->assignableRoles()]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse|JsonResponse
    {
        $this->ensureSameShop($user);

        $role = $this->resolveRole($request->validated('role'));

        $user->name = $request->validated('name');
        $user->email = $request->validated('email');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->validated('password'));
        }

        $user->save();
        $user->syncRoles([$role]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ইউজারের তথ্য সফলভাবে হালনাগাদ করা হয়েছে',
                'user' => $user->load('roles'),
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'ইউজারের তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->ensureSameShop($user);

        if ($user->id === auth()->id()) {
            $msg = 'নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                ], 422);
            }

            return redirect()
                ->route('users.index')
                ->with('status', $msg);
        }

        $user->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ইউজার সফলভাবে মুছে ফেলা হয়েছে',
            ]);
        }

        return redirect()
            ->route('users.index')
            ->with('status', 'ইউজার মুছে ফেলা হয়েছে');
    }

    /**
     * Roles this shop admin is allowed to hand out: the global "Admin" role
     * plus any custom roles created for this shop.
     */
    private function assignableRoles(): Collection
    {
        $shopId = auth()->user()->shop_id;

        return Role::where(function ($query) use ($shopId) {
            $query->where('shop_id', $shopId)->orWhereNull('shop_id');
        })
            ->where('name', '!=', 'Super Admin')
            ->orderBy('name')
            ->get();
    }

    private function resolveRole(string $roleName): Role
    {
        $role = $this->assignableRoles()->firstWhere('name', $roleName);

        if (! $role) {
            throw ValidationException::withMessages(['role' => 'অবৈধ রোল নির্বাচন করা হয়েছে']);
        }

        return $role;
    }

    private function ensureSameShop(User $user): void
    {
        abort_unless($user->shop_id === auth()->user()->shop_id, 404);
    }
}
