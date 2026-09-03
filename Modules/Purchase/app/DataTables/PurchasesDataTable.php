<?php

namespace Modules\Purchase\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Purchase\Models\Purchase;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class PurchasesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Purchase>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $clone = (clone $query);
        $clone->getQuery()->columns = null;
        $totals = $clone->selectRaw('
            COALESCE(SUM(purchases.total), 0) as agg_total,
            COALESCE(SUM(purchases.paid_amount), 0) as agg_paid,
            COALESCE(SUM(purchases.due_amount), 0) as agg_due,
            COALESCE(COUNT(purchases.id), 0) as agg_count
        ')->first();

        return (new EloquentDataTable($query))
            ->with([
                'totalAmount' => number_format((float) ($totals->agg_total ?? 0), 2),
                'totalPaid' => number_format((float) ($totals->agg_paid ?? 0), 2),
                'totalDue' => number_format((float) ($totals->agg_due ?? 0), 2),
                'totalCount' => (int) ($totals->agg_count ?? 0),
            ])
            ->editColumn('supplier', function (Purchase $purchase) {
                $name = e($purchase->supplier->name ?? '—');
                $initial = mb_substr($purchase->supplier->name ?? '?', 0, 1);
                $phone = $purchase->supplier?->phone
                    ? '<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace);">'.e($purchase->supplier->phone).'</div>'
                    : '';

                return '<div style="display:flex; align-items:center; gap:8px;">'
                    .'<div style="width:28px; height:28px; border-radius:6px; background:var(--teal-100); color:var(--teal-800); display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div><div style="font-weight:700; color:var(--ink-900);">'.$name.'</div>'.$phone.'</div>'
                    .'</div>';
            })
            ->editColumn('invoice_no', function (Purchase $purchase) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); background:var(--paper-line); padding:3px 8px; border-radius:6px; border:1px solid var(--border); font-size:12px;">#'.e($purchase->invoice_no).'</span>';
            })
            ->addColumn('batch_no', function (Purchase $purchase) {
                $batches = $purchase->items->pluck('batch_no')->filter()->unique();
                if ($batches->isEmpty()) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12px; color:var(--ink-700);">'.e($batches->implode(', ')).'</span>';
            })
            ->addColumn('items_count', function (Purchase $purchase) {
                $qty = $purchase->items->sum('quantity');

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-700); font-weight:600;">'.rtrim(rtrim(number_format((float) $qty, 2), '0'), '.').'</span>';
            })
            ->editColumn('total', function (Purchase $purchase) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">৳'.number_format((float) $purchase->total, 2).'</span>';
            })
            ->editColumn('purchase_date', function (Purchase $purchase) {
                if (! $purchase->purchase_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-size:12.5px; color:var(--ink-700); white-space:nowrap;">'
                    .e($purchase->purchase_date->format('d M, Y'))
                    .'</span>';
            })
            ->editColumn('payment_status', function (Purchase $purchase) {
                if ($purchase->payment_status === 'paid') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="পরিশোধিত" label-en="Paid" />');
                } elseif ($purchase->payment_status === 'partial') {
                    return Blade::render('<x-core::badge color="gold" size="xs" :dot="true" label="আংশিক" label-en="Partial" />');
                }

                return Blade::render('<x-core::badge color="red" size="xs" :dot="true" label="বাকি" label-en="Due" />');
            })
            ->addColumn('action', function (Purchase $purchase) {
                return view('purchase::purchase.datatables-actions', compact('purchase'))->render();
            })
            ->filterColumn('supplier', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('phone', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('invoice_no', function ($query, $keyword) {
                $clean = ltrim($keyword, '#');
                $query->where('purchases.invoice_no', 'like', "%{$clean}%");
            })
            ->filterColumn('batch_no', function ($query, $keyword) {
                $query->whereHas('items', function ($q) use ($keyword) {
                    $q->where('batch_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('purchase_date', function ($query, $keyword) {
                $query->where('purchases.purchase_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('total', function ($query, $keyword) {
                $clean = str_replace(['৳', ','], '', $keyword);
                $query->where('purchases.total', 'like', "%{$clean}%");
            })
            ->filterColumn('payment_status', function ($query, $keyword) {
                $query->where('purchases.payment_status', 'like', "%{$keyword}%");
            })
            ->setRowAttr([
                'data-id' => fn (Purchase $purchase) => $purchase->id,
                'data-url' => fn (Purchase $purchase) => route('purchase.show', $purchase),
                'class' => 'clickable-purchase-row',
                'style' => 'cursor:pointer;',
            ])
            ->rawColumns(['supplier', 'invoice_no', 'batch_no', 'items_count', 'total', 'purchase_date', 'payment_status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Purchase>
     */
    public function query(Purchase $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['supplier', 'items.product', 'payments'])
            ->select('purchases.*');

        if ($from = request('from')) {
            $query->whereDate('purchases.purchase_date', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('purchases.purchase_date', '<=', $to);
        }

        if ($status = request('status')) {
            if (in_array($status, ['paid', 'partial', 'due'], true)) {
                $query->where('purchases.payment_status', $status);
            }
        }

        if ($search = request('search.value') ?: request('q')) {
            $searchClean = ltrim($search, '#');
            $query->where(function ($q) use ($search, $searchClean) {
                $q->where('purchases.invoice_no', 'like', "%{$searchClean}%")
                    ->orWhere('purchases.note', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('batch_no', 'like', "%{$search}%")
                            ->orWhereHas('product', function ($pq) use ($search) {
                                $pq->where('name', 'like', "%{$search}%")
                                    ->orWhere('sku', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy([5, 'desc'])
            ->minifiedAjax('', '
                data.from = $("#filter-from").val();
                data.to = $("#filter-to").val();
                data.status = $("#filter-status").val();
            ');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::computed('supplier')
                ->title('<span class="bn">যোগাযোগ</span><span class="en">Contact</span>')
                ->width(180),
            Column::make('invoice_no')
                ->title('<span class="bn">ইনভয়েস নং</span><span class="en">Invoice No</span>')
                ->width(130),
            Column::computed('batch_no')
                ->title('<span class="bn">ব্যাচ নং</span><span class="en">Batch No</span>')
                ->orderable(false)
                ->width(130),
            Column::computed('items_count')
                ->title('<span class="bn">আইটেম</span><span class="en">Item</span>')
                ->orderable(false)
                ->width(100),
            Column::make('total')
                ->title('<span class="bn">টাকার পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::make('purchase_date')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::make('payment_status')
                ->title('<span class="bn">পেমেন্ট অবস্থা</span><span class="en">Payment Status</span>')
                ->addClass('table-cell-center')
                ->width(130),
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
        return 'Purchase_Ledger_'.date('YmdHis');
    }
}
