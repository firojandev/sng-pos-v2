<?php

namespace Modules\Product\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Category as BaseCategory;

class Category extends BaseCategory
{
    protected static function booted(): void
    {
        static::addGlobalScope('product_type', function (Builder $builder) {
            $builder->where('categories.type', 'product');
        });

        static::creating(function ($model) {
            if (empty($model->type)) {
                $model->type = 'product';
            }
        });
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }
}
