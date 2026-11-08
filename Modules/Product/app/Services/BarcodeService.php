<?php

namespace Modules\Product\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;

class BarcodeService
{
    /** @param array<string, mixed> $filters */
    public function products(array $filters): Builder
    {
        return Product::query()
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%"));
            })
            ->when($filters['category_id'] ?? null, fn (Builder $q, $id) => $q->where('category_id', $id))
            ->when($filters['product_id'] ?? null, fn (Builder $q, $id) => $q->whereKey($id));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Product>
     */
    public function generate(array $data): Collection
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($data) {
                    $query = ! empty($data['all_filtered'])
                        ? $this->products($data)
                        : Product::whereKey($data['product_ids']);
                    $products = $query->with(['category', 'units'])->orderBy('id')->limit(501)->lockForUpdate()->get();

                    $labelCount = $products->sum(fn (Product $product) => $this->labelQuantity($data, $product->id));

                    if ($products->isEmpty() || $products->count() > 500 || $labelCount > 1000) {
                        throw ValidationException::withMessages(['product_ids' => 'Select 1–500 products and at most 1,000 labels. Narrow your filters if needed.']);
                    }
                    if (empty($data['all_filtered']) && $products->count() !== count($data['product_ids'])) {
                        throw ValidationException::withMessages(['product_ids' => 'One or more selected products are unavailable.']);
                    }

                    foreach ($products as $product) {
                        if (! $product->barcode || ! empty($data['regenerate'])) {
                            Gate::authorize('products.edit');
                            $product->update(['barcode' => $this->newNumber(), 'has_barcode' => true]);
                        }
                        if (! $this->supports($product->barcode, $data['format'])) {
                            throw ValidationException::withMessages(['format' => "{$product->name}: existing barcode is incompatible with this format. Choose Code 128 or explicitly regenerate."]);
                        }
                    }

                    return $products;
                }, 5);
            } catch (UniqueConstraintViolationException $exception) {
                if ($attempt === 4) {
                    throw $exception;
                }
            }
        }

        throw new \LogicException('Barcode generation did not complete.');
    }

    /** @param array<string, mixed> $settings */
    public function labelQuantity(array $settings, int $productId): int
    {
        return (int) ($settings['quantities'][$productId] ?? $settings['copies'] ?? 1);
    }

    private function newNumber(): string
    {
        do {
            $body = '2'.str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT);
            $number = $body.$this->checkDigit($body);
        } while (Product::withoutGlobalScopes()->where('barcode', $number)->exists());

        return $number;
    }

    private function checkDigit(string $body): int
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $body[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return (10 - $sum % 10) % 10;
    }

    public function supports(string $number, string $format): bool
    {
        if ($format === 'EAN13') {
            return preg_match('/^[0-9]{13}$/D', $number) === 1
                && (int) $number[12] === $this->checkDigit(substr($number, 0, 12));
        }

        return preg_match('/^[\x20-\x7E]+$/D', $number) === 1;
    }
}
