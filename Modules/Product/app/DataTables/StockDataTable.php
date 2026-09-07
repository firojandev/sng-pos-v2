<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class StockDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Product>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Product $product) {
                $avatar = '';
                if ($product->image_url) {
                    $avatar = '<img src="'.e($product->image_url).'" alt="" style="width:34px; height:34px; border-radius:8px; object-fit:cover; flex-shrink:0;">';
                } else {
                    $initial = mb_substr($product->name, 0, 1);
                    $avatar = '<div class="av" style="width:34px; height:34px; border-radius:8px; background:var(--teal-800); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13.5px; flex-shrink:0;">'.e($initial).'</div>';
                }

                $sizeHtml = $product->size ? ' <span style="font-size:11.5px; color:var(--ink-500); font-weight:500;">('.e($product->size).')</span>' : '';
                $skuHtml = $product->sku ? '<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">SKU: '.e($product->sku).'</div>' : '';

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px; max-width:280px;">'
                    .$avatar
                    .'<div style="min-width:0; flex:1;">'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word;" title="'.e($product->name).'">'
                    .e($product->name)
                    .$sizeHtml
                    .'</div>'
                    .$skuHtml
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('category', function (Product $product) {
                if ($product->category) {
                    $catName = e($product->category->name);
                    $subHtml = $product->subCategory ? '<div style="color:var(--ink-400); font-size:11px; margin-top:2px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">'.e($product->subCategory->name).'</div>' : '';

                    return '<div style="font-weight:600; color:var(--ink-800); font-size:12.5px; max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.$catName.'">'.$catName.'</div>'.$subHtml;
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('purchase_price', function (Product $product) {
                return '<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); font-size:13px; white-space:nowrap;">৳'.number_format((float) $product->purchase_price, 2).'</div>';
            })
            ->editColumn('sale_price', function (Product $product) {
                return '<div style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--teal-700); font-size:13px; white-space:nowrap;">৳'.number_format((float) $product->sale_price, 2).'</div>';
            })
            ->addColumn('total_stock', function (Product $product) {
                $qty = (float) ($product->total_stock ?? 0);
                $formattedQty = rtrim(rtrim(number_format($qty, 2), '0'), '.');
                $unitName = $product->units->first()?->short_code ?? '';
                $batchesCount = (int) ($product->batches_count ?? 0);

                $color = 'var(--green-600)';
                $bgColor = 'var(--green-100)';
                if ($qty <= 0) {
                    $color = 'var(--red-600)';
                    $bgColor = 'var(--red-100)';
                } elseif ($product->alert_qty > 0 && $qty <= $product->alert_qty) {
                    $color = 'var(--gold-ink)';
                    $bgColor = 'var(--gold-100)';
                }

                $pill = '<div style="display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; background:'.$bgColor.'; color:'.$color.'; font-family:var(--font-mono, monospace); font-weight:800; font-size:13px; white-space:nowrap;">'
                    .$formattedQty.($unitName ? ' <span style="font-size:11px; font-weight:600;">'.e($unitName).'</span>' : '')
                    .'</div>';

                $batchText = $batchesCount > 0
                    ? '<div style="font-size:11px; color:var(--ink-400); margin-top:3px; white-space:nowrap;">'.$batchesCount.' টি ব্যাচ</div>'
                    : '<div style="font-size:11px; color:var(--ink-400); margin-top:3px; white-space:nowrap;">কোনো ব্যাচ নেই</div>';

                return '<div style="text-align:right;">'.$pill.$batchText.'</div>';
            })
            ->addColumn('stock_value', function (Product $product) {
                $qty = (float) ($product->total_stock ?? 0);
                $price = (float) $product->purchase_price;
                $val = max(0, $qty * $price);

                return '<div style="text-align:right;">'
                    .'<div style="font-family:var(--font-mono, monospace); font-weight:800; color:var(--teal-800); font-size:13px; white-space:nowrap;">৳'.number_format($val, 2).'</div>'
                    .'</div>';
            })
            ->addColumn('stock_status', function (Product $product) {
                $qty = (float) ($product->total_stock ?? 0);
                if ($qty <= 0) {
                    return Blade::render('<x-core::badge color="red" size="xs" :dot="true" label="স্টক আউট" label-en="Out of Stock" />');
                } elseif ($product->alert_qty > 0 && $qty <= $product->alert_qty) {
                    return Blade::render('<x-core::badge color="gold" size="xs" :dot="true" label="কম মজুদ" label-en="Low Stock" />');
                }

                return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="মজুদ আছে" label-en="In Stock" />');
            })
            ->addColumn('action', function (Product $product) {
                return view('product::stock.datatables-actions', compact('product'))->render();
            })
            ->filter(function ($query) {
                if ($keyword = request('search.value')) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('products.name', 'like', "%{$keyword}%")
                            ->orWhere('products.sku', 'like', "%{$keyword}%")
                            ->orWhere('products.barcode', 'like', "%{$keyword}%")
                            ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$keyword}%"));
                    });
                }
            }, true)
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('products.name', $order);
            })
            ->orderColumn('category', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT name FROM categories WHERE categories.id = products.category_id), "") '.$order);
            })
            ->orderColumn('purchase_price', function ($query, $order) {
                $query->orderBy('products.purchase_price', $order);
            })
            ->orderColumn('sale_price', function ($query, $order) {
                $query->orderBy('products.sale_price', $order);
            })
            ->orderColumn('total_stock', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) '.$order);
            })
            ->orderColumn('stock_value', function ($query, $order) {
                $query->orderByRaw('(COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) * products.purchase_price) '.$order);
            })
            ->orderColumn('stock_status', function ($query, $order) {
                $query->orderByRaw('CASE WHEN COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= 0 THEN 1 WHEN products.alert_qty > 0 AND COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= products.alert_qty THEN 2 ELSE 3 END '.$order);
            })
            ->rawColumns(['name', 'category', 'purchase_price', 'sale_price', 'total_stock', 'stock_value', 'stock_status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['category', 'subCategory', 'brand', 'units'])
            ->select([
                'products.id',
                'products.shop_id',
                'products.category_id',
                'products.sub_category_id',
                'products.brand_id',
                'products.name',
                'products.sku',
                'products.size',
                'products.barcode',
                'products.image_url',
                'products.purchase_price',
                'products.sale_price',
                'products.alert_qty',
                'products.status',
                'products.created_at',
            ])
            ->withSum('batches as total_stock', 'quantity')
            ->withCount('batches');

        if ($categoryId = request('category_id')) {
            $query->where('products.category_id', $categoryId);
        }

        if ($brandId = request('brand_id')) {
            $query->where('products.brand_id', $brandId);
        }

        if ($stockStatus = request('stock_status')) {
            if ($stockStatus === 'out') {
                $query->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= 0');
            } elseif ($stockStatus === 'low') {
                $query->where('products.alert_qty', '>', 0)
                    ->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) > 0')
                    ->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) <= products.alert_qty');
            } elseif ($stockStatus === 'in') {
                $query->whereRaw('COALESCE((SELECT SUM(quantity) FROM batches WHERE batches.product_id = products.id), 0) > 0');
            }
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('stock-data-table')
            ->minifiedAjax('', 'data.stock_status = $("#filter-stock-status").val(); data.category_id = $("#filter-category").val(); data.brand_id = $("#filter-brand").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')
                ->title('<span class="bn">পণ্য</span><span class="en">Product</span>')
                ->width(220),
            Column::computed('category')
                ->title('<span class="bn">ক্যাটাগরি</span><span class="en">Category</span>')
                ->width(140),
            Column::make('purchase_price')
                ->title('<span class="bn">ক্রয় দর</span><span class="en">Purchase Rate</span>')
                ->addClass('table-cell-right')
                ->width(120),
            Column::make('sale_price')
                ->title('<span class="bn">বিক্রয় দর</span><span class="en">Sale Rate</span>')
                ->addClass('table-cell-right')
                ->width(120),
            Column::computed('total_stock')
                ->title('<span class="bn">বর্তমান মজুদ</span><span class="en">Current Stock</span>')
                ->addClass('table-cell-right')
                ->width(140),
            Column::computed('stock_value')
                ->title('<span class="bn">মজুদ মূল্য</span><span class="en">Stock Value</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::computed('stock_status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(100),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(115)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Stock_'.date('YmdHis');
    }
}
