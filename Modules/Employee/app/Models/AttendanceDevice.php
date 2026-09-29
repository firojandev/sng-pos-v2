<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToCompany;
use Modules\Shop\Models\Shop;

/**
 * An attendance machine that pushes punches to the server (ZKTeco ADMS).
 * It's identified by its serial number.
 */
class AttendanceDevice extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'shop_id', 'name', 'serial_number', 'is_active', 'last_seen_at', 'last_ip'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'last_seen_at' => 'datetime'];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function punches(): HasMany
    {
        return $this->hasMany(AttendanceDevicePunch::class);
    }
}
