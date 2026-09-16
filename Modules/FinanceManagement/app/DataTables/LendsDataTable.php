<?php

namespace Modules\FinanceManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\FinanceManagement\Models\Lend;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class LendsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Lend>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('date', function (Lend $lend) {
                return '<span style="white-space:nowrap;">'.e($lend->date->format('d M, Y')).'</span>';
            })
            ->editColumn('borrower_name', function (Lend $lend) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($lend->borrower_name).'</div>';
            })
            ->editColumn('amount', function (Lend $lend) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); font-size:14px; white-space:nowrap;">'
                    .'৳'.number_format((float) $lend->amount, 2)
                    .'</span>';
            })
            ->addColumn('status', function (Lend $lend) {
                $badges = [
                    'due' => ['bn' => 'বাকি', 'en' => 'Due', 'bg' => 'var(--red-100)', 'color' => 'var(--red-600)'],
                    'received' => ['bn' => 'ফেরত পাওয়া', 'en' => 'Received', 'bg' => 'var(--green-100)', 'color' => 'var(--green-ink)'],
                ];
                $badge = $badges[$lend->status] ?? $badges['due'];

                return '<span style="display:inline-block; font-size:11.5px; padding:3px 8px; border-radius:6px; font-weight:600; background:'.$badge['bg'].'; color:'.$badge['color'].'; white-space:nowrap;">'
                    .'<span class="bn">'.e($badge['bn']).'</span><span class="en" style="display:none;">'.e($badge['en']).'</span></span>';
            })
            ->editColumn('note', function (Lend $lend) {
                if (! $lend->note) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12.5px; color:var(--ink-600); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($lend->note).'">'.e($lend->note).'</div>';
            })
            ->addColumn('action', function (Lend $lend) {
                return view('financemanagement::lend.datatables-actions', compact('lend'))->render();
            })
            ->filterColumn('borrower_name', function ($query, $keyword) {
                $query->where('lends.borrower_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->where('lends.date', 'like', "%{$keyword}%");
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('lends.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('lends.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['date', 'borrower_name', 'amount', 'status', 'note', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Lend>
     */
    public function query(Lend $model): QueryBuilder
    {
        $query = $model->newQuery()->select([
            'lends.id',
            'lends.shop_id',
            'lends.account_id',
            'lends.borrower_name',
            'lends.date',
            'lends.amount',
            'lends.status',
            'lends.note',
            'lends.created_at',
        ]);

        if ($status = request('status')) {
            $query->where('lends.status', $status);
        }

        return $query->latest('lends.date')->latest('lends.id');
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy(1, 'desc')
            ->minifiedAjax('', '
                data.status = $("#filter-lend-status").val();
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
            Column::make('borrower_name')
                ->title('<span class="bn">গ্রহীতা</span><span class="en">Borrower</span>')
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
        return 'Lends_'.date('YmdHis');
    }
}
