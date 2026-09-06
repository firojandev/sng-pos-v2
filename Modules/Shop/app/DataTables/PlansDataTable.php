<?php

namespace Modules\Shop\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Shop\Models\Plan;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class PlansDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Plan>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Plan $plan) {
                $slug = e($plan->slug);
                $name = e($plan->name);
                $slugBadge = Blade::render('<x-core::badge color="grey" size="xs" variant="outline">{{ $slug }}</x-core::badge>', ['slug' => $slug]);

                return '<div style="display:flex; flex-direction:column; align-items:flex-start; gap:4px;">'
                    .'<span style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.2;">'.$name.'</span>'
                    .$slugBadge
                    .'</div>';
            })
            ->editColumn('price', function (Plan $plan) {
                $cycleBn = $plan->billing_cycle === 'yearly' ? 'বছর' : 'মাস';
                $cycleEn = $plan->billing_cycle === 'yearly' ? 'yr' : 'mo';
                $formattedPrice = number_format((float) $plan->price, 0);

                return '<div style="font-weight:800; color:var(--teal-800); font-size:13.5px;">'
                    .'৳'.$formattedPrice
                    .'<span style="font-size:11px; color:var(--ink-500); font-weight:600; margin-left:2px;">/'
                    .'<span class="bn">'.$cycleBn.'</span>'
                    .'<span class="en">'.$cycleEn.'</span>'
                    .'</span>'
                    .'</div>';
            })
            ->editColumn('max_users', function (Plan $plan) {
                if ($plan->max_users !== null) {
                    return Blade::render('<x-core::badge color="grey" size="xs">{{ $limit }}</x-core::badge>', ['limit' => $plan->max_users]);
                }

                return '<span style="font-weight:800; color:var(--teal-700); font-size:15px;" title="Unlimited">&infin;</span>';
            })
            ->editColumn('max_branches', function (Plan $plan) {
                if ($plan->max_branches !== null) {
                    return Blade::render('<x-core::badge color="grey" size="xs">{{ $limit }}</x-core::badge>', ['limit' => $plan->max_branches]);
                }

                return '<span style="font-weight:800; color:var(--teal-700); font-size:15px;" title="Unlimited">&infin;</span>';
            })
            ->editColumn('max_warehouses', function (Plan $plan) {
                if ($plan->max_warehouses !== null) {
                    return Blade::render('<x-core::badge color="grey" size="xs">{{ $limit }}</x-core::badge>', ['limit' => $plan->max_warehouses]);
                }

                return '<span style="font-weight:800; color:var(--teal-700); font-size:15px;" title="Unlimited">&infin;</span>';
            })
            ->editColumn('max_products', function (Plan $plan) {
                if ($plan->max_products !== null) {
                    $formatted = number_format((float) $plan->max_products);

                    return Blade::render('<x-core::badge color="grey" size="xs">{{ $limit }}</x-core::badge>', ['limit' => $formatted]);
                }

                return '<span style="font-weight:800; color:var(--teal-700); font-size:15px;" title="Unlimited">&infin;</span>';
            })
            ->editColumn('subscriptions_count', function (Plan $plan) {
                $count = (int) ($plan->subscriptions_count ?? 0);

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft" :dot="true">{{ $count }}</x-core::badge>', ['count' => $count]);
            })
            ->editColumn('status', function (Plan $plan) {
                if ($plan->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Plan $plan) {
                return view('shop::plans.datatables-actions', compact('plan'))->render();
            })
            ->rawColumns(['name', 'price', 'max_users', 'max_branches', 'max_warehouses', 'max_products', 'subscriptions_count', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Plan>
     */
    public function query(Plan $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('subscriptions')
            ->select([
                'plans.id',
                'plans.name',
                'plans.slug',
                'plans.price',
                'plans.billing_cycle',
                'plans.max_users',
                'plans.max_branches',
                'plans.max_warehouses',
                'plans.max_products',
                'plans.status',
                'plans.created_at',
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
            Column::make('name')->title('<span class="bn">প্ল্যান</span><span class="en">Plan</span>')->width(180),
            Column::make('price')->title('<span class="bn">মূল্য</span><span class="en">Price</span>')->width(110),
            Column::make('max_users')->title('<span class="bn">ইউজার</span><span class="en">Users</span>')->addClass('table-cell-center')->width(80),
            Column::make('max_branches')->title('<span class="bn">শাখা</span><span class="en">Branches</span>')->addClass('table-cell-center')->width(80),
            Column::make('max_warehouses')->title('<span class="bn">গুদাম</span><span class="en">Warehouses</span>')->addClass('table-cell-center')->width(80),
            Column::make('max_products')->title('<span class="bn">পণ্য</span><span class="en">Products</span>')->addClass('table-cell-center')->width(90),
            Column::make('subscriptions_count')->title('<span class="bn">সাবস্ক্রাইবার</span><span class="en">Subscribers</span>')->addClass('table-cell-center')->width(110)->searchable(false),
            Column::make('status')->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')->addClass('table-cell-center')->width(90),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Plans_'.date('YmdHis');
    }
}
