<?php

namespace Modules\Shop\Models;

use Revoltify\Subscriptionify\Models\Plan as BasePlan;

class Plan extends BasePlan
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'is_free',
        'is_active',
        'trial_days',
        'billing_period',
        'billing_interval',
        'grace_days',
        'sort_order',
        'billing_cycle',
        'max_users',
        'max_branches',
        'max_warehouses',
        'max_products',
        'features',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'price' => 'decimal:2',
            'features' => 'array',
        ]);
    }

    public function hasUnlimited(string $limitKey): bool
    {
        return $this->{$limitKey} === null;
    }
}
