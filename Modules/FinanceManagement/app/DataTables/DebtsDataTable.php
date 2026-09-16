<?php

namespace Modules\FinanceManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\FinanceManagement\Models\Debt;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class DebtsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Debt>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('date', function (Debt $debt) {
                return '<span style="white-space:nowrap;">'.e($debt->date->format('d M, Y')).'</span>';
            })
            ->editColumn('lender_name', function (Debt $debt) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($debt->lender_name).'</div>';
            })
            ->editColumn('amount', function (Debt $debt) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); font-size:14px; white-space:nowrap;">'
                    .'৳'.number_format((float) $debt->amount, 2)
                    .'</span>';
            })
            ->addColumn('status', function (Debt $debt) {
                $badges = [
                    'unpaid' => ['bn' => 'অপরিশোধিত', 'en' => 'Unpaid', 'bg' => 'var(--red-100)', 'color' => 'var(--red-600)'],
                    'paid' => ['bn' => 'পরিশোধিত', 'en' => 'Paid', 'bg' => 'var(--green-100)', 'color' => 'var(--green-ink)'],
                ];
                $badge = $badges[$debt->status] ?? $badges['unpaid'];

                return '<span style="display:inline-block; font-size:11.5px; padding:3px 8px; border-radius:6px; font-weight:600; background:'.$badge['bg'].'; color:'.$badge['color'].'; white-space:nowrap;">'
                    .'<span class="bn">'.e($badge['bn']).'</span><span class="en" style="display:none;">'.e($badge['en']).'</span></span>';
            })
            ->editColumn('note', function (Debt $debt) {
                if (! $debt->note) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12.5px; color:var(--ink-600); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($debt->note).'">'.e($debt->note).'</div>';
            })
            ->addColumn('action', function (Debt $debt) {
                return view('financemanagement::debts.datatables-actions', compact('debt'))->render();
            })
            ->filterColumn('lender_name', function ($query, $keyword) {
                $query->where('debts.lender_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->where('debts.date', 'like', "%{$keyword}%");
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('debts.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('debts.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['date', 'lender_name', 'amount', 'status', 'note', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Debt>
     */
    public function query(Debt $model): QueryBuilder
    {
        $query = $model->newQuery()->select([
            'debts.id',
            'debts.shop_id',
            'debts.account_id',
            'debts.lender_name',
            'debts.date',
            'debts.amount',
            'debts.status',
            'debts.note',
            'debts.created_at',
        ]);

        if ($status = request('status')) {
            $query->where('debts.status', $status);
        }

        return $query->latest('debts.date')->latest('debts.id');
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy(1, 'desc')
            ->minifiedAjax('', '
                data.status = $("#filter-debt-status").val();
            ');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('lender_name')
                ->title('<span class="bn">ঋণদাতা</span><span class="en">Lender</span>')
                ->width(180),
            Column::make('date')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::make('amount')
                ->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(140),
            Column::computed('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(120),
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
        return 'Debts_'.date('YmdHis');
    }
}
