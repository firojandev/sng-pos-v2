<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A customer's (free) membership of the company's loyalty programme.
 */
class CustomerMembership extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'customer_id', 'card_no', 'joined_at', 'status'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['joined_at' => 'date'];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
