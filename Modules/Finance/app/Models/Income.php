<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToShop;

class Income extends Model
{
    use BelongsToShop;

    protected $fillable = ['shop_id', 'source', 'amount', 'income_date', 'payment_method', 'note'];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
