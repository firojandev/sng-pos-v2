<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Support\PaymentMethods;
use Modules\Finance\Models\Account;

class PurchasePayment extends Model
{
    use SoftDeletes;

    protected $fillable = ['purchase_id', 'account_id', 'method', 'amount', 'note'];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function methodLabel(): array
    {
        return PaymentMethods::all()[$this->method] ?? ['bn' => $this->method, 'en' => $this->method];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
