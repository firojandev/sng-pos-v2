<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\Batch;
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
                    return '<div style="font-weight:600; color:var(--ink-800); font-size:13px;">'
                        .e($batch->product->name)
                        .'</div><div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace);">'
                        .e($batch->product->sku)
                        .'</div>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('mfg_date', function (Batch $batch) {
                if (! $batch->mfg_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:12.5px;">'.optional($batch->mfg_date)->format('d M, Y').'</span>';
            })
            ->editColumn('expiry_date', function (Batch $batch) {
                if (! $batch->expiry_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $formatted = optional($batch->expiry_date)->format('d M, Y');
                if ($batch->expiry_date->isPast()) {
                    return Blade::render('<x-core::badge color="red" size="xs" variant="soft">{{ $date }} (মেয়াদোত্তীর্ণ)</x-core::badge>', ['date' => $formatted]);
                }

                return '<span style="color:var(--ink-700); font-size:12.5px;">'.$formatted.'</span>';
            })
            ->editColumn('quantity', function (Batch $batch) {
                $qty = rtrim(rtrim(number_format($batch->quantity, 2), '0'), '.');

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft">{{ $qty }}</x-core::badge>', ['qty' => $qty]);
            })
            ->addColumn('action', function (Batch $batch) {
                return view('product::batches.datatables-actions', compact('batch'))->render();
            })
            ->filterColumn('batch_no', function ($query, $keyword) {
                $query->where('batches.batch_no', 'like', "%{$keyword}%");
            })
            ->filterColumn('product', function ($query, $keyword) {
                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")->orWhere('sku', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['batch_no', 'product', 'mfg_date', 'expiry_date', 'quantity', 'action'])
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

        if ($productId = request('product_id')) {
            $query->where('batches.product_id', $productId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax('', 'data.product_id = $("#filter-product").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('batch_no')->title('<span class="bn">ব্যাচ নং</span><span class="en">Batch No</span>')->width(140),
            Column::computed('product')->title('<span class="bn">পণ্য</span><span class="en">Product</span>')->width(220),
            Column::make('mfg_date')->title('<span class="bn">উৎপাদন তারিখ</span><span class="en">Mfg Date</span>')->addClass('table-cell-center')->width(130),
            Column::make('expiry_date')->title('<span class="bn">মেয়াদ শেষ</span><span class="en">Exp Date</span>')->addClass('table-cell-center')->width(150),
            Column::make('quantity')->title('<span class="bn">পরিমাণ</span><span class="en">Qty</span>')->addClass('table-cell-center')->width(100),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(110)
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
