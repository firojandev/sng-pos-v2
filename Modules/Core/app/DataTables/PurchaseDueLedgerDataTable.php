<?php

namespace Modules\Core\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Purchase\Models\Purchase;
use Modules\Supplier\Models\Supplier;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class PurchaseDueLedgerDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Supplier>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $supplierIds = (clone $query)->pluck('suppliers.id');
        $openingDueSum = (float) Supplier::whereIn('id', $supplierIds)->sum('opening_due');
        $purchaseDueSum = (float) Purchase::whereIn('supplier_id', $supplierIds)->sum('due_amount');
        $totalDueSum = round($openingDueSum + $purchaseDueSum, 2);

        return (new EloquentDataTable($query))
            ->with([
                'totalDue' => number_format($totalDueSum, 2),
                'totalCount' => count($supplierIds),
            ])
            ->editColumn('name', function (Supplier $supplier) {
                $initial = mb_substr($supplier->name, 0, 1);
                $phone = $supplier->phone
                    ? '<div style="font-size:12px; font-family:var(--font-mono, monospace); color:var(--ink-700); display:flex; align-items:center; gap:4px; margin-top:2px;">'
                        .'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'
                        .e($supplier->phone)
                        .'</div>'
                    : '';
                $address = $supplier->address
                    ? '<div style="font-size:11.5px; color:var(--ink-500); display:flex; align-items:center; gap:4px; margin-top:2px; max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">'
                        .'<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6; flex-shrink:0;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>'
                        .'<span style="overflow:hidden; text-overflow:ellipsis;">'.e($supplier->address).'</span>'
                        .'</div>'
                    : '';

                return '<div class="row-avatar" style="display:flex; align-items:flex-start; gap:10px;">'
                    .'<div class="av" style="width:34px; height:34px; border-radius:8px; background:var(--gold-600); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; margin-top:2px;">'.e($initial).'</div>'
                    .'<div style="min-width:0;">'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.3;">'.e($supplier->name).'</div>'
                    .$phone
                    .$address
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('purchase_summary', function (Supplier $supplier) {
                $totalCount = (int) ($supplier->purchases_count ?? 0);
                $dueCount = (int) ($supplier->due_purchases_count ?? 0);
                $totalAmount = (float) ($supplier->purchases_sum_total ?? 0);

                if ($totalCount === 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--paper-line); color:var(--ink-500); margin-left:4px;">কোনো ক্রয় নেই</span>';
                } elseif ($dueCount > 0) {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:700; padding:1px 6px; border-radius:4px; background:var(--red-100); color:var(--red-600); margin-left:4px;">'.$dueCount.' বাকি</span>';
                } else {
                    $dueBadge = '<span style="display:inline-block; font-size:11px; font-weight:600; padding:1px 6px; border-radius:4px; background:var(--blue-100); color:var(--blue-ink); margin-left:4px;">বিল পরিশোধিত</span>';
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
            ->editColumn('opening_due', function (Supplier $supplier) {
                $opening = (float) $supplier->opening_due;
                if ($opening > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-700);">৳'.number_format($opening, 2).'</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-400);">৳0.00</span>';
            })
            ->addColumn('purchase_due', function (Supplier $supplier) {
                $purchaseDue = (float) ($supplier->purchases_sum_due_amount ?? 0);
                if ($purchaseDue > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--gold-ink, #b45309);">৳'.number_format($purchaseDue, 2).'</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); color:var(--ink-400);">৳0.00</span>';
            })
            ->addColumn('total_due', function (Supplier $supplier) {
                $total = (float) $supplier->opening_due + (float) ($supplier->purchases_sum_due_amount ?? 0);

                return '<div style="display:inline-flex; align-items:center; background:var(--red-100); border-radius:6px; padding:3px 8px;">'
                    .'<span style="font-family:var(--font-mono, monospace); font-weight:800; font-size:13.5px; color:var(--red-600);">৳'.number_format($total, 2).'</span>'
                    .'</div>';
            })
            ->addColumn('last_purchase', function (Supplier $supplier) {
                $lastPurchase = $supplier->purchases->first();
                if (! $lastPurchase) {
                    return '<span style="color:var(--ink-400); font-size:11.5px;">শুধু ওপেনিং বাকি</span>';
                }

                $date = optional($lastPurchase->purchase_date)->format('d M, Y') ?? '—';
                $inv = e($lastPurchase->invoice_no);

                return '<div style="font-size:12px;">'
                    .'<div style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800);">#'.$inv.'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-top:1px;">'.$date.'</div>'
                    .'</div>';
            })
            ->editColumn('status', function (Supplier $supplier) {
                if ($supplier->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Supplier $supplier) {
                $detailUrl = route('due-ledger.supplier.details', $supplier);
                $paymentModalUrl = route('due-ledger.supplier.payment-modal', $supplier);
                $newPurchaseUrl = route('purchase.create').'?supplier_id='.$supplier->id;

                return '<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">'
                    .'<button type="button" class="btn btn-soft-green btn-xs btn-open-supplier-payment" data-url="'.e($paymentModalUrl).'" title="বাকি পরিশোধ / Pay Due">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>'
                    .'<span class="bn">পরিশোধ</span><span class="en" style="display:none;">Pay</span>'
                    .'</button>'
                    .'<button type="button" class="btn btn-soft-dark btn-xs btn-view-supplier-due" data-url="'.e($detailUrl).'" title="বাকির বিস্তারিত / View Due Details">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>'
                    .'<span class="bn">বিস্তারিত</span><span class="en" style="display:none;">Details</span>'
                    .'</button>'
                    .'<a href="'.e($newPurchaseUrl).'" class="btn btn-soft-teal btn-xs" title="নতুন ক্রয় / New Purchase">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M12 5v14M5 12h14"/></svg>'
                    .'<span class="bn">ক্রয়</span><span class="en" style="display:none;">Buy</span>'
                    .'</a>'
                    .'</div>';
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
            ->orderColumn('opening_due', function ($query, $order) {
                $query->orderBy('suppliers.opening_due', $order);
            })
            ->orderColumn('purchase_due', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT SUM(due_amount) FROM purchases WHERE purchases.supplier_id = suppliers.id), 0) '.$order);
            })
            ->orderColumn('total_due', function ($query, $order) {
                $query->orderByRaw('(suppliers.opening_due + COALESCE((SELECT SUM(due_amount) FROM purchases WHERE purchases.supplier_id = suppliers.id), 0)) '.$order);
            })
            ->setRowId('id')
            ->setRowAttr([
                'class' => 'clickable-supplier-row',
                'data-url' => fn (Supplier $supplier) => route('due-ledger.supplier.details', $supplier),
                'style' => 'cursor:pointer;',
            ])
            ->rawColumns(['name', 'purchase_summary', 'opening_due', 'purchase_due', 'total_due', 'last_purchase', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Supplier>
     */
    public function query(Supplier $model): QueryBuilder
    {
        return $model->newQuery()
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
            ])
            ->withSum('purchases', 'total')
            ->withSum('purchases', 'due_amount')
            ->withCount('purchases')
            ->withCount(['purchases as due_purchases_count' => fn ($q) => $q->where('due_amount', '>', 0)])
            ->with(['purchases' => fn ($q) => $q->latest('purchase_date')])
            ->where(function ($q) {
                $q->where('suppliers.opening_due', '>', 0)
                    ->orWhereHas('purchases', fn ($sq) => $sq->where('due_amount', '>', 0));
            });
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('purchase-due-data-table');
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
                ->width(220),
            Column::computed('purchase_summary')
                ->title('<span class="bn">ক্রয় সারসংক্ষেপ</span><span class="en">Purchase Summary</span>')
                ->width(150),
            Column::make('opening_due')
                ->title('<span class="bn">ওপেনিং বাকি</span><span class="en">Opening Due</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::computed('purchase_due')
                ->title('<span class="bn">বিল বাকি</span><span class="en">Bill Due</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::computed('total_due')
                ->title('<span class="bn">মোট বাকি</span><span class="en">Total Due</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::computed('last_purchase')
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
        return 'PurchaseDueLedger_'.date('YmdHis');
    }
}
