<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One denomination line of a physical cash count (e.g. ৳500 × 10).
 */
class CashSessionCount extends Model
{
    protected $fillable = ['cash_session_id', 'stage', 'denomination', 'quantity', 'subtotal'];

    protected function casts(): array
    {
        return [
            'denomination' => 'decimal:2',
            'quantity' => 'integer',
            'subtotal' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }
}
