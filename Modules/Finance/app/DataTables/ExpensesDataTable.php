<?php

namespace Modules\Finance\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\Expense;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class ExpensesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Expense>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('title', function (Expense $expense) {
                $title = '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($expense->title).'</div>';
                $note = $expense->note ? '<div style="font-size:11.5px; color:var(--ink-500); font-weight:400; max-width:260px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">'.e($expense->note).'</div>' : '';

                return $title.$note;
            })
            ->addColumn('category', function (Expense $expense) {
                $catName = $expense->category ? e($expense->category->name) : '—';
                $subName = $expense->subCategory ? ' <span style="color:var(--ink-500); font-size:12px;">/ '.e($expense->subCategory->name).'</span>' : '';

                return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'.$catName.'</span>'.$subName;
            })
            ->editColumn('expense_date', function (Expense $expense) {
                if (! $expense->expense_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-size:13px; color:var(--ink-700); white-space:nowrap;">'
                    .e($expense->expense_date->format('d M, Y'))
                    .'</span>';
            })
            ->editColumn('amount', function (Expense $expense) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600); font-size:13.5px; white-space:nowrap;">৳'.number_format((float) $expense->amount, 2).'</span>';
            })
            ->addColumn('account', function (Expense $expense) {
                if ($expense->account) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'.e($expense->account->display_name).'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('action', function (Expense $expense) {
                return view('finance::expenses.datatables-actions', compact('expense'))->render();
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('expenses.title', 'like', "%{$keyword}%")
                        ->orWhere('expenses.note', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->whereHas('category', fn ($c) => $c->where('name', 'like', "%{$keyword}%"))
                        ->orWhereHas('subCategory', fn ($s) => $s->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->filterColumn('expense_date', function ($query, $keyword) {
                $query->where('expenses.expense_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('expenses.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('account', function ($query, $keyword) {
                $query->whereHas('account', fn ($a) => $a->where('name', 'like', "%{$keyword}%"));
            })
            ->rawColumns(['title', 'category', 'expense_date', 'amount', 'account', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Expense>
     */
    public function query(Expense $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['category', 'subCategory', 'account'])
            ->select([
                'expenses.id',
                'expenses.shop_id',
                'expenses.account_id',
                'expenses.expense_category_id',
                'expenses.expense_sub_category_id',
                'expenses.title',
                'expenses.amount',
                'expenses.expense_date',
                'expenses.payment_method',
                'expenses.note',
                'expenses.created_at',
            ]);

        if ($categoryId = request('category_id')) {
            $query->where('expenses.expense_category_id', $categoryId);
        }

        if ($accountId = request('account_id')) {
            $query->where('expenses.account_id', $accountId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy([2, 'desc'])
            ->minifiedAjax('', '
                data.category_id = $("#filter-expense-category").val();
                data.account_id = $("#filter-expense-account").val();
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
            Column::make('title')->title('<span class="bn">শিরোনাম</span><span class="en">Title</span>')->width(220),
            Column::computed('category')->title('<span class="bn">ক্যাটাগরি</span><span class="en">Category</span>')->orderable(false)->width(180),
            Column::make('expense_date')->title('<span class="bn">তারিখ</span><span class="en">Date</span>')->addClass('table-cell-center')->width(120),
            Column::make('amount')->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')->addClass('table-cell-right')->width(130),
            Column::computed('account')->title('<span class="bn">পেমেন্ট অ্যাকাউন্ট</span><span class="en">Payment Account</span>')->orderable(false)->width(170),
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
        return 'Expenses_'.date('YmdHis');
    }
}
