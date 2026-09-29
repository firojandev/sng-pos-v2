<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Shop\Models\Shop;

/**
 * One movement in a customer's points: earned (positive, with the points
 * still unspent in `remaining`), or redeemed, expired, reversed or adjusted.
 */
class LoyaltyPointTransaction extends Model
{
    use BelongsToCompany;

    public const EARN = 'earn';

    public const REDEEM = 'redeem';

    public const EXPIRE = 'expire';

    public const ADJUST = 'adjust';

    public const REVERSE = 'reverse';

    protected $fillable = [
        'company_id', 'shop_id', 'customer_id', 'type', 'points', 'value',
        'remaining', 'expires_at', 'source_type', 'source_id', 'note', 'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'value' => 'decimal:2',
            'remaining' => 'integer',
            'expires_at' => 'date',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
