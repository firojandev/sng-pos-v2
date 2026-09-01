<?php

namespace Modules\Shop\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;

class Branch extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'phone', 'address', 'status'];

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }
}
