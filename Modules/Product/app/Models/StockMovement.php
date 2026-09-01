<?php

namespace Modules\Product\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Concerns\BelongsToShop;

class StockMovement extends Model
{
    use BelongsToShop;

    protected $fillable = [
        'shop_id', 'product_id', 'batch_id', 'type',
        'quantity_change', 'quantity_before', 'quantity_after',
        'reference_type', 'reference_id', 'note', 'created_by',
    ];

    protected $casts = [
        'quantity_change' => 'decimal:2',
        'quantity_before' => 'decimal:2',
        'quantity_after' => 'decimal:2',
    ];

    /**
     * Bengali/English labels for each movement type.
     *
     * @return array<string, array{bn: string, en: string}>
     */
    public static function typeLabels(): array
    {
        return [
            'purchase' => ['bn' => 'ক্রয়', 'en' => 'Purchase'],
            'purchase_reversal' => ['bn' => 'ক্রয় বাতিল', 'en' => 'Purchase Reversal'],
            'sale' => ['bn' => 'বিক্রয়', 'en' => 'Sale'],
            'sale_reversal' => ['bn' => 'বিক্রয় বাতিল', 'en' => 'Sale Reversal'],
            'adjustment_increase' => ['bn' => 'সমন্বয় (বৃদ্ধি)', 'en' => 'Adjustment (Increase)'],
            'adjustment_decrease' => ['bn' => 'সমন্বয় (হ্রাস)', 'en' => 'Adjustment (Decrease)'],
            'transfer_out' => ['bn' => 'ট্রান্সফার (প্রেরিত)', 'en' => 'Transfer Out'],
            'transfer_in' => ['bn' => 'ট্রান্সফার (গৃহীত)', 'en' => 'Transfer In'],
            'sale_return' => ['bn' => 'বিক্রয় ফেরত', 'en' => 'Sale Return'],
            'purchase_return' => ['bn' => 'ক্রয় ফেরত', 'en' => 'Purchase Return'],
        ];
    }

    public function typeLabel(): array
    {
        return static::typeLabels()[$this->type] ?? ['bn' => $this->type, 'en' => $this->type];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
