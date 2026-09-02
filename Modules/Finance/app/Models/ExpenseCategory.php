<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Category as BaseCategory;

class ExpenseCategory extends BaseCategory
{
    protected static function booted(): void
    {
        static::addGlobalScope('expense_type', function (Builder $builder) {
            $builder->where('categories.type', 'expense');
        });

        static::creating(function ($model) {
            if (empty($model->type)) {
                $model->type = 'expense';
            }
            if (isset($model->category_id) && ! isset($model->parent_id)) {
                $model->parent_id = $model->category_id;
                unset($model->category_id);
            }
        });
    }

    public function getCategoryIdAttribute(): ?int
    {
        return $this->parent_id;
    }

    public function setCategoryIdAttribute($value): void
    {
        $this->attributes['parent_id'] = $value;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function category(): BelongsTo
    {
        return $this->parent();
    }

    public function subCategories(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    public function subCategoryExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_sub_category_id');
    }
}
