<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;

class Income extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = ['shop_id', 'source', 'amount', 'income_date', 'payment_method', 'note'];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'decimal:2',
    ];
}
