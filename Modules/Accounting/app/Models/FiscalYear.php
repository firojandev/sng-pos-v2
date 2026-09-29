<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

/**
 * A company's accounting year. Entries can't be posted into a closed year.
 */
class FiscalYear extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'starts_on', 'ends_on', 'is_closed'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_closed' => 'boolean'];
    }
}
