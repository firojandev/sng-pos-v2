<?php

namespace Modules\Task\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The people tasks can be assigned to: the company's members and the login
 * users of its shops, named with their employee record when they have one.
 */
class TaskPeople
{
    /**
     * @return Collection<int, string> user id => label
     */
    public function assignable(int $companyId): Collection
    {
        $shopIds = DB::table('shops')->where('company_id', $companyId)->pluck('id');
        $designations = DB::table('employees')->where('company_id', $companyId)->whereNotNull('user_id')->pluck('designation', 'user_id');

        return User::query()
            ->where(fn ($query) => $query->whereIn('shop_id', $shopIds)
                ->orWhereHas('shops', fn ($shops) => $shops->whereIn('shops.id', $shopIds))
                ->orWhereHas('companies', fn ($companies) => $companies->where('companies.id', $companyId)))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->mapWithKeys(fn (User $user) => [$user->id => $user->name.(($designations[$user->id] ?? null) ? ' — '.$designations[$user->id] : '')]);
    }
}
