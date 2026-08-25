<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;

class ExpenseCategory extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'name', 'description'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
