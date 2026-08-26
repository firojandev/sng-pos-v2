<?php

namespace Modules\Product\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Shop\Models\Warehouse;

class StockTransfer extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id', 'transfer_no', 'from_warehouse_id', 'to_warehouse_id', 'status',
        'requested_by', 'approved_by', 'dispatched_by', 'received_by',
        'approved_at', 'dispatched_at', 'received_at', 'note',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    /**
     * Bengali/English labels for each transfer status.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'pending' => ['bn' => 'অনুরোধ করা হয়েছে', 'en' => 'Requested'],
            'approved' => ['bn' => 'অনুমোদিত', 'en' => 'Approved'],
            'dispatched' => ['bn' => 'প্রেরিত', 'en' => 'Dispatched'],
            'received' => ['bn' => 'গৃহীত', 'en' => 'Received'],
            'cancelled' => ['bn' => 'বাতিল', 'en' => 'Cancelled'],
        ];
    }

    public function statusLabel(): array
    {
        return static::statusLabels()[$this->status] ?? ['bn' => $this->status, 'en' => $this->status];
    }

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
