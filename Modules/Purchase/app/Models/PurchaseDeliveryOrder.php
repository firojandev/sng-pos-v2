<?php

namespace Modules\Purchase\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Core\Observers\AuditObserver;
use Modules\Shop\Models\Warehouse;
use Modules\Supplier\Models\Supplier;

class PurchaseDeliveryOrder extends Model
{
    use BelongsToShop, SoftDeletes;

    protected static function booted(): void
    {
        static::observe(AuditObserver::class);
    }

    protected $fillable = [
        'shop_id',
        'supplier_id',
        'warehouse_id',
        'order_no',
        'order_date',
        'expected_delivery_date',
        'status',
        'subtotal',
        'discount',
        'delivery_charge',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'delivery_person_name',
        'delivery_person_phone',
        'note',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'delivery_charge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    /**
     * Bengali/English labels for each order status.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function statusLabels(): array
    {
        return [
            'pending' => ['bn' => 'অপেক্ষমান', 'en' => 'Pending'],
            'partial_received' => ['bn' => 'আংশিক গৃহীত', 'en' => 'Partially Received'],
            'received' => ['bn' => 'সম্পূর্ণ গৃহীত', 'en' => 'Received'],
            'cancelled' => ['bn' => 'বাতিল', 'en' => 'Cancelled'],
        ];
    }

    public function statusLabel(): array
    {
        return static::statusLabels()[$this->status] ?? ['bn' => $this->status, 'en' => $this->status];
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'partial_received' => 'blue',
            'received' => 'emerald',
            'cancelled' => 'rose',
            default => 'slate',
        };
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseDeliveryOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PurchaseDeliveryReceipt::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalOrderedQuantity(): float
    {
        return (float) $this->items->sum('ordered_quantity');
    }

    public function totalReceivedQuantity(): float
    {
        return (float) $this->items->sum('received_quantity');
    }

    public function fulfillmentPercentage(): int
    {
        $ordered = $this->totalOrderedQuantity();
        if ($ordered <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->totalReceivedQuantity() / $ordered) * 100));
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['pending', 'partial_received'], true);
    }

    public function canBeCancelled(): bool
    {
        return $this->status === 'pending' && $this->receipts()->count() === 0;
    }

    public function canBeEdited(): bool
    {
        return $this->status === 'pending' && $this->receipts()->count() === 0;
    }
}
