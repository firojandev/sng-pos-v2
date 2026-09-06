<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Support\PaymentMethods;
use Modules\Finance\Models\Account;

class SalePayment extends Model
{
    use SoftDeletes;

    protected $fillable = ['sale_id', 'account_id', 'method', 'amount', 'payment_date', 'note'];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function methodLabel(): array
    {
        return PaymentMethods::all()[$this->method] ?? ['bn' => $this->method, 'en' => $this->method];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
