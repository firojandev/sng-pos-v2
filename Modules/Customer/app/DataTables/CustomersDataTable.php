<?php

namespace Modules\Customer\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Customer\Models\Customer;
use Modules\Sales\Models\Sale;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CustomersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Customer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Customer $customer) {
                $initial = mb_substr($customer->name, 0, 1);
                $code = '<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">#CUS-'.str_pad((string) $customer->id, 4, '0', STR_PAD_LEFT).'</div>';

                return '<div class="row-avatar" style="display:flex; align-items:flex-start; gap:10px;">'
                    .'<div class="av" style="width:34px; height:34px; border-radius:8px; background:var(--teal-700); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13.5px; flex-shrink:0; margin-top:2px;">'.e($initial).'</div>'
                    .'<div style="min-width:0; max-width:200px;">'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word;" title="'.e($customer->name).'">'.e($customer->name).'</div>'
                    .$code
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('contact', function (Customer $customer) {
                $phone = $customer->phone
                    ? '<div style="font-family:var(--font-mono, monospace); font-size:12px; font-weight:600; color:var(--ink-800); display:flex; align-items:center; gap:5px; white-space:nowrap;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
                        .'<span>'.e($customer->phone).'</span>'
                        .'</div>'
                    : '';

                $email = $customer->email
                    ? '<div style="font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:5px; margin-top:2px; max-width:160px;">'
                        .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>'
                        .'<span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($customer->email).'">'.e($customer->email).'</span>'
                        .'</div>'
                    : '';

                if (! $phone && ! $email) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="min-width:0;">'.$phone.$email.'</div>';
            })
            ->editColumn('address', function (Customer $customer) {
                if (! $customer->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12px; color:var(--ink-700); display:flex; align-items:flex-start; gap:5px; max-width:200px; line-height:1.4;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0; margin-top:2px;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
                    .'<span style="min-width:0; flex:1; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word;" title="'.e($customer->address).'">'
                    .e($customer->address)
                    .'</span>'
                    .'</div>';
            })
            ->addColumn('sales_summary', function (Customer $customer) {
                $totalCount = (int) ($customer->sales_count ?? 0);
                $dueCount = (int) ($customer->due_sales_count ?? 0);
                $totalAmount = (float) ($customer->sales_sum_total ?? 0);

                if ($totalCount === 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--paper-line); color:var(--ink-500); margin-left:4px; white-space:nowrap;">নতুন</span>';
                } elseif ($dueCount > 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600); margin-left:4px; white-space:nowrap;">'.$dueCount.' বাকি</span>';
                } else {
                    $dueBadge = '<span style="display:inline-block; font-size:10.5px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); margin-left:4px; white-space:nowrap;">পরিশোধিত</span>';
                }

                return '<div style="font-size:12.5px; white-space:nowrap;">'
                    .'<div style="font-weight:600; color:var(--ink-800); display:flex; align-items:center;">'
                    .'<span>'.$totalCount.' টি বিক্রয়</span>'
                    .$dueBadge
                    .'</div>'
                    .'<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace); margin-top:2px;">'
                    .'মোট: ৳'.number_format($totalAmount, 2)
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('due_breakdown', function (Customer $customer) {
                $openingDue = (float) $customer->opening_due;
                $salesDue = (float) ($customer->sales_sum_due_amount ?? 0);
                $totalDue = $openingDue + $salesDue;

                if ($totalDue > 0) {
                    $badge = '<div style="display:inline-flex; align-items:center; background:var(--red-100); border-radius:6px; padding:2px 8px; white-space:nowrap;">'
                        .'<span style="font-family:var(--font-mono, monospace); font-weight:800; font-size:13px; color:var(--red-600);">৳'.number_format($totalDue, 2).'</span>'
                        .'</div>';

                    $subDetails = [];
                    if ($salesDue > 0) {
                        $subDetails[] = 'চালান: ৳'.number_format($salesDue, 0);
                    }
                    if ($openingDue > 0) {
                        $subDetails[] = 'ওপেনিং: ৳'.number_format($openingDue, 0);
                    }

                    $subHtml = ! empty($subDetails)
                        ? '<div style="font-size:11px; color:var(--ink-500); margin-top:3px; white-space:nowrap;">'.implode(' | ', $subDetails).'</div>'
                        : '';

                    return '<div style="text-align:right; white-space:nowrap;">'.$badge.$subHtml.'</div>';
                }

                return '<div style="text-align:right; white-space:nowrap;"><span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--green-ink); font-size:12px;">৳0.00</span></div>';
            })
            ->addColumn('total_due', function (Customer $customer) {
                $due = (float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0);

                return '৳'.number_format($due, 2);
            })
            ->addColumn('last_sale', function (Customer $customer) {
                $lastSale = $customer->sales->first();
                if (! $lastSale) {
                    return '<span style="color:var(--ink-400); font-size:11.5px; white-space:nowrap;">কোনো লেনদেন নেই</span>';
                }

                $inv = e($lastSale->invoice_no);
                $date = optional($lastSale->sale_date)->format('d M, Y') ?? '—';
                $amount = '৳'.number_format((float) $lastSale->total, 2);

                return '<div style="font-size:12px; white-space:nowrap;">'
                    .'<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800);">#'.$inv.'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-top:1px;">'.$date.' ('.$amount.')</div>'
                    .'</div>';
            })
            ->editColumn('status', function (Customer $customer) {
                if ($customer->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Customer $customer) {
                return view('customer::datatables-actions', compact('customer'))->render();
            })
            ->filter(function ($query) {
                if ($keyword = request('search.value')) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('customers.name', 'like', "%{$keyword}%")
                            ->orWhere('customers.phone', 'like', "%{$keyword}%")
                            ->orWhere('customers.email', 'like', "%{$keyword}%")
                            ->orWhere('customers.address', 'like', "%{$keyword}%");
                    });
                }
            }, true)
            ->orderColumn('name', function ($query, $order) {
                $query->orderBy('customers.name', $order);
            })
            ->orderColumn('contact', function ($query, $order) {
                $query->orderBy('customers.phone', $order);
            })
            ->orderColumn('address', function ($query, $order) {
                $query->orderBy('customers.address', $order);
            })
            ->orderColumn('sales_summary', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT SUM(total) FROM sales WHERE sales.customer_id = customers.id), 0) '.$order);
            })
            ->orderColumn('due_breakdown', function ($query, $order) {
                $query->orderByRaw('(customers.opening_due + COALESCE((SELECT SUM(due_amount) FROM sales WHERE sales.customer_id = customers.id), 0)) '.$order);
            })
            ->orderColumn('last_sale', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT MAX(sale_date) FROM sales WHERE sales.customer_id = customers.id), "1970-01-01") '.$order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('customers.status', $order);
            })
            ->rawColumns(['name', 'contact', 'address', 'sales_summary', 'due_breakdown', 'last_sale', 'status', 'action'])
            ->with('metrics', function () {
                $openingDueSum = (float) Customer::sum('opening_due');
                $salesDueSum = (float) Sale::whereNotNull('customer_id')->sum('due_amount');
                $totalSalesAmount = round((float) Sale::whereNotNull('customer_id')->sum('total'), 2);
                $totalSalesCount = Sale::whereNotNull('customer_id')->count();

                return [
                    'totalCustomers' => Customer::count(),
                    'activeCustomers' => Customer::where('status', 'active')->count(),
                    'totalDue' => round($openingDueSum + $salesDueSum, 2),
                    'dueCustomersCount' => Customer::where(function ($q) {
                        $q->where('opening_due', '>', 0)
                            ->orWhereHas('sales', fn ($sq) => $sq->where('due_amount', '>', 0));
                    })->count(),
                    'totalSalesAmount' => $totalSalesAmount,
                    'totalSalesCount' => $totalSalesCount,
                    'paidTotal' => max(0, round($totalSalesAmount - $salesDueSum, 2)),
                ];
            })
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->select([
                'customers.id',
                'customers.shop_id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.address',
                'customers.opening_due',
                'customers.status',
                'customers.created_at',
            ])
            ->withSum('sales', 'total')
            ->withSum('sales', 'due_amount')
            ->withCount('sales')
            ->withCount(['sales as due_sales_count' => fn ($q) => $q->where('due_amount', '>', 0)])
            ->with(['sales' => fn ($q) => $q->latest('sale_date')]);

        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->where('customers.status', $status);
            }
        }

        if (request('due_status') === 'has_due') {
            $query->where(function ($q) {
                $q->where('customers.opening_due', '>', 0)
                    ->orWhereHas('sales', fn ($sq) => $sq->where('due_amount', '>', 0));
            });
        } elseif (request('due_status') === 'no_due') {
            $query->where(function ($q) {
                $q->where('customers.opening_due', '<=', 0)
                    ->whereDoesntHave('sales', fn ($sq) => $sq->where('due_amount', '>', 0));
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
            ->setTableId('customers-data-table')
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
                ->title('<span class="bn">গ্রাহক</span><span class="en">Customer</span>')
                ->width(190),
            Column::computed('contact')
                ->title('<span class="bn">যোগাযোগ</span><span class="en">Contact</span>')
                ->width(160),
            Column::make('address')
                ->title('<span class="bn">ঠিকানা</span><span class="en">Address</span>')
                ->width(180),
            Column::computed('sales_summary')
                ->title('<span class="bn">বিক্রয় তথ্য</span><span class="en">Sales Summary</span>')
                ->width(150),
            Column::computed('due_breakdown')
                ->title('<span class="bn">বাকি ও বকেয়া</span><span class="en">Due Balance</span>')
                ->addClass('table-cell-right')
                ->width(140),
            Column::computed('last_sale')
                ->title('<span class="bn">সর্বশেষ লেনদেন</span><span class="en">Last Sale</span>')
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
        return 'Customers_'.date('YmdHis');
    }
}
