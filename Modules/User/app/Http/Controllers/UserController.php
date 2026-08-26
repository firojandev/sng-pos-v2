<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Shop\Support\PlanLimits;
use Modules\User\Http\Requests\StoreUserRequest;
use Modules\User\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::where('shop_id', auth()->user()->shop_id)->with('roles')->latest()->paginate(10);

        return view('user::index', compact('users'));
    }

    public function create(): View
    {
        return view('user::create', ['user' => new User, 'roles' => $this->assignableRoles()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $shopId = auth()->user()->shop_id;
        $currentCount = User::where('shop_id', $shopId)->count();

        if ($message = PlanLimits::check($shopId, 'max_users', $currentCount)) {
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

        return redirect()
            ->route('users.index')
            ->with('status', 'ইউজার সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(User $user): View
    {
        $this->ensureSameShop($user);

        return view('user::edit', ['user' => $user, 'roles' => $this->assignableRoles()]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
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

        return redirect()
            ->route('users.index')
            ->with('status', 'ইউজারের তথ্য হালনাগাদ করা হয়েছে');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureSameShop($user);

        if ($user->id === auth()->id()) {
            return redirect()
                ->route('users.index')
                ->with('status', 'নিজের অ্যাকাউন্ট মুছে ফেলা যাবে না');
        }

        $user->delete();

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
