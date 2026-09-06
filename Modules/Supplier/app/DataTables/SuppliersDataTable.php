<?php

namespace Modules\Supplier\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Supplier\Models\Supplier;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class SuppliersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Supplier>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Supplier $supplier) {
                $initial = mb_substr($supplier->name, 0, 1);
                $code = '<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">#SUP-'.str_pad((string) $supplier->id, 4, '0', STR_PAD_LEFT).'</div>';

                return '<div class="row-avatar" style="display:flex; align-items:flex-start; gap:10px;">'
                    .'<div class="av" style="width:34px; height:34px; border-radius:8px; background:var(--gold-600); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13.5px; flex-shrink:0; margin-top:2px;">'.e($initial).'</div>'
                    .'<div style="min-width:0;">'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.3;">'.e($supplier->name).'</div>'
                    .$code
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('contact', function (Supplier $supplier) {
                $phone = $supplier->phone
                    ? '<div style="font-family:var(--font-mono, monospace); font-size:12px; font-weight:600; color:var(--ink-800); display:flex; align-items:center; gap:5px;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
                        .e($supplier->phone)
                        .'</div>'
                    : '';

                $email = $supplier->email
                    ? '<div style="font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:5px; margin-top:2px;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>'
                        .e($supplier->email)
                        .'</div>'
                    : '';

                if (! $phone && ! $email) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div>'.$phone.$email.'</div>';
            })
            ->editColumn('address', function (Supplier $supplier) {
                if (! $supplier->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12px; color:var(--ink-700); display:flex; align-items:flex-start; gap:4px; max-width:180px; line-height:1.35;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0; margin-top:2px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
                    .'<span>'.e($supplier->address).'</span>'
                    .'</div>';
            })
            ->addColumn('purchase_summary', function (Supplier $supplier) {
                $totalCount = (int) ($supplier->purchases_count ?? 0);
                $dueCount = (int) ($supplier->due_purchases_count ?? 0);
                $totalAmount = (float) ($supplier->purchases_sum_total ?? 0);

                if ($totalCount === 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--paper-line); color:var(--ink-500); margin-left:4px;">নতুন</span>';
                } elseif ($dueCount > 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600); margin-left:4px;">'.$dueCount.' বাকি</span>';
                } else {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); margin-left:4px;">বিল পরিশোধিত</span>';
                }

                return '<div style="font-size:12.5px;">'
                    .'<div style="font-weight:600; color:var(--ink-800); display:flex; align-items:center;">'
                    .'<span>'.$totalCount.' টি ক্রয়</span>'
                    .$dueBadge
                    .'</div>'
                    .'<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace); margin-top:2px;">'
                    .'মোট: ৳'.number_format($totalAmount, 2)
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('due_breakdown', function (Supplier $supplier) {
                $openingDue = (float) $supplier->opening_due;
                $purchaseDue = (float) ($supplier->purchases_sum_due_amount ?? 0);
                $totalDue = $openingDue + $purchaseDue;

                if ($totalDue > 0) {
                    $badge = '<div style="display:inline-flex; align-items:center; background:var(--red-100); border-radius:6px; padding:2px 8px;">'
                        .'<span style="font-family:var(--font-mono, monospace); font-weight:800; font-size:13px; color:var(--red-600);">৳'.number_format($totalDue, 2).'</span>'
                        .'</div>';

                    $subDetails = [];
                    if ($purchaseDue > 0) {
                        $subDetails[] = 'বিল: ৳'.number_format($purchaseDue, 0);
                    }
                    if ($openingDue > 0) {
                        $subDetails[] = 'ওপেনিং: ৳'.number_format($openingDue, 0);
                    }

                    $subHtml = ! empty($subDetails)
                        ? '<div style="font-size:11px; color:var(--ink-500); margin-top:3px;">'.implode(' | ', $subDetails).'</div>'
                        : '';

                    return '<div style="text-align:right;">'.$badge.$subHtml.'</div>';
                }

                return '<div style="text-align:right;"><span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--green-ink); font-size:12px;">৳0.00</span></div>';
            })
            ->addColumn('total_due', function (Supplier $supplier) {
                $due = (float) $supplier->opening_due + (float) ($supplier->purchases_sum_due_amount ?? 0);

                return '৳'.number_format($due, 2);
            })
            ->addColumn('last_purchase', function (Supplier $supplier) {
                $lastPurchase = $supplier->purchases->first();
                if (! $lastPurchase) {
                    return '<span style="color:var(--ink-400); font-size:11.5px;">কোনো লেনদেন নেই</span>';
                }

                $inv = e($lastPurchase->invoice_no);
                $date = optional($lastPurchase->purchase_date)->format('d M, Y') ?? '—';
                $amount = '৳'.number_format((float) $lastPurchase->total, 2);

                return '<div style="font-size:12px;">'
                    .'<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800);">#'.$inv.'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-top:1px;">'.$date.' ('.$amount.')</div>'
                    .'</div>';
            })
            ->editColumn('status', function (Supplier $supplier) {
                if ($supplier->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Supplier $supplier) {
                return view('supplier::datatables-actions', compact('supplier'))->render();
            })
            ->filter(function ($query) {
                if ($keyword = request('search.value')) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('suppliers.name', 'like', "%{$keyword}%")
                            ->orWhere('suppliers.phone', 'like', "%{$keyword}%")
                            ->orWhere('suppliers.email', 'like', "%{$keyword}%")
                            ->orWhere('suppliers.address', 'like', "%{$keyword}%");
                    });
                }
            }, true)
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('suppliers.name', $order);
            })
            ->orderColumn('contact', function ($query, $order) {
                $query->orderBy('suppliers.phone', $order);
            })
            ->orderColumn('address', function ($query, $order) {
                $query->orderBy('suppliers.address', $order);
            })
            ->orderColumn('purchase_summary', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT SUM(total) FROM purchases WHERE purchases.supplier_id = suppliers.id), 0) '.$order);
            })
            ->orderColumn('due_breakdown', function ($query, $order) {
                $query->orderByRaw('(suppliers.opening_due + COALESCE((SELECT SUM(due_amount) FROM purchases WHERE purchases.supplier_id = suppliers.id), 0)) '.$order);
            })
            ->orderColumn('last_purchase', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT MAX(purchase_date) FROM purchases WHERE purchases.supplier_id = suppliers.id), "1970-01-01") '.$order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('suppliers.status', $order);
            })
            ->rawColumns(['name', 'contact', 'address', 'purchase_summary', 'due_breakdown', 'last_purchase', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Supplier>
     */
    public function query(Supplier $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->withSum('purchases', 'total')
            ->withSum('purchases', 'due_amount')
            ->withCount('purchases')
            ->withCount(['purchases as due_purchases_count' => fn ($q) => $q->where('due_amount', '>', 0)])
            ->with(['purchases' => fn ($q) => $q->latest('purchase_date')])
            ->select([
                'suppliers.id',
                'suppliers.shop_id',
                'suppliers.name',
                'suppliers.phone',
                'suppliers.email',
                'suppliers.address',
                'suppliers.opening_due',
                'suppliers.status',
                'suppliers.created_at',
            ]);

        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->where('suppliers.status', $status);
            }
        }

        if (request('due_status') === 'has_due') {
            $query->where(function ($q) {
                $q->where('suppliers.opening_due', '>', 0)
                    ->orWhereHas('purchases', fn ($sq) => $sq->where('due_amount', '>', 0));
            });
        } elseif (request('due_status') === 'no_due') {
            $query->where(function ($q) {
                $q->where('suppliers.opening_due', '<=', 0)
                    ->whereDoesntHave('purchases', fn ($sq) => $sq->where('due_amount', '>', 0));
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
            ->setTableId('suppliers-data-table')
            ->minifiedAjax('', 'data.status = $("#filter-status").val(); data.due_status = $("#filter-due").val();');
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
                ->title('<span class="bn">সরবরাহকারী</span><span class="en">Supplier</span>')
                ->width(190),
            Column::computed('contact')
                ->title('<span class="bn">যোগাযোগ</span><span class="en">Contact</span>')
                ->width(160),
            Column::make('address')
                ->title('<span class="bn">ঠিকানা</span><span class="en">Address</span>')
                ->width(160),
            Column::computed('purchase_summary')
                ->title('<span class="bn">ক্রয় তথ্য</span><span class="en">Purchase Summary</span>')
                ->width(150),
            Column::computed('due_breakdown')
                ->title('<span class="bn">প্রদেয় দেনা</span><span class="en">Payable Due</span>')
                ->addClass('table-cell-right')
                ->width(140),
            Column::computed('last_purchase')
                ->title('<span class="bn">সর্বশেষ লেনদেন</span><span class="en">Last Purchase</span>')
                ->width(150),
            Column::make('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(90),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(170)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Suppliers_'.date('YmdHis');
    }
}
