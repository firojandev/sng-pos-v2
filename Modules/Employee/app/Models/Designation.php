<?php

namespace Modules\Employee\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToCompany;

class Designation extends Model
{
    use BelongsToCompany;

    protected $fillable = ['company_id', 'name'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }
}
