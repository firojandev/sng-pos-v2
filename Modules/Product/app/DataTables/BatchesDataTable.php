<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\Batch;
use Modules\Product\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class BatchesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Batch>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('batch_no', function (Batch $batch) {
                return '<div style="font-weight:700; color:var(--ink-900); font-family:var(--font-mono, monospace); font-size:13px;">'
                    .e($batch->batch_no)
                    .'</div>';
            })
            ->addColumn('product', function (Batch $batch) {
                if ($batch->product) {
                    $skuHtml = $batch->product->sku
                        ? '<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace); white-space:nowrap;">'
                            .e($batch->product->sku)
                            .'</div>'
                        : '';

                    return '<div style="font-weight:600; color:var(--ink-800); font-size:13px; max-width:240px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word;" title="'.e($batch->product->name).'">'
                        .e($batch->product->name)
                        .'</div>'
                        .$skuHtml;
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('mfg_date', function (Batch $batch) {
                if (! $batch->mfg_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:12.5px; white-space:nowrap;">'.optional($batch->mfg_date)->format('d M, Y').'</span>';
            })
            ->editColumn('expiry_date', function (Batch $batch) {
                if (! $batch->expiry_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $formatted = optional($batch->expiry_date)->format('d M, Y');
                if ($batch->expiry_date->isPast()) {
                    return Blade::render('<x-core::badge color="red" size="xs" variant="soft" style="white-space:nowrap;">{{ $date }} (মেয়াদোত্তীর্ণ)</x-core::badge>', ['date' => $formatted]);
                }

                return '<span style="color:var(--ink-700); font-size:12.5px; white-space:nowrap;">'.$formatted.'</span>';
            })
            ->editColumn('quantity', function (Batch $batch) {
                $qty = rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.');

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft" style="white-space:nowrap;">{{ $qty }}</x-core::badge>', ['qty' => $qty]);
            })
            ->addColumn('purchase_price', function (Batch $batch) {
                if (! $batch->product) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }
                $price = (float) $batch->product->purchase_price;

                return '<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); font-size:13px; white-space:nowrap;">৳'.number_format($price, 2).'</div>';
            })
            ->addColumn('sale_price', function (Batch $batch) {
                if (! $batch->product) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }
                $price = (float) $batch->product->sale_price;

                return '<div style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--teal-700); font-size:13px; white-space:nowrap;">৳'.number_format($price, 2).'</div>';
            })
            ->addColumn('stock_valuation', function (Batch $batch) {
                $qty = (float) ($batch->quantity ?? 0);
                $price = (float) ($batch->product?->purchase_price ?? 0);
                $valuation = max(0, $qty * $price);

                return '<div style="font-family:var(--font-mono, monospace); font-weight:800; color:var(--teal-800); font-size:13px; white-space:nowrap;">৳'.number_format($valuation, 2).'</div>';
            })
            ->addColumn('action', function (Batch $batch) {
                return view('product::batches.datatables-actions', compact('batch'))->render();
            })
            ->orderColumn('purchase_price', function ($query, $order) {
                $query->orderBy(Product::select('purchase_price')->whereColumn('products.id', 'batches.product_id'), $order);
            })
            ->orderColumn('sale_price', function ($query, $order) {
                $query->orderBy(Product::select('sale_price')->whereColumn('products.id', 'batches.product_id'), $order);
            })
            ->orderColumn('stock_valuation', function ($query, $order) {
                $query->orderByRaw('(batches.quantity * COALESCE((SELECT purchase_price FROM products WHERE products.id = batches.product_id), 0)) '.$order);
            })
            ->filterColumn('batch_no', function ($query, $keyword) {
                $query->where('batches.batch_no', 'like', "%{$keyword}%");
            })
            ->filterColumn('product', function ($query, $keyword) {
                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")->orWhere('sku', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['batch_no', 'product', 'mfg_date', 'expiry_date', 'quantity', 'purchase_price', 'sale_price', 'stock_valuation', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Batch>
     */
    public function query(Batch $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['product'])
            ->select([
                'batches.id',
                'batches.shop_id',
                'batches.product_id',
                'batches.batch_no',
                'batches.mfg_date',
                'batches.expiry_date',
                'batches.quantity',
                'batches.created_at',
            ]);

        if (request()->filled('product_id')) {
            $query->where('batches.product_id', request('product_id'));
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax(route('batches.index'), 'data.product_id = $("#filter-product").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('batch_no')->title('<span class="bn">ব্যাচ নং</span><span class="en">Batch No</span>')->width(130),
            Column::computed('product')->title('<span class="bn">পণ্য</span><span class="en">Product</span>')->width(200),
            Column::make('mfg_date')->title('<span class="bn">উৎপাদন তারিখ</span><span class="en">Mfg Date</span>')->addClass('table-cell-center')->width(115),
            Column::make('expiry_date')->title('<span class="bn">মেয়াদ শেষ</span><span class="en">Exp Date</span>')->addClass('table-cell-center')->width(130),
            Column::make('quantity')->title('<span class="bn">পরিমাণ</span><span class="en">Qty</span>')->addClass('table-cell-center')->width(85),
            Column::computed('purchase_price')
                ->title('<span class="bn">ক্রয় মূল্য</span><span class="en">Purchase Price</span>')
                ->orderable(true)
                ->addClass('table-cell-right')
                ->width(115),
            Column::computed('sale_price')
                ->title('<span class="bn">বিক্রয় মূল্য</span><span class="en">Sale Price</span>')
                ->orderable(true)
                ->addClass('table-cell-right')
                ->width(115),
            Column::computed('stock_valuation')
                ->title('<span class="bn">স্টক মূল্যায়ন</span><span class="en">Stock Valuation</span>')
                ->orderable(true)
                ->addClass('table-cell-right')
                ->width(125),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(90)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Batches_'.date('YmdHis');
    }
}
