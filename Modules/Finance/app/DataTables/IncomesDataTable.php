<?php

namespace Modules\Finance\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\Income;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class IncomesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Income>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('source', function (Income $income) {
                $source = '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($income->source).'</div>';
                $note = $income->note ? '<div style="font-size:11.5px; color:var(--ink-500); font-weight:400; max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">'.e($income->note).'</div>' : '';

                return $source.$note;
            })
            ->editColumn('income_date', function (Income $income) {
                if (! $income->income_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-size:13px; color:var(--ink-700); white-space:nowrap;">'
                    .e($income->income_date->format('d M, Y'))
                    .'</span>';
            })
            ->editColumn('amount', function (Income $income) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--teal-700); font-size:13.5px; white-space:nowrap;">৳'.number_format((float) $income->amount, 2).'</span>';
            })
            ->addColumn('account', function (Income $income) {
                if ($income->account) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'.e($income->account->display_name).'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('action', function (Income $income) {
                return view('finance::income.datatables-actions', compact('income'))->render();
            })
            ->filterColumn('source', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('incomes.source', 'like', "%{$keyword}%")
                        ->orWhere('incomes.note', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('income_date', function ($query, $keyword) {
                $query->where('incomes.income_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('incomes.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('account', function ($query, $keyword) {
                $query->whereHas('account', fn ($a) => $a->where('name', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['source', 'income_date', 'amount', 'account', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Income>
     */
    public function query(Income $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['account'])
            ->select([
                'incomes.id',
                'incomes.shop_id',
                'incomes.account_id',
                'incomes.source',
                'incomes.amount',
                'incomes.income_date',
                'incomes.payment_method',
                'incomes.note',
                'incomes.created_at',
            ]);

        if ($accountId = request('account_id')) {
            $query->where('incomes.account_id', $accountId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy([1, 'desc'])
            ->minifiedAjax('', '
                data.account_id = $("#filter-income-account").val();
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
            Column::make('source')->title('<span class="bn">উৎস</span><span class="en">Source</span>')->width(220),
            Column::make('income_date')->title('<span class="bn">তারিখ</span><span class="en">Date</span>')->addClass('table-cell-center')->width(120),
            Column::make('amount')->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')->addClass('table-cell-right')->width(130),
            Column::computed('account')->title('<span class="bn">জমা অ্যাকাউন্ট</span><span class="en">Deposit Account</span>')->orderable(false)->width(180),
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
        return 'Incomes_'.date('YmdHis');
    }
}
