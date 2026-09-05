<?php

namespace App\Support\Permissions;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class ShopPermissionsTeamResolver implements PermissionsTeamResolver
{
    protected int|string|null $teamId = null;

    protected bool $explicitlySet = false;

    public function setPermissionsTeamId(int|string|Model|null $id): void
    {
        if ($id instanceof Model) {
            $id = $id->getKey();
        }
        $this->teamId = $id;
        $this->explicitlySet = true;
    }

    public function getPermissionsTeamId(): int|string|null
    {
        if ($this->explicitlySet) {
            return $this->teamId;
        }

        if (auth()->check()) {
            $user = auth()->user();
            if ($user->isSuperAdmin()) {
                return 0;
            }

            return session('current_shop_id', $user->shop_id);
        }

        return null;
    }
}
