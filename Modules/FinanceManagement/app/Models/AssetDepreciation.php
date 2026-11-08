<?php

namespace Modules\FinanceManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\BelongsToShop;

/**
 * One month's depreciation of an asset, posted to the ledger.
 */
class AssetDepreciation extends Model
{
    use BelongsToShop;

    protected $fillable = ['asset_id', 'shop_id', 'period', 'amount', 'accumulated', 'book_value'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['period' => 'date', 'amount' => 'decimal:2', 'accumulated' => 'decimal:2', 'book_value' => 'decimal:2'];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }
}
