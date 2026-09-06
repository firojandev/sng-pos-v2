<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\BelongsToShop;
use Modules\Finance\Models\Expense;
use Modules\Product\Models\Product;

class Category extends Model
{
    use BelongsToShop;

    protected $table = 'categories';

    protected $fillable = [
        'shop_id',
        'parent_id',
        'category_id',
        'type',
        'name',
        'description',
    ];

    public function setCategoryIdAttribute($value): void
    {
        $this->attributes['parent_id'] = $value;
    }

    public function getCategoryIdAttribute(): ?int
    {
        return $this->parent_id ?? null;
    }

    /**
     * Parent category relationship.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    /**
     * Category alias for parent.
     */
    public function category(): BelongsTo
    {
        return $this->parent();
    }

    /**
     * Direct children / sub-categories relationship.
     */
    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * Sub-categories alias for children.
     */
    public function subCategories(): HasMany
    {
        return $this->children();
    }

    /**
     * Products assigned to this category as main category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Products assigned to this category as sub-category.
     */
    public function subCategoryProducts(): HasMany
    {
        return $this->hasMany(Product::class, 'sub_category_id');
    }

    /**
     * Expenses assigned to this category as main category.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_category_id');
    }

    /**
     * Expenses assigned to this category as sub-category.
     */
    public function subCategoryExpenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'expense_sub_category_id');
    }

    /**
     * Scope to filter categories by type (e.g. 'product', 'expense').
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('categories.type', $type);
    }

    /**
     * Scope to filter top-level / parent categories only.
     */
    public function scopeParents(Builder $query): Builder
    {
        return $query->whereNull('categories.parent_id');
    }

    /**
     * Scope alias for parents.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('categories.parent_id');
    }

    /**
     * Scope to filter sub-categories only.
     */
    public function scopeSubCategories(Builder $query): Builder
    {
        return $query->whereNotNull('categories.parent_id');
    }

    /**
     * Scope to filter product categories.
     */
    public function scopeProduct(Builder $query): Builder
    {
        return $query->where('categories.type', 'product');
    }

    /**
     * Scope to filter expense categories.
     */
    public function scopeExpense(Builder $query): Builder
    {
        return $query->where('categories.type', 'expense');
    }
}
