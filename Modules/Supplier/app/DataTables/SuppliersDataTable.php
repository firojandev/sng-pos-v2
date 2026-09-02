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
                $email = $supplier->email ? '<div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">'.e($supplier->email).'</div>' : '';

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px;">'
                    .'<div class="av" style="width:32px; height:32px; border-radius:8px; background:var(--teal-800); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div>'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($supplier->name).'</div>'
                    .$email
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('phone', function (Supplier $supplier) {
                if (! $supplier->phone) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12.5px; color:var(--ink-700);">'
                    .e($supplier->phone)
                    .'</span>';
            })
            ->editColumn('address', function (Supplier $supplier) {
                if (! $supplier->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:13px;">'.e($supplier->address).'</span>';
            })
            ->addColumn('total_due', function (Supplier $supplier) {
                $due = (float) $supplier->opening_due + (float) ($supplier->purchases_sum_due_amount ?? 0);
                if ($due > 0) {
                    return '<span style="color:var(--red-600); font-weight:700; font-family:var(--font-mono, monospace);">৳'.number_format($due, 2).'</span>';
                }

                return '<span style="color:var(--ink-500); font-family:var(--font-mono, monospace);">৳0.00</span>';
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
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('suppliers.name', 'like', "%{$keyword}%")
                    ->orWhere('suppliers.email', 'like', "%{$keyword}%");
            })
            ->filterColumn('phone', function ($query, $keyword) {
                $query->where('suppliers.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('address', function ($query, $keyword) {
                $query->where('suppliers.address', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('suppliers.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'phone', 'address', 'total_due', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Supplier>
     */
    public function query(Supplier $model): QueryBuilder
    {
        return $model->newQuery()
            ->withSum('purchases', 'due_amount')
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
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml();
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">নাম</span><span class="en">Name</span>')->width(220),
            Column::make('phone')->title('<span class="bn">ফোন</span><span class="en">Phone</span>')->width(140),
            Column::make('address')->title('<span class="bn">ঠিকানা</span><span class="en">Address</span>'),
            Column::computed('total_due')
                ->title('<span class="bn">বাকি</span><span class="en">Due</span>')
                ->addClass('table-cell-right')
                ->width(120),
            Column::make('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(100),
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
        return 'Suppliers_'.date('YmdHis');
    }
}
