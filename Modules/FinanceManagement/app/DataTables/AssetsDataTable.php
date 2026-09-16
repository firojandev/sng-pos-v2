<?php

namespace Modules\FinanceManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\FinanceManagement\Models\Asset;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class AssetsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Asset>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Asset $asset) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($asset->name).'</div>';
            })
            ->editColumn('amount', function (Asset $asset) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--ink-900); font-size:13.5px; white-space:nowrap;">'
                    .'৳'.number_format((float) $asset->amount, 2)
                    .'</span>';
            })
            ->addColumn('depreciation', function (Asset $asset) {
                $dep = (float) ($asset->depreciation ?? 0);
                if ($dep <= 0) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $depAmount = $asset->depreciation_amount;
                $formattedAmount = '-৳'.number_format($depAmount, 2);

                if ($asset->depreciation_type === 'percentage') {
                    $percentLabel = rtrim(rtrim(number_format($dep, 2), '0'), '.').'%';

                    return '<div style="display:flex; flex-direction:column; align-items:flex-end; line-height:1.2;">'
                        .'<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--red-600); font-size:13.5px; white-space:nowrap;">'.$formattedAmount.'</span>'
                        .'<span style="font-size:11px; color:var(--ink-400); font-weight:600;">('.$percentLabel.')</span>'
                        .'</div>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--red-600); font-size:13.5px; white-space:nowrap;">'
                    .$formattedAmount
                    .'</span>';
            })
            ->addColumn('net_value', function (Asset $asset) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--green-ink); font-size:14px; white-space:nowrap;">'
                    .'৳'.number_format($asset->net_value, 2)
                    .'</span>';
            })
            ->addColumn('validity', function (Asset $asset) {
                if ($asset->validity === null || $asset->validity === '') {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return $asset->validity_formatted ?? '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('note', function (Asset $asset) {
                if (! $asset->note) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12.5px; color:var(--ink-600); max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($asset->note).'">'.e($asset->note).'</div>';
            })
            ->addColumn('action', function (Asset $asset) {
                return view('financemanagement::assets.datatables-actions', compact('asset'))->render();
            })
            ->rawColumns(['name', 'amount', 'depreciation', 'net_value', 'validity', 'note', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Asset>
     */
    public function query(Asset $model): QueryBuilder
    {
        return $model->newQuery()->select([
            'assets.id',
            'assets.shop_id',
            'assets.name',
            'assets.amount',
            'assets.depreciation_type',
            'assets.depreciation',
            'assets.validity',
            'assets.validity_unit',
            'assets.note',
            'assets.created_at',
        ])->latest('assets.id');
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
            Column::make('name')
                ->title('<span class="bn">সম্পদের নাম</span><span class="en">Asset Name</span>')
                ->width(200),
            Column::make('amount')
                ->title('<span class="bn">মূল্য</span><span class="en">Cost / Amount</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::make('depreciation')
                ->title('<span class="bn">অবচয়</span><span class="en">Depreciation</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::computed('net_value')
                ->title('<span class="bn">নিট মূল্য</span><span class="en">Net Value</span>')
                ->addClass('table-cell-right')
                ->width(140),
            Column::make('validity')
                ->title('<span class="bn">মেয়াদ</span><span class="en">Validity</span>')
                ->addClass('table-cell-center')
                ->width(110),
            Column::computed('note')
                ->title('<span class="bn">নোট</span><span class="en">Note</span>'),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Assets_'.date('YmdHis');
    }
}
