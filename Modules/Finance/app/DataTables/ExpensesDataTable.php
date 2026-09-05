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
            ->addColumn('voucher_no', function (Expense $expense) {
                $voucher = '#EXP-'.str_pad((string) $expense->id, 5, '0', STR_PAD_LEFT);

                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:12px; color:var(--red-800, #991b1b); background:var(--red-100, rgba(239, 68, 68, 0.12)); padding:3px 8px; border-radius:6px; white-space:nowrap; border:1px solid var(--red-200, rgba(239, 68, 68, 0.25));">'
                    .e($voucher)
                    .'</span>';
            })
            ->editColumn('expense_date', function (Expense $expense) {
                if (! $expense->expense_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $dateStr = $expense->expense_date->format('d M, Y');
                $dayStr = $expense->expense_date->format('l');
                $dayBnMap = [
                    'Saturday' => 'শনিবার',
                    'Sunday' => 'রবিবার',
                    'Monday' => 'সোমবার',
                    'Tuesday' => 'মঙ্গলবার',
                    'Wednesday' => 'বুধবার',
                    'Thursday' => 'বৃহস্পতিবার',
                    'Friday' => 'শুক্রবার',
                ];
                $dayBn = $dayBnMap[$dayStr] ?? $dayStr;

                return '<div style="font-size:13px; font-weight:600; color:var(--ink-800); white-space:nowrap;">'.e($dateStr).'</div>'
                    .'<div style="font-size:11px; color:var(--ink-400); white-space:nowrap;"><span class="bn">'.e($dayBn).'</span><span class="en" style="display:none;">'.e($dayStr).'</span></div>';
            })
            ->editColumn('title', function (Expense $expense) {
                $titleText = '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px; display:flex; align-items:center; gap:6px;">'
                    .'<span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; background:var(--red-100); color:var(--red-600); flex-shrink:0;">'
                    .'<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17h10V7"/><path d="m17 17-10-10"/></svg>'
                    .'</span>'
                    .'<span>'.e($expense->title).'</span>'
                    .'</div>';

                $note = $expense->note
                    ? '<div style="font-size:11.5px; color:var(--ink-500); font-weight:400; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-top:3px;" title="'.e($expense->note).'">'
                        .e($expense->note)
                        .'</div>'
                    : '';

                return $titleText.$note;
            })
            ->addColumn('category', function (Expense $expense) {
                if (! $expense->category) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $catName = e($expense->category->name);
                $subName = $expense->subCategory ? ' <span style="color:var(--ink-500); font-size:11.5px;">/ '.e($expense->subCategory->name).'</span>' : '';

                return '<div style="font-weight:600; color:var(--ink-800); font-size:13px;">'.$catName.$subName.'</div>';
            })
            ->addColumn('account', function (Expense $expense) {
                if (! $expense->account) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $acc = $expense->account;
                $name = '<div style="font-weight:600; color:var(--ink-900); font-size:13px;">'.e($acc->name).'</div>';

                $typeBadges = [
                    'cash' => '<span style="display:inline-block; font-size:11px; padding:2px 7px; border-radius:4px; font-weight:600; background:var(--green-100); color:var(--green-ink);"><span class="bn">নগদ</span><span class="en" style="display:none;">Cash</span></span>',
                    'bank' => '<span style="display:inline-block; font-size:11px; padding:2px 7px; border-radius:4px; font-weight:600; background:var(--blue-100); color:var(--blue-ink);"><span class="bn">ব্যাংক</span><span class="en" style="display:none;">Bank</span></span>',
                    'mfs' => '<span style="display:inline-block; font-size:11px; padding:2px 7px; border-radius:4px; font-weight:600; background:var(--gold-100); color:var(--gold-ink);"><span class="bn">মোবাইল ব্যাংকিং</span><span class="en" style="display:none;">MFS</span></span>',
                ];

                $badge = $typeBadges[$acc->type] ?? '';
                $details = '';
                if ($acc->type === 'bank' && ($acc->bank_name || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->bank_name ?? '').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                } elseif ($acc->type === 'mfs' && ($acc->mfs_provider || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->mfs_provider ?? 'MFS').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                }

                return $name.'<div style="margin-top:2px; display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'.$badge.$details.'</div>';
            })
            ->addColumn('payment_method', function (Expense $expense) {
                $method = $expense->payment_method;
                if (! $method && $expense->account) {
                    $method = $expense->account->type;
                }

                if (! $method) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $methodLabels = [
                    'cash' => ['bn' => 'নগদ', 'en' => 'Cash', 'bg' => 'var(--green-100)', 'color' => 'var(--green-ink)'],
                    'bank_transfer' => ['bn' => 'ব্যাংক ট্রান্সফার', 'en' => 'Bank Transfer', 'bg' => 'var(--blue-100)', 'color' => 'var(--blue-ink)'],
                    'bank' => ['bn' => 'ব্যাংক', 'en' => 'Bank', 'bg' => 'var(--blue-100)', 'color' => 'var(--blue-ink)'],
                    'cheque' => ['bn' => 'চেক', 'en' => 'Cheque', 'bg' => 'var(--blue-100)', 'color' => 'var(--blue-ink)'],
                    'bkash' => ['bn' => 'বিকাশ', 'en' => 'bKash', 'bg' => 'var(--red-100)', 'color' => 'var(--red-600)'],
                    'nagad' => ['bn' => 'নগদ (MFS)', 'en' => 'Nagad', 'bg' => 'var(--gold-100)', 'color' => 'var(--gold-ink)'],
                    'rocket' => ['bn' => 'রকেট', 'en' => 'Rocket', 'bg' => 'var(--blue-100)', 'color' => 'var(--blue-ink)'],
                    'mfs' => ['bn' => 'মোবাইল ব্যাংকিং', 'en' => 'MFS', 'bg' => 'var(--gold-100)', 'color' => 'var(--gold-ink)'],
                    'other' => ['bn' => 'অন্যান্য', 'en' => 'Other', 'bg' => 'var(--paper-line)', 'color' => 'var(--ink-700)'],
                ];

                $matched = $methodLabels[$method] ?? [
                    'bn' => $method,
                    'en' => ucfirst($method),
                    'bg' => 'var(--paper-line)',
                    'color' => 'var(--ink-700)',
                ];

                return '<span style="display:inline-block; font-size:11.5px; padding:3px 8px; border-radius:6px; font-weight:600; background:'.$matched['bg'].'; color:'.$matched['color'].'; white-space:nowrap;">'
                    .'<span class="bn">'.e($matched['bn']).'</span><span class="en" style="display:none;">'.e($matched['en']).'</span>'
                    .'</span>';
            })
            ->editColumn('amount', function (Expense $expense) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--red-600); font-size:14px; white-space:nowrap;">'
                    .'- ৳'.number_format((float) $expense->amount, 2)
                    .'</span>';
            })
            ->addColumn('action', function (Expense $expense) {
                return view('finance::expenses.datatables-actions', compact('expense'))->render();
            })
            ->filterColumn('voucher_no', function ($query, $keyword) {
                $clean = preg_replace('/[^0-9]/', '', $keyword);
                if (! empty($clean)) {
                    $query->where('expenses.id', 'like', "%{$clean}%");
                } else {
                    $query->where('expenses.id', 'like', "%{$keyword}%");
                }
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
            ->filterColumn('payment_method', function ($query, $keyword) {
                $query->where('expenses.payment_method', 'like', "%{$keyword}%");
            })
            ->orderColumn('voucher_no', 'expenses.id $1')
            ->rawColumns(['voucher_no', 'expense_date', 'title', 'category', 'amount', 'account', 'payment_method', 'action'])
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

        if ($paymentMethod = request('payment_method')) {
            $query->where('expenses.payment_method', $paymentMethod);
        }

        if ($dateFrom = request('date_from')) {
            $query->whereDate('expenses.expense_date', '>=', $dateFrom);
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('expenses.expense_date', '<=', $dateTo);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy([0, 'desc'])
            ->minifiedAjax('', '
                data.category_id = $("#filter-expense-category").val();
                data.account_id = $("#filter-expense-account").val();
                data.payment_method = $("#filter-expense-method").val();
                data.date_from = $("#filter-expense-date-from").val();
                data.date_to = $("#filter-expense-date-to").val();
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
            Column::make('voucher_no')
                ->data('voucher_no')
                ->name('id')
                ->title('<span class="bn">ভাউচার নং</span><span class="en">Voucher No</span>')
                ->width(120),
            Column::make('expense_date')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::make('title')
                ->title('<span class="bn">শিরোনাম ও বিবরণ</span><span class="en">Title & Details</span>')
                ->width(200),
            Column::computed('category')
                ->title('<span class="bn">ক্যাটাগরি</span><span class="en">Category</span>')
                ->orderable(false)
                ->width(160),
            Column::computed('account')
                ->title('<span class="bn">পেমেন্ট অ্যাকাউন্ট</span><span class="en">Payment Account</span>')
                ->orderable(false)
                ->width(180),
            Column::computed('payment_method')
                ->title('<span class="bn">পেমেন্ট মেথড</span><span class="en">Payment Method</span>')
                ->orderable(false)
                ->width(130),
            Column::make('amount')
                ->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(130),
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
        return 'Expenses_'.date('YmdHis');
    }
}
