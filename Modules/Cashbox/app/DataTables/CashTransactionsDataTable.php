<?php

namespace Modules\Cashbox\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Cashbox\Models\CashTransaction;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CashTransactionsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<CashTransaction>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('source', function (CashTransaction $tx) {
                $label = $tx->sourceLabel();
                $bn = e($label['bn'] ?? $tx->source);
                $en = e($label['en'] ?? $tx->source);

                $isIn = $tx->type === 'in';
                $iconBg = $isIn ? 'var(--green-100)' : 'var(--red-100)';
                $iconColor = $isIn ? 'var(--green-ink)' : 'var(--red-600)';
                $arrow = $isIn
                    ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19V5M6 11l6-6 6 6"/></svg>'
                    : '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>';

                $idBadge = '<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-400); margin-top:2px;">#TX-'.str_pad((string) $tx->id, 5, '0', STR_PAD_LEFT).'</div>';

                return '<div style="display:flex; align-items:center; gap:10px;">'
                    .'<div style="width:30px; height:30px; border-radius:8px; background:'.$iconBg.'; color:'.$iconColor.'; display:flex; align-items:center; justify-content:center; flex-shrink:0;">'
                    .$arrow
                    .'</div>'
                    .'<div>'
                    .'<div style="font-weight:700; font-size:13px; color:var(--ink-900);"><span class="bn">'.$bn.'</span><span class="en" style="display:none;">'.$en.'</span></div>'
                    .$idBadge
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('occurred_at', function (CashTransaction $tx) {
                if (! $tx->occurred_at) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $dateStr = $tx->occurred_at->format('d M, Y');
                $timeStr = $tx->occurred_at->format('h:i A');

                return '<div style="font-weight:600; font-size:13px; color:var(--ink-800); white-space:nowrap;">'.e($dateStr).'</div>'
                    .'<div style="font-size:11px; color:var(--ink-400); font-family:var(--font-mono, monospace); margin-top:2px;">'.e($timeStr).'</div>';
            })
            ->editColumn('type', function (CashTransaction $tx) {
                if ($tx->type === 'in') {
                    return '<span class="badge b-green badge-green badge-xs badge-pill"><span class="badge-dot"></span><span class="bn">ক্যাশ ইন</span><span class="en" style="display:none;">Cash In</span></span>';
                }

                return '<span class="badge b-red badge-red badge-xs badge-pill"><span class="badge-dot"></span><span class="bn">ক্যাশ আউট</span><span class="en" style="display:none;">Cash Out</span></span>';
            })
            ->editColumn('note', function (CashTransaction $tx) {
                if (! $tx->note) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12.5px; color:var(--ink-600);" title="'.e($tx->note).'">'
                    .e($tx->note)
                    .'</div>';
            })
            ->addColumn('creator', function (CashTransaction $tx) {
                if (! $tx->creator) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12.5px; font-weight:600; color:var(--ink-800); white-space:nowrap;" title="'.e($tx->creator->name).'">'
                    .e($tx->creator->name)
                    .'</div>';
            })
            ->editColumn('amount', function (CashTransaction $tx) {
                $sign = $tx->type === 'in' ? '+' : '-';
                $color = $tx->type === 'in' ? 'var(--green-ink)' : 'var(--red-600)';
                $formatted = number_format((float) $tx->amount, 2);

                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:13.5px; color:'.$color.'; white-space:nowrap;">'
                    .e($sign).'৳'.e($formatted)
                    .'</span>';
            })
            ->with('summary', function () {
                $baseQuery = CashTransaction::query();
                $this->applyFilters($baseQuery);

                $summary = (clone $baseQuery)->selectRaw(
                    "SUM(CASE WHEN type = 'in' THEN amount ELSE 0 END) as cash_in, ".
                    "SUM(CASE WHEN type = 'out' THEN amount ELSE 0 END) as cash_out, ".
                    'COUNT(*) as total_count'
                )->first();

                $balance = CashTransaction::selectRaw("SUM(CASE WHEN type = 'in' THEN amount ELSE -amount END) as balance")->value('balance') ?? 0;

                return [
                    'balance' => (float) $balance,
                    'cash_in' => (float) ($summary->cash_in ?? 0),
                    'cash_out' => (float) ($summary->cash_out ?? 0),
                    'total_count' => (int) ($summary->total_count ?? 0),
                ];
            })
            ->rawColumns(['source', 'occurred_at', 'type', 'note', 'creator', 'amount'])
            ->setRowId('id');
    }

    /**
     * Apply request filters to query.
     *
     * @param  QueryBuilder<CashTransaction>  $query
     */
    protected function applyFilters(QueryBuilder $query): void
    {
        $type = request('type', 'all');
        $from = request('from');
        $to = request('to');
        $creator = request('creator');

        if ($from) {
            $query->whereDate('occurred_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('occurred_at', '<=', $to);
        }

        match ($type) {
            'cash_in' => $query->where('type', 'in'),
            'cash_out' => $query->where('type', 'out'),
            'sale' => $query->where('source', 'sale'),
            'purchase' => $query->where('source', 'purchase'),
            'income' => $query->where('source', 'income'),
            'expense' => $query->where('source', 'expense'),
            'sale_return' => $query->where('source', 'sale_return'),
            'purchase_return' => $query->where('source', 'purchase_return'),
            default => null,
        };

        if ($creator) {
            $query->where('created_by', $creator);
        }
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<CashTransaction>
     */
    public function query(CashTransaction $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with('creator')
            ->select([
                'cash_transactions.id',
                'cash_transactions.shop_id',
                'cash_transactions.type',
                'cash_transactions.source',
                'cash_transactions.sourceable_type',
                'cash_transactions.sourceable_id',
                'cash_transactions.amount',
                'cash_transactions.note',
                'cash_transactions.occurred_at',
                'cash_transactions.created_by',
                'cash_transactions.created_at',
            ]);

        $this->applyFilters($query);

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('cashbox-data-table')
            ->orderBy([1, 'desc'])
            ->minifiedAjax('', '
                data.type = $("#filter-cashbox-type").val();
                data.from = $("#filter-cashbox-from").val();
                data.to = $("#filter-cashbox-to").val();
                data.creator = $("#filter-cashbox-creator").val();
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
            Column::make('source')
                ->title('<span class="bn">উৎস ও ভাউচার</span><span class="en">Source & Voucher</span>')
                ->width(200),
            Column::make('occurred_at')
                ->title('<span class="bn">তারিখ ও সময়</span><span class="en">Date & Time</span>')
                ->width(150),
            Column::make('type')
                ->title('<span class="bn">লেনদেন ধরন</span><span class="en">Type</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::make('note')
                ->title('<span class="bn">নোট বা মন্তব্য</span><span class="en">Note</span>')
                ->width(220),
            Column::computed('creator')
                ->title('<span class="bn">প্রবেশকারী</span><span class="en">Created By</span>')
                ->orderable(false)
                ->width(140),
            Column::make('amount')
                ->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(130),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Cashbox_'.date('YmdHis');
    }
}
