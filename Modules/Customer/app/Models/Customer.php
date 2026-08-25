<?php

namespace Modules\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Sales\Models\Sale;

class Customer extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'phone', 'email', 'address', 'opening_due', 'status'];

    protected $casts = [
        'opening_due' => 'decimal:2',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function totalDue(): string
    {
        return bcadd((string) $this->opening_due, (string) $this->sales()->sum('due_amount'), 2);
    }
}
