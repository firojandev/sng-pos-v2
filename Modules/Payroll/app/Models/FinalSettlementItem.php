<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalSettlementItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['final_settlement_id', 'type', 'code', 'name', 'amount', 'employee_loan_id', 'sort_order'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(FinalSettlement::class, 'final_settlement_id');
    }
}
