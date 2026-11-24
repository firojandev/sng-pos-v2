<?php

namespace Modules\Sales\Services;

use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\Sale;
use Modules\Sales\Models\SaleItem;

class TopSellingProducts
{
    /**
     * Period keys shown as tabs, with their Bengali and English labels.
     *
     * @var array<string, array{bn: string, en: string}>
     */
    public const RANGES = [
        'today' => ['bn' => 'আজ', 'en' => 'Today'],
        'yesterday' => ['bn' => 'গতকাল', 'en' => 'Yesterday'],
        'week' => ['bn' => 'এই সপ্তাহ', 'en' => 'This week'],
        'month' => ['bn' => 'এই মাস', 'en' => 'This month'],
        'year' => ['bn' => 'এই বছর', 'en' => 'This year'],
        'all' => ['bn' => 'সর্বমোট', 'en' => 'All-time'],
    ];

    /**
     * Inclusive start and end dates for a period key; nulls mean unbounded.
     *
     * @return array{0: ?string, 1: ?string}
     */
    public static function rangeBounds(string $range): array
    {
        return match ($range) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->toDateString()],
            default => [null, null],
        };
    }

    /**
     * Products with the highest sales amount or quantity between two dates, limited to the
     * sales the current user may see. Quantity is in each product's base unit.
     *
     * @return list<array{name: string, quantity: float, unit: ?string, amount: float}>
     */
    public function between(?string $from, ?string $to, int $limit = 10, string $sortBy = 'amount'): array
    {
        $visibleSaleIds = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->select('id');

        $baseUnitCode = DB::table('product_units')
            ->join('units', 'units.id', '=', 'product_units.unit_id')
            ->whereColumn('product_units.product_id', 'sale_items.product_id')
            ->where('product_units.is_base', true)
            ->select('units.short_code')
            ->limit(1);

        $orderColumn = $sortBy === 'quantity' ? 'quantity' : 'amount';

        return SaleItem::query()
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('product_units as sold_unit', function ($join) {
                $join->on('sold_unit.product_id', '=', 'sale_items.product_id')
                    ->on('sold_unit.unit_id', '=', 'sale_items.unit_id');
            })
            ->whereIn('sale_items.sale_id', $visibleSaleIds)
            ->groupBy('sale_items.product_id', 'products.name')
            ->select('products.name as name')
            ->selectRaw('SUM((sale_items.quantity - COALESCE((SELECT SUM(sri.quantity) FROM sale_return_items sri WHERE sri.sale_item_id = sale_items.id AND sri.deleted_at IS NULL), 0)) * CASE
                WHEN sold_unit.conversion_factor > 0 AND sold_unit.is_smaller_unit = 1 THEN 1.0 / sold_unit.conversion_factor
                WHEN sold_unit.conversion_factor > 0 THEN sold_unit.conversion_factor
                ELSE 1 END) as quantity')
            ->selectRaw('SUM(sale_items.total - COALESCE((SELECT SUM(sri.total) FROM sale_return_items sri WHERE sri.sale_item_id = sale_items.id AND sri.deleted_at IS NULL), 0)) as amount')
            ->selectSub($baseUnitCode, 'unit_code')
            ->havingRaw("{$orderColumn} > 0")
            ->orderByDesc($orderColumn)
            ->limit($limit)
            ->get()
            ->map(fn (SaleItem $item): array => [
                'name' => (string) $item->name,
                'quantity' => round((float) $item->quantity, 2),
                'unit' => $item->unit_code !== null ? (string) $item->unit_code : null,
                'amount' => round((float) $item->amount, 2),
            ])
            ->all();
    }

    /**
     * @return list<array{name: string, quantity: float, unit: ?string, amount: float}>
     */
    public function byAmount(?string $from, ?string $to, int $limit = 10): array
    {
        return $this->between($from, $to, $limit, 'amount');
    }

    /**
     * @return list<array{name: string, quantity: float, unit: ?string, amount: float}>
     */
    public function byQuantity(?string $from, ?string $to, int $limit = 10): array
    {
        return $this->between($from, $to, $limit, 'quantity');
    }
}
