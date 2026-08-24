<?php

namespace Modules\Shop\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shop extends Model
{
    protected $fillable = ['name', 'slug', 'phone', 'address', 'status', 'enabled_features'];

    protected $casts = [
        'enabled_features' => 'array',
    ];

    public function admins(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasFeature(string $key): bool
    {
        return in_array($key, $this->enabled_features ?? [], true);
    }
}
