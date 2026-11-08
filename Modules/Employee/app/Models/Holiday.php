<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Concerns\BelongsToCompany;

class Holiday extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'date', 'name', 'type'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['date' => 'date'];
    }
}
