<?php

namespace Modules\Sales\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Sales\Models\Sale;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class SalesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Sale>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $clone = (clone $query);
        $clone->getQuery()->columns = null;
        $totals = $clone->selectRaw('
            COALESCE(SUM(sales.total), 0) as agg_total,
            COALESCE(SUM(sales.paid_amount), 0) as agg_paid,
            COALESCE(SUM(sales.due_amount), 0) as agg_due,
            COALESCE(COUNT(sales.id), 0) as agg_count
        ')->first();

        return (new EloquentDataTable($query))
            ->with([
                'totalAmount' => number_format((float) ($totals->agg_total ?? 0), 2),
                'totalPaid' => number_format((float) ($totals->agg_paid ?? 0), 2),
                'totalDue' => number_format((float) ($totals->agg_due ?? 0), 2),
                'totalCount' => (int) ($totals->agg_count ?? 0),
            ])
            ->editColumn('customer', function (Sale $sale) {
                $customer = $sale->customer;
                $name = $customer ? e($customer->name) : 'ওয়াক-ইন গ্রাহক';
                $initial = mb_substr($customer->name ?? '?', 0, 1);
                $phone = $customer?->phone
                    ? '<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace);">'.e($customer->phone).'</div>'
                    : '';

                return '<div style="display:flex; align-items:center; gap:8px;">'
                    .'<div style="width:28px; height:28px; border-radius:6px; background:var(--teal-100); color:var(--teal-800); display:inline-flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div><div style="font-weight:700; color:var(--ink-900);">'.$name.'</div>'.$phone.'</div>'
                    .'</div>';
            })
            ->editColumn('invoice_no', function (Sale $sale) {
                $badge = '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); background:var(--paper-line); padding:3px 8px; border-radius:6px; border:1px solid var(--border); font-size:12px;">#'.e($sale->invoice_no).'</span>';

                $warehouse = $sale->warehouse
                    ? '<div style="font-size:11px; color:var(--ink-500); display:flex; align-items:center; gap:4px; margin-top:3px;"><span style="color:var(--ink-400);">•</span> '.e($sale->warehouse->name).'</div>'
                    : '';

                $hasReturns = $sale->returns->isNotEmpty()
                    ? '<div style="margin-top:2px;"><span class="badge b-gold" style="font-size:10px; padding:1px 5px;">ফেরত / Return</span></div>'
                    : '';

                return '<div>'.$badge.$warehouse.$hasReturns.'</div>';
            })
            ->addColumn('batch_no', function (Sale $sale) {
                $batches = $sale->items->pluck('batch.batch_no')->filter()->unique();
                if ($batches->isEmpty()) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12px; color:var(--ink-700);">'.e($batches->implode(', ')).'</span>';
            })
            ->addColumn('items_count', function (Sale $sale) {
                $qty = (float) $sale->items->sum('quantity');
                $qtyFormatted = rtrim(rtrim(number_format($qty, 2), '0'), '.');
                $count = $sale->items->count();

                $hasWarranty = $sale->items->filter(fn ($item) => ! empty($item->warranty_expires_at))->isNotEmpty();
                $warranty = $hasWarranty
                    ? '<span title="ওয়ারেন্টি অন্তর্ভুক্ত / Warranty Included" style="color:var(--teal-700); margin-left:3px; display:inline-flex; vertical-align:middle;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>'
                    : '';

                return '<div>'
                    .'<span style="font-family:var(--font-mono, monospace); color:var(--ink-700); font-weight:600;">'.$qtyFormatted.'</span>'
                    .$warranty
                    .'<div style="font-size:11px; color:var(--ink-500); margin-top:2px;">('.$count.' টি পণ্য)</div>'
                    .'</div>';
            })
            ->editColumn('total', function (Sale $sale) {
                $total = '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">৳'.number_format((float) $sale->total, 2).'</span>';

                $discount = (float) $sale->discount > 0
                    ? '<div style="font-size:11px; color:var(--green-ink); font-family:var(--font-mono, monospace); margin-top:1px;">(ছাড়: ৳'.number_format((float) $sale->discount, 2).')</div>'
                    : '';

                return '<div>'.$total.$discount.'</div>';
            })
            ->editColumn('paid_amount', function (Sale $sale) {
                $paid = '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--teal-800);">৳'.number_format((float) $sale->paid_amount, 2).'</span>';

                $methods = $sale->payments->map(function ($p) {
                    return $p->methodLabel()['bn'] ?? $p->method;
                })->filter()->unique()->implode(', ');

                $methodsHtml = $methods
                    ? '<div style="font-size:10.5px; color:var(--ink-500); margin-top:1px; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($methods).'">'.e($methods).'</div>'
                    : '';

                return '<div>'.$paid.$methodsHtml.'</div>';
            })
            ->editColumn('due_amount', function (Sale $sale) {
                $due = (float) $sale->due_amount;
                if ($due > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600);">৳'.number_format($due, 2).'</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-400);">৳0.00</span>';
            })
            ->editColumn('sale_date', function (Sale $sale) {
                if (! $sale->sale_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $date = '<span style="font-size:12.5px; color:var(--ink-700); white-space:nowrap;">'.e($sale->sale_date->format('d M, Y')).'</span>';
                $time = '<div style="font-size:11px; color:var(--ink-400); font-family:var(--font-mono, monospace);">'.e($sale->created_at->format('h:i A')).'</div>';

                $employee = $sale->employee_name
                    ? '<div style="font-size:10.5px; color:var(--ink-500); margin-top:1px; max-width:110px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="বিক্রেতা: '.e($sale->employee_name).'">'.e($sale->employee_name).'</div>'
                    : '';

                return '<div>'.$date.$time.$employee.'</div>';
            })
            ->editColumn('payment_status', function (Sale $sale) {
                if ($sale->payment_status === 'paid') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="পরিশোধিত" label-en="Paid" />');
                } elseif ($sale->payment_status === 'partial') {
                    return Blade::render('<x-core::badge color="gold" size="xs" :dot="true" label="আংশিক" label-en="Partial" />');
                }

                return Blade::render('<x-core::badge color="red" size="xs" :dot="true" label="বাকি" label-en="Due" />');
            })
            ->addColumn('action', function (Sale $sale) {
                return view('sales::sales.datatables-actions', compact('sale'))->render();
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('customer', function ($cq) use ($keyword) {
                        $cq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    });
                    if (str_contains(mb_strtolower('ওয়াক-ইন গ্রাহক walk-in'), mb_strtolower($keyword))) {
                        $q->orWhereNull('sales.customer_id');
                    }
                });
            })
            ->filterColumn('invoice_no', function ($query, $keyword) {
                $clean = ltrim($keyword, '#');
                $query->where('sales.invoice_no', 'like', "%{$clean}%");
            })
            ->filterColumn('batch_no', function ($query, $keyword) {
                $query->whereHas('items.batch', function ($q) use ($keyword) {
                    $q->where('batch_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('total', function ($query, $keyword) {
                $clean = str_replace(['৳', ',', ' '], '', $keyword);
                $query->where('sales.total', 'like', "%{$clean}%");
            })
            ->filterColumn('paid_amount', function ($query, $keyword) {
                $clean = str_replace(['৳', ',', ' '], '', $keyword);
                $query->where('sales.paid_amount', 'like', "%{$clean}%");
            })
            ->filterColumn('due_amount', function ($query, $keyword) {
                $clean = str_replace(['৳', ',', ' '], '', $keyword);
                $query->where('sales.due_amount', 'like', "%{$clean}%");
            })
            ->filterColumn('sale_date', function ($query, $keyword) {
                $query->where('sales.sale_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('payment_status', function ($query, $keyword) {
                $query->where('sales.payment_status', 'like', "%{$keyword}%");
            })
            ->setRowAttr([
                'data-id' => fn (Sale $sale) => $sale->id,
                'data-url' => fn (Sale $sale) => route('sales.show', $sale),
                'class' => 'clickable-sale-row',
                'style' => 'cursor:pointer;',
            ])
            ->rawColumns(['customer', 'invoice_no', 'batch_no', 'items_count', 'total', 'paid_amount', 'due_amount', 'sale_date', 'payment_status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Sale>
     */
    public function query(Sale $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['customer', 'warehouse', 'items.product', 'items.batch', 'items.unit', 'payments', 'returns'])
            ->select('sales.*');

        if ($from = request('from')) {
            $query->whereDate('sales.sale_date', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('sales.sale_date', '<=', $to);
        }

        if ($status = request('status')) {
            if (in_array($status, ['paid', 'partial', 'due'], true)) {
                $query->where('sales.payment_status', $status);
            }
        }

        if ($search = request('search.value') ?: request('q')) {
            $searchClean = ltrim($search, '#');
            $query->where(function ($q) use ($search, $searchClean) {
                $q->where('sales.invoice_no', 'like', "%{$searchClean}%")
                    ->orWhere('sales.note', 'like', "%{$search}%")
                    ->orWhere('sales.employee_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->whereHas('batch', function ($bq) use ($search) {
                            $bq->where('batch_no', 'like', "%{$search}%");
                        })->orWhereHas('product', function ($pq) use ($search) {
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
            ->orderBy([7, 'desc'])
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
            Column::computed('customer')
                ->title('<span class="bn">যোগাযোগ</span><span class="en">Contact</span>')
                ->width(170),
            Column::make('invoice_no')
                ->title('<span class="bn">ইনভয়েস নং</span><span class="en">Invoice No</span>')
                ->width(120),
            Column::computed('batch_no')
                ->title('<span class="bn">ব্যাচ নং</span><span class="en">Batch No</span>')
                ->orderable(false)
                ->width(110),
            Column::computed('items_count')
                ->title('<span class="bn">আইটেম</span><span class="en">Item</span>')
                ->orderable(false)
                ->width(100),
            Column::make('total')
                ->title('<span class="bn">টাকার পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::make('paid_amount')
                ->title('<span class="bn">পরিশোধিত</span><span class="en">Paid</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::make('due_amount')
                ->title('<span class="bn">বাকি</span><span class="en">Due</span>')
                ->addClass('table-cell-right')
                ->width(100),
            Column::make('sale_date')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->addClass('table-cell-center')
                ->width(110),
            Column::make('payment_status')
                ->title('<span class="bn">পেমেন্ট অবস্থা</span><span class="en">Payment Status</span>')
                ->addClass('table-cell-center')
                ->width(110),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(130)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Sales_Ledger_'.date('YmdHis');
    }
}
