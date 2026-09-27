<?php

namespace Modules\Product\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Product\Models\Product;

/**
 * Folds a duplicate product into the product that is kept: every sale,
 * purchase, stock and listing record moves to the kept product, and the
 * duplicate is deleted.
 */
class ProductMergeService
{
    /**
     * Records that only point at the product.
     *
     * @var list<string>
     */
    private const REFERENCING_TABLES = [
        'sale_items', 'sale_return_items',
        'purchase_items', 'purchase_return_items', 'purchase_receipt_items',
        'purchase_delivery_order_items', 'purchase_delivery_receipt_items',
        'stock_movements', 'stock_adjustments', 'stock_transfer_items',
    ];

    public function merge(Product $duplicate, Product $keep): void
    {
        $this->ensureMergeable($duplicate, $keep);

        DB::transaction(function () use ($duplicate, $keep) {
            $this->moveBatches($duplicate, $keep);
            $this->moveUniquePerProduct('product_units', 'unit_id', $duplicate, $keep);
            $this->moveUniquePerProduct('shop_products', 'shop_id', $duplicate, $keep);
            $this->moveUniquePerProduct('company_products', 'company_id', $duplicate, $keep);

            foreach (self::REFERENCING_TABLES as $table) {
                DB::table($table)->where('product_id', $duplicate->id)->update(['product_id' => $keep->id]);
            }

            $barcode = $duplicate->barcode;
            Product::withoutGlobalScopes()->whereKey($duplicate->id)->delete();

            if ($barcode && ! $keep->barcode) {
                Product::withoutGlobalScopes()->whereKey($keep->id)->update(['has_barcode' => true, 'barcode' => $barcode]);
            }
        });
    }

    /**
     * No company may lose access to a product it uses: both products belong
     * to the same company, both are shared, or a company's product is folded
     * into a shared one.
     */
    private function ensureMergeable(Product $duplicate, Product $keep): void
    {
        if ($duplicate->is($keep)) {
            throw ValidationException::withMessages(['keep_id' => 'একই পণ্য নিজের সাথে মার্জ করা যায় না (A product cannot be merged into itself)।']);
        }

        $sameOwner = $duplicate->company_id === $keep->company_id;
        $intoShared = $keep->company_id === null;

        if (! $sameOwner && ! $intoShared) {
            throw ValidationException::withMessages([
                'keep_id' => 'ভিন্ন কোম্পানির পণ্য বা শেয়ার্ড পণ্যকে কোনো কোম্পানির পণ্যে মার্জ করা যায় না (Only products of the same company, or into a shared product, can be merged)।',
            ]);
        }
    }

    /**
     * A batch number is unique per product and warehouse; a duplicate batch
     * that would collide keeps its stock under a suffixed number.
     */
    private function moveBatches(Product $duplicate, Product $keep): void
    {
        $batches = DB::table('batches')->where('product_id', $duplicate->id)->get(['id', 'batch_no', 'warehouse_id']);

        foreach ($batches as $batch) {
            $collides = DB::table('batches')
                ->where('product_id', $keep->id)
                ->where('batch_no', $batch->batch_no)
                ->where('warehouse_id', $batch->warehouse_id)
                ->exists();

            DB::table('batches')->where('id', $batch->id)->update([
                'product_id' => $keep->id,
                'batch_no' => $collides ? $batch->batch_no.'-M'.$duplicate->id : $batch->batch_no,
            ]);
        }
    }

    /**
     * Rows unique per (owner, product): the kept product's rows win, the
     * duplicate's rows move over where the kept product has none.
     */
    private function moveUniquePerProduct(string $table, string $ownerColumn, Product $duplicate, Product $keep): void
    {
        $keptOwners = DB::table($table)->where('product_id', $keep->id)->pluck($ownerColumn);

        DB::table($table)->where('product_id', $duplicate->id)->whereIn($ownerColumn, $keptOwners)->delete();
        DB::table($table)->where('product_id', $duplicate->id)->update(['product_id' => $keep->id]);
    }
}
