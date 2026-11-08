<?php

namespace Modules\Product\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Observers\AuditObserver;
use Modules\Core\Support\TenantContext;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Warehouse;

/**
 * A stock transfer between warehouses. It may cross into another shop of
 * the same company: shop_id is the sending shop, to_shop_id the receiving
 * one. Both shops see the transfer; the sender approves and dispatches it,
 * the receiver confirms receipt.
 */
class StockTransfer extends Model
{
    use SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);

        static::addGlobalScope('shop', function (Builder $builder) {
            $user = Auth::user();

            if (! $user || $user->isSuperAdmin() || ! $user->shop_id) {
                return;
            }

            $builder->where(function (Builder $query) use ($user) {
                $query->where('stock_transfers.shop_id', $user->shop_id)
                    ->orWhere('stock_transfers.to_shop_id', $user->shop_id);
            });
        });

        static::creating(function (StockTransfer $transfer) {
            $transfer->shop_id ??= app(TenantContext::class)->shopId();
            $transfer->to_shop_id ??= Warehouse::withoutGlobalScope('shop')->whereKey($transfer->to_warehouse_id)->value('shop_id')
                ?? $transfer->shop_id;
        });
    }

    protected $fillable = [
        'shop_id', 'to_shop_id', 'transfer_no', 'from_warehouse_id', 'to_warehouse_id', 'status',
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
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id')->withoutGlobalScope('shop');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id')->withoutGlobalScope('shop');
    }

    public function fromShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function toShop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'to_shop_id');
    }

    public function isBetweenShops(): bool
    {
        return (int) $this->shop_id !== (int) $this->to_shop_id;
    }

    public function isSentBy(?int $shopId): bool
    {
        return (int) $this->shop_id === (int) $shopId;
    }

    public function isReceivedBy(?int $shopId): bool
    {
        return (int) $this->to_shop_id === (int) $shopId;
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
