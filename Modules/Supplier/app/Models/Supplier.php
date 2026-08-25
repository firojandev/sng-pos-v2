<?php

namespace Modules\Supplier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Purchase\Models\Purchase;

class Supplier extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'phone', 'email', 'address', 'opening_due', 'status'];

    protected $casts = [
        'opening_due' => 'decimal:2',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function totalDue(): string
    {
        return bcadd((string) $this->opening_due, (string) $this->purchases()->sum('due_amount'), 2);
    }
}
