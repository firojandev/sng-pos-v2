<?php

namespace Modules\Shop\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Shop\Models\Warehouse;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class WarehousesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Warehouse>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Warehouse $warehouse) {
                $badge = '';
                if ($warehouse->is_default) {
                    $badge = ' <span style="display:inline-block; font-size:10.5px; font-weight:700; background:var(--gold-100); color:var(--gold-ink); padding:2px 7px; border-radius:4px; margin-left:6px; vertical-align:middle;">ডিফল্ট</span>';
                }

                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; display:flex; align-items:center;">'
                    .'<span>'.e($warehouse->name).'</span>'
                    .$badge
                    .'</div>';
            })
            ->addColumn('branch', function (Warehouse $warehouse) {
                if ($warehouse->branch) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'
                        .e($warehouse->branch->name)
                        .'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('address', function (Warehouse $warehouse) {
                if (! $warehouse->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:13px;">'.e($warehouse->address).'</span>';
            })
            ->editColumn('batches_count', function (Warehouse $warehouse) {
                $count = (int) ($warehouse->batches_count ?? 0);

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft">{{ $count }} টি</x-core::badge>', ['count' => $count]);
            })
            ->addColumn('is_default', function (Warehouse $warehouse) {
                if ($warehouse->is_default) {
                    return Blade::render('<x-core::badge color="teal" size="xs" variant="soft" icon="check" label="ডিফল্ট" label-en="Default" />');
                }

                if ($warehouse->status === 'active') {
                    return '<x-core::button type="button" variant="ghost" color="secondary" size="xs" class="btn-set-default-warehouse" data-id="'.$warehouse->id.'" data-url="'.route('warehouses.set-default', $warehouse).'" title="ডিফল্ট হিসেবে সেট করুন">ডিফল্ট করুন</x-core::button>';
                }

                return '<span style="color:var(--ink-400); font-size:12px;">—</span>';
            })
            ->editColumn('status', function (Warehouse $warehouse) {
                if ($warehouse->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Warehouse $warehouse) {
                return view('shop::warehouses.datatables-actions', compact('warehouse'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('warehouses.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('branch', function ($query, $keyword) {
                $query->whereHas('branch', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('address', function ($query, $keyword) {
                $query->where('warehouses.address', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('warehouses.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'branch', 'address', 'batches_count', 'is_default', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Warehouse>
     */
    public function query(Warehouse $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['branch'])
            ->withCount('batches')
            ->select([
                'warehouses.id',
                'warehouses.shop_id',
                'warehouses.branch_id',
                'warehouses.name',
                'warehouses.address',
                'warehouses.status',
                'warehouses.is_default',
                'warehouses.created_at',
            ])
            ->orderByDesc('warehouses.is_default');

        if ($branchId = request('branch_id')) {
            $query->where('warehouses.branch_id', $branchId);
        }

        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->where('warehouses.status', $status);
            }
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax('', 'data.branch_id = $("#filter-branch").val(); data.status = $("#filter-status").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">গুদামের নাম</span><span class="en">Warehouse Name</span>')->width(180),
            Column::computed('branch')->title('<span class="bn">শাখা</span><span class="en">Branch</span>')->width(150),
            Column::make('address')->title('<span class="bn">ঠিকানা</span><span class="en">Address</span>'),
            Column::make('batches_count')->title('<span class="bn">ব্যাচ সংখ্যা</span><span class="en">Batches</span>')->addClass('table-cell-center')->width(110)->searchable(false),
            Column::computed('is_default')->title('<span class="bn">ডিফল্ট</span><span class="en">Default</span>')->addClass('table-cell-center')->width(100)->orderable(false)->searchable(false),
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
        return 'Warehouses_'.date('YmdHis');
    }
}
