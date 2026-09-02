<?php

namespace Modules\Customer\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Customer\Models\Customer;
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
                $email = $customer->email ? '<div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">'.e($customer->email).'</div>' : '';

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px;">'
                    .'<div class="av" style="width:32px; height:32px; border-radius:8px; background:var(--teal-800); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">'.e($initial).'</div>'
                    .'<div>'
                    .'<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($customer->name).'</div>'
                    .$email
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('phone', function (Customer $customer) {
                if (! $customer->phone) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12.5px; color:var(--ink-700);">'
                    .e($customer->phone)
                    .'</span>';
            })
            ->editColumn('address', function (Customer $customer) {
                if (! $customer->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:13px;">'.e($customer->address).'</span>';
            })
            ->addColumn('total_due', function (Customer $customer) {
                $due = (float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0);
                if ($due > 0) {
                    return '<span style="color:var(--red-600); font-weight:700; font-family:var(--font-mono, monospace);">৳'.number_format($due, 2).'</span>';
                }

                return '<span style="color:var(--ink-500); font-family:var(--font-mono, monospace);">৳0.00</span>';
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
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('customers.name', 'like', "%{$keyword}%")
                    ->orWhere('customers.email', 'like', "%{$keyword}%");
            })
            ->filterColumn('phone', function ($query, $keyword) {
                $query->where('customers.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('address', function ($query, $keyword) {
                $query->where('customers.address', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('customers.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'phone', 'address', 'total_due', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Customer>
     */
    public function query(Customer $model): QueryBuilder
    {
        return $model->newQuery()
            ->withSum('sales', 'due_amount')
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
        return 'Customers_'.date('YmdHis');
    }
}
