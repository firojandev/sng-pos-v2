<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Core\Support\Features;
use Modules\User\Http\Requests\StoreRoleRequest;
use Modules\User\Http\Requests\UpdateRoleRequest;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::where('shop_id', auth()->user()->shop_id)
            ->withCount(['permissions', 'users'])
            ->orderBy('name')
            ->paginate(10);

        return view('user::roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('user::roles.create', [
            'role' => new Role,
            'features' => $this->assignableFeatures(),
            'rolePermissions' => [],
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        // Role::create() is intercepted by Spatie to reject duplicate names
        // globally (it isn't aware of our per-shop `shop_id` scoping), so we
        // go through the query builder directly; our own request validation
        // and the DB's (shop_id, name, guard_name) unique index already
        // guarantee per-shop uniqueness.
        $role = Role::query()->create([
            'shop_id' => auth()->user()->shop_id,
            'name' => $request->validated('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->validated('permissions', []));

        return redirect()->route('roles.index')->with('status', 'রোল সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Role $role): View
    {
        $this->ensureSameShop($role);

        return view('user::roles.edit', [
            'role' => $role,
            'features' => $this->assignableFeatures(),
            'rolePermissions' => $role->permissions->pluck('name')->toArray(),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->ensureSameShop($role);

        if ($role->name !== 'Admin') {
            $role->update(['name' => $request->validated('name')]);
        }
        $role->syncPermissions($request->validated('permissions', []));

        return redirect()->route('roles.index')->with('status', 'রোল হালনাগাদ করা হয়েছে');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->ensureSameShop($role);

        if ($role->name === 'Admin') {
            return redirect()->route('roles.index')->with('status', 'ডিফল্ট এডমিন রোলটি মুছে ফেলা যাবে না');
        }

        if ($role->users()->exists()) {
            return redirect()->route('roles.index')->with('status', 'এই রোলে ইউজার যুক্ত আছে, মুছে ফেলা যাবে না');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('status', 'রোল মুছে ফেলা হয়েছে');
    }

    /**
     * @return array<string, array{bn: string, en: string}>
     */
    private function assignableFeatures(): array
    {
        $shop = auth()->user()->shop;

        return collect(Features::all())
            ->filter(fn ($labels, $key) => $shop && $shop->hasFeature($key))
            ->all();
    }

    private function ensureSameShop(Role $role): void
    {
        abort_unless($role->shop_id === auth()->user()->shop_id, 404);
    }
}
