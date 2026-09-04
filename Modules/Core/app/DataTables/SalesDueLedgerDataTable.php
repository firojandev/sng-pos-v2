<?php

namespace Modules\Core\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Customer\Models\Customer;
use Modules\Sales\Models\Sale;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class SalesDueLedgerDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Customer>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $customerIds = (clone $query)->pluck('customers.id');
        $openingDueSum = (float) Customer::whereIn('id', $customerIds)->sum('opening_due');
        $salesDueSum = (float) Sale::whereIn('customer_id', $customerIds)->sum('due_amount');
        $totalDueSum = round($openingDueSum + $salesDueSum, 2);

        return (new EloquentDataTable($query))
            ->with([
                'totalDue' => number_format($totalDueSum, 2),
                'totalCount' => count($customerIds),
            ])
            ->editColumn('name', function (Customer $customer) {
                $initial = mb_substr($customer->name, 0, 1);
                $phone = $customer->phone
                    ? '<div style="font-size:12px; font-family:var(--font-mono, monospace); color:var(--ink-700); display:flex; align-items:center; gap:4px; margin-top:2px;">'
                        .'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
                        .e($customer->phone)
                        .'</div>'
                    : '';
                $address = $customer->address
                    ? '<div style="font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:4px; margin-top:2px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">'
                        .'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
                        .'<span style="overflow:hidden; text-overflow:ellipsis;">'.e($customer->address).'</span>'
                        .'</div>'
                    : '';

                return '<div class="row-avatar" style="display:flex; align-items:flex-start; gap:10px;">'
                    .'<div class="av" style="width:34px; height:34px; border-radius:8px; background:var(--teal-700); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; margin-top:2px;">'.e($initial).'</div>'
                    .'<div style="min-width:0;">'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.3;">'.e($customer->name).'</div>'
                    .$phone
                    .$address
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('sales_summary', function (Customer $customer) {
                $totalCount = (int) ($customer->sales_count ?? 0);
                $dueCount = (int) ($customer->due_sales_count ?? 0);
                $totalAmount = (float) ($customer->sales_sum_total ?? 0);

                if ($totalCount === 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--paper-line); color:var(--ink-500); margin-left:4px;">কোনো বিক্রয় নেই</span>';
                } elseif ($dueCount > 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600); margin-left:4px;">'.$dueCount.' বাকি</span>';
                } else {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); margin-left:4px;">চালান পরিশোধিত</span>';
                }

                return '<div style="font-size:12.5px;">'
                    .'<div style="font-weight:600; color:var(--ink-800); display:flex; align-items:center;">'
                    .'<span>'.$totalCount.' টি বিক্রয়</span>'
                    .$dueBadge
                    .'</div>'
                    .'<div style="font-size:11.5px; color:var(--ink-500); font-family:var(--font-mono, monospace); margin-top:2px;">'
                    .'মোট: ৳'.number_format($totalAmount, 2)
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('opening_due', function (Customer $customer) {
                $opening = (float) $customer->opening_due;
                if ($opening > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-700);">৳'.number_format($opening, 2).'</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-400);">৳0.00</span>';
            })
            ->addColumn('sales_due', function (Customer $customer) {
                $salesDue = (float) ($customer->sales_sum_due_amount ?? 0);
                if ($salesDue > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--gold-ink, #b45309);">৳'.number_format($salesDue, 2).'</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-400);">৳0.00</span>';
            })
            ->addColumn('total_due', function (Customer $customer) {
                $total = (float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0);

                return '<div style="display:inline-flex; align-items:center; background:var(--red-100); border-radius:6px; padding:3px 8px;">'
                    .'<span style="font-family:var(--font-mono, monospace); font-weight:800; font-size:13.5px; color:var(--red-600);">৳'.number_format($total, 2).'</span>'
                    .'</div>';
            })
            ->addColumn('last_sale', function (Customer $customer) {
                $lastSale = $customer->sales->first();
                if (! $lastSale) {
                    return '<span style="color:var(--ink-400); font-size:11.5px;">শুধু ওপেনিং বাকি</span>';
                }

                $date = optional($lastSale->sale_date)->format('d M, Y') ?? '—';
                $inv = e($lastSale->invoice_no);

                return '<div style="font-size:12px;">'
                    .'<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800);">#'.$inv.'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-top:1px;">'.$date.'</div>'
                    .'</div>';
            })
            ->editColumn('status', function (Customer $customer) {
                if ($customer->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Customer $customer) {
                $detailUrl = route('due-ledger.customer.details', $customer);
                $paymentModalUrl = route('due-ledger.customer.payment-modal', $customer);
                $newSaleUrl = route('sales.create').'?customer_id='.$customer->id;

                return '<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">'
                    .'<button type="button" class="btn btn-soft-green btn-xs btn-open-customer-payment" data-url="'.e($paymentModalUrl).'" title="বাকি আদায় / Collect Due">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'
                    .'<span class="bn">জমা</span><span class="en" style="display:none;">Collect</span>'
                    .'</button>'
                    .'<button type="button" class="btn btn-soft-dark btn-xs btn-view-customer-due" data-url="'.e($detailUrl).'" title="বাকির বিস্তারিত / View Due Details">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>'
                    .'<span class="bn">বিস্তারিত</span><span class="en" style="display:none;">Details</span>'
                    .'</button>'
                    .'<a href="'.e($newSaleUrl).'" class="btn btn-soft-teal btn-xs" title="নতুন বিক্রয় / New Sale">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M12 5v14M5 12h14"/></svg>'
                    .'<span class="bn">বেচা</span><span class="en" style="display:none;">Sell</span>'
                    .'</a>'
                    .'</div>';
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
            ->orderColumn('opening_due', function ($query, $order) {
                $query->orderBy('customers.opening_due', $order);
            })
            ->orderColumn('sales_due', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT SUM(due_amount) FROM sales WHERE sales.customer_id = customers.id), 0) '.$order);
            })
            ->orderColumn('total_due', function ($query, $order) {
                $query->orderByRaw('(customers.opening_due + COALESCE((SELECT SUM(due_amount) FROM sales WHERE sales.customer_id = customers.id), 0)) '.$order);
            })
            ->setRowId('id')
            ->setRowAttr([
                'class' => 'clickable-customer-row',
                'data-url' => fn (Customer $customer) => route('due-ledger.customer.details', $customer),
                'style' => 'cursor:pointer;',
            ])
            ->rawColumns(['name', 'sales_summary', 'opening_due', 'sales_due', 'total_due', 'last_sale', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()
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
            ->with(['sales' => fn ($q) => $q->latest('sale_date')])
            ->where(function ($q) {
                $q->where('customers.opening_due', '>', 0)
                    ->orWhereHas('sales', fn ($sq) => $sq->where('due_amount', '>', 0));
            });
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('sales-due-data-table');
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
                ->width(220),
            Column::computed('sales_summary')
                ->title('<span class="bn">বিক্রয় সারসংক্ষেপ</span><span class="en">Sales Summary</span>')
                ->width(150),
            Column::make('opening_due')
                ->title('<span class="bn">ওপেনিং বাকি</span><span class="en">Opening Due</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::computed('sales_due')
                ->title('<span class="bn">চালান বাকি</span><span class="en">Invoice Due</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::computed('total_due')
                ->title('<span class="bn">মোট বাকি</span><span class="en">Total Due</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::computed('last_sale')
                ->title('<span class="bn">সর্বশেষ লেনদেন</span><span class="en">Last Transaction</span>')
                ->width(140),
            Column::make('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(80),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(180)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'SalesDueLedger_'.date('YmdHis');
    }
}
