<?php

namespace Modules\Employee\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Support\PasswordInvitation;
use Modules\Company\Models\Company;
use Modules\Core\Support\TenantContext;
use Modules\Employee\Models\Employee;
use Modules\Shop\Models\Shop;
use Modules\Shop\Support\PlanLimits;
use Spatie\Permission\Models\Role;

/**
 * Creates the login account of an employee where the acting user works: a
 * shop user with a shop role, or in the company workspace a company
 * employee with a company role.
 */
class EmployeeLogin
{
    public function __construct(private TenantContext $tenant) {}

    /**
     * Only someone who may create users where they work may give an employee
     * a login: it comes with a role.
     */
    public function allowed(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $this->tenant->shopId()
            ? $user->can('users.create')
            : (bool) $this->company()?->isAdministeredBy($user);
    }

    /**
     * A shop login always has a role; a company employee may have none
     * (self-service only).
     */
    public function roleRequired(): bool
    {
        return $this->tenant->shopId() !== null;
    }

    /**
     * @return array<int, string> role id => name
     */
    public function roleOptions(): array
    {
        if ($shopId = $this->tenant->shopId()) {
            $roles = Role::where('shop_id', $shopId)->where('name', '!=', 'Super Admin')->orderBy('name')->pluck('name', 'id');

            if ($roles->isEmpty()) {
                $roles = Role::whereNull('shop_id')->where('name', '!=', 'Super Admin')->orderBy('name')->pluck('name', 'id');
            }

            return $roles->all();
        }

        return $this->company()?->roles()->orderBy('name')->pluck('name', 'id')->all() ?? [];
    }

    /**
     * Why a login can't be created for these details, or null when it can.
     *
     * @param  int  $pending  logins about to be created in the same request
     */
    public function blocker(?string $phone, ?string $email, int $pending = 0): ?string
    {
        if ($phone && User::where('phone', $phone)->exists()) {
            return 'এই মোবাইল নম্বরে একটি ইউজার আগে থেকেই আছে (A user with this phone already exists)।';
        }

        if ($email && User::where('email', $email)->exists()) {
            return 'এই ইমেইলে একটি ইউজার আগে থেকেই আছে (A user with this email already exists)।';
        }

        if ($shopId = $this->tenant->shopId()) {
            return PlanLimits::check($shopId, 'max_users', User::where('shop_id', $shopId)->count() + $pending);
        }

        return null;
    }

    /**
     * Create the login and link it to the employee. Without a password the
     * user gets a random one and sets their own from the emailed link (see
     * invite()).
     */
    public function create(Employee $employee, ?int $roleId, ?string $password = null): User
    {
        $shopId = $this->tenant->shopId();

        $user = User::create([
            'shop_id' => $shopId,
            'name' => $employee->name,
            'phone' => $employee->phone,
            'email' => $employee->email,
            'password' => Hash::make($password ?? Str::random(40)),
            // An invited user verifies the address by opening the link.
            'email_verified_at' => $password ? now() : null,
        ]);

        if ($shopId) {
            $role = Role::findOrFail($roleId);
            setPermissionsTeamId($shopId);
            $user->assignRole($role);
            $user->shops()->updateExistingPivot($shopId, ['role' => $role->name, 'is_owner' => false]);
        } else {
            $this->company()->users()->attach($user->id, [
                'role' => Company::ROLE_EMPLOYEE,
                'is_owner' => false,
                'company_role_id' => $roleId,
            ]);
        }

        $employee->forceFill(['user_id' => $user->id])->save();

        return $user;
    }

    /**
     * Email the set-password link; call it once the login is committed.
     */
    public function invite(User $user, bool $queued = false): bool
    {
        return PasswordInvitation::send($user, $this->organisationName(), $queued);
    }

    private function organisationName(): ?string
    {
        if ($shopId = $this->tenant->shopId()) {
            return Shop::whereKey($shopId)->value('name');
        }

        return $this->company()?->name;
    }

    private function company(): ?Company
    {
        $companyId = $this->tenant->companyId();

        return $companyId ? Company::find($companyId) : null;
    }
}
