<?php

namespace Modules\Shop\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Shop\Models\Branch;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class BranchesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Branch>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Branch $branch) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'
                    .e($branch->name)
                    .'</div>';
            })
            ->editColumn('phone', function (Branch $branch) {
                if (! $branch->phone) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12.5px; color:var(--ink-700);">'
                    .e($branch->phone)
                    .'</span>';
            })
            ->editColumn('address', function (Branch $branch) {
                if (! $branch->address) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:13px;">'.e($branch->address).'</span>';
            })
            ->editColumn('warehouses_count', function (Branch $branch) {
                $count = (int) ($branch->warehouses_count ?? 0);

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft">{{ $count }} টি</x-core::badge>', ['count' => $count]);
            })
            ->editColumn('status', function (Branch $branch) {
                if ($branch->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Branch $branch) {
                return view('shop::branches.datatables-actions', compact('branch'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('branches.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('phone', function ($query, $keyword) {
                $query->where('branches.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('address', function ($query, $keyword) {
                $query->where('branches.address', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('branches.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'phone', 'address', 'warehouses_count', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Branch>
     */
    public function query(Branch $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('warehouses')
            ->select([
                'branches.id',
                'branches.shop_id',
                'branches.name',
                'branches.phone',
                'branches.address',
                'branches.status',
                'branches.created_at',
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
            Column::make('name')->title('<span class="bn">শাখার নাম</span><span class="en">Branch Name</span>')->width(200),
            Column::make('phone')->title('<span class="bn">মোবাইল</span><span class="en">Phone</span>')->width(140),
            Column::make('address')->title('<span class="bn">ঠিকানা</span><span class="en">Address</span>'),
            Column::make('warehouses_count')->title('<span class="bn">গুদাম সংখ্যা</span><span class="en">Warehouses</span>')->addClass('table-cell-center')->width(120)->searchable(false),
            Column::make('status')->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')->addClass('table-cell-center')->width(100),
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
        return 'Branches_'.date('YmdHis');
    }
}
