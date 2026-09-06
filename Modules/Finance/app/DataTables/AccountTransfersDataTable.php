<?php

namespace Modules\Finance\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Finance\Models\AccountTransfer;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class AccountTransfersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<AccountTransfer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('transfer_no', function (AccountTransfer $transfer) {
                $no = $transfer->transfer_no ?: ('#TRF-'.str_pad((string) $transfer->id, 5, '0', STR_PAD_LEFT));

                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; font-size:12px; color:var(--teal-800); background:var(--teal-100); padding:3px 8px; border-radius:6px; white-space:nowrap; border:1px solid var(--teal-200, rgba(20, 184, 166, 0.25));">'
                    .e($no)
                    .'</span>';
            })
            ->editColumn('transfer_date', function (AccountTransfer $transfer) {
                if (! $transfer->transfer_date) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $dateStr = $transfer->transfer_date->format('d M, Y');
                $dayStr = $transfer->transfer_date->format('l');
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
            ->addColumn('from_account', function (AccountTransfer $transfer) {
                if (! $transfer->fromAccount) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $acc = $transfer->fromAccount;
                $header = '<div style="font-weight:700; color:var(--red-600); font-size:13px; display:flex; align-items:center; gap:6px;">'
                    .'<span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; background:var(--red-100); color:var(--red-600); flex-shrink:0;">'
                    .'<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17h10V7"/><path d="m17 17-10-10"/></svg>'
                    .'</span>'
                    .'<span>'.e($acc->name).'</span>'
                    .'</div>';

                $typeBadges = [
                    'cash' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--green-100); color:var(--green-ink);"><span class="bn">নগদ</span><span class="en" style="display:none;">Cash</span></span>',
                    'bank' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--blue-100); color:var(--blue-ink);"><span class="bn">ব্যাংক</span><span class="en" style="display:none;">Bank</span></span>',
                    'mfs' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--gold-100); color:var(--gold-ink);"><span class="bn">মোবাইল ব্যাংকিং</span><span class="en" style="display:none;">MFS</span></span>',
                ];
                $badge = $typeBadges[$acc->type] ?? '';
                $details = '';
                if ($acc->type === 'bank' && ($acc->bank_name || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->bank_name ?? '').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                } elseif ($acc->type === 'mfs' && ($acc->mfs_provider || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->mfs_provider ?? 'MFS').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                }

                return $header.'<div style="margin-top:3px; display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'.$badge.$details.'</div>';
            })
            ->addColumn('to_account', function (AccountTransfer $transfer) {
                if (! $transfer->toAccount) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                $acc = $transfer->toAccount;
                $header = '<div style="font-weight:700; color:var(--green-ink); font-size:13px; display:flex; align-items:center; gap:6px;">'
                    .'<span style="display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:50%; background:var(--green-100); color:var(--green-ink); flex-shrink:0;">'
                    .'<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>'
                    .'</span>'
                    .'<span>'.e($acc->name).'</span>'
                    .'</div>';

                $typeBadges = [
                    'cash' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--green-100); color:var(--green-ink);"><span class="bn">নগদ</span><span class="en" style="display:none;">Cash</span></span>',
                    'bank' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--blue-100); color:var(--blue-ink);"><span class="bn">ব্যাংক</span><span class="en" style="display:none;">Bank</span></span>',
                    'mfs' => '<span style="display:inline-block; font-size:10.5px; padding:2px 6px; border-radius:4px; font-weight:600; background:var(--gold-100); color:var(--gold-ink);"><span class="bn">মোবাইল ব্যাংকিং</span><span class="en" style="display:none;">MFS</span></span>',
                ];
                $badge = $typeBadges[$acc->type] ?? '';
                $details = '';
                if ($acc->type === 'bank' && ($acc->bank_name || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->bank_name ?? '').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                } elseif ($acc->type === 'mfs' && ($acc->mfs_provider || $acc->account_number)) {
                    $details = '<span style="font-size:11px; color:var(--ink-500);">'.e($acc->mfs_provider ?? 'MFS').($acc->account_number ? ' ('.e($acc->account_number).')' : '').'</span>';
                }

                return $header.'<div style="margin-top:3px; display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'.$badge.$details.'</div>';
            })
            ->editColumn('amount', function (AccountTransfer $transfer) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900); font-size:14px; white-space:nowrap;">'
                    .'৳ '.number_format((float) $transfer->amount, 2)
                    .'</span>';
            })
            ->editColumn('charge', function (AccountTransfer $transfer) {
                if ((float) $transfer->charge > 0) {
                    return '<span style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--red-600); font-size:13px; white-space:nowrap;">'
                        .'৳ '.number_format((float) $transfer->charge, 2)
                        .'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('details', function (AccountTransfer $transfer) {
                $note = $transfer->note
                    ? '<div style="font-size:12.5px; color:var(--ink-800); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($transfer->note).'">'.e($transfer->note).'</div>'
                    : '<div style="font-size:12px; color:var(--ink-400);">—</div>';

                $creator = e($transfer->creator->name ?? 'System');
                $creatorHtml = '<div style="font-size:11px; color:var(--ink-500); margin-top:2px; display:flex; align-items:center; gap:4px;">'
                    .'<span class="bn">এন্ট্রি:</span><span class="en" style="display:none;">By:</span> <span>'.$creator.'</span>'
                    .'</div>';

                return $note.$creatorHtml;
            })
            ->addColumn('action', function (AccountTransfer $transfer) {
                return view('finance::transfers.datatables-actions', compact('transfer'))->render();
            })
            ->filterColumn('transfer_no', function ($query, $keyword) {
                $query->where('account_transfers.transfer_no', 'like', "%{$keyword}%");
            })
            ->filterColumn('transfer_date', function ($query, $keyword) {
                $query->where('account_transfers.transfer_date', 'like', "%{$keyword}%");
            })
            ->filterColumn('from_account', function ($query, $keyword) {
                $query->whereHas('fromAccount', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('to_account', function ($query, $keyword) {
                $query->whereHas('toAccount', fn ($q) => $q->where('name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('account_transfers.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('charge', function ($query, $keyword) {
                $query->where('account_transfers.charge', 'like', "%{$keyword}%");
            })
            ->filterColumn('details', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('account_transfers.note', 'like', "%{$keyword}%")
                        ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->orderColumn('transfer_no', 'account_transfers.id $1')
            ->orderColumn('transfer_date', 'account_transfers.transfer_date $1')
            ->orderColumn('amount', 'account_transfers.amount $1')
            ->orderColumn('charge', 'account_transfers.charge $1')
            ->rawColumns(['transfer_no', 'transfer_date', 'from_account', 'to_account', 'amount', 'charge', 'details', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<AccountTransfer>
     */
    public function query(AccountTransfer $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['fromAccount', 'toAccount', 'creator'])
            ->select([
                'account_transfers.id',
                'account_transfers.shop_id',
                'account_transfers.transfer_no',
                'account_transfers.from_account_id',
                'account_transfers.to_account_id',
                'account_transfers.amount',
                'account_transfers.charge',
                'account_transfers.transfer_date',
                'account_transfers.note',
                'account_transfers.created_by',
                'account_transfers.created_at',
            ]);

        if ($fromAccountId = request('from_account_id')) {
            $query->where('account_transfers.from_account_id', $fromAccountId);
        }

        if ($toAccountId = request('to_account_id')) {
            $query->where('account_transfers.to_account_id', $toAccountId);
        }

        if ($dateFrom = request('date_from')) {
            $query->whereDate('account_transfers.transfer_date', '>=', $dateFrom);
        }

        if ($dateTo = request('date_to')) {
            $query->whereDate('account_transfers.transfer_date', '<=', $dateTo);
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
                data.from_account_id = $("#filter-from-account").val();
                data.to_account_id = $("#filter-to-account").val();
                data.date_from = $("#filter-date-from").val();
                data.date_to = $("#filter-date-to").val();
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
            Column::make('transfer_no')
                ->title('<span class="bn">ভাউচার নং</span><span class="en">Voucher No</span>')
                ->width(140),
            Column::make('transfer_date')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::computed('from_account')
                ->title('<span class="bn">উৎস অ্যাকাউন্ট (হতে)</span><span class="en">From Account</span>')
                ->orderable(false)
                ->width(180),
            Column::computed('to_account')
                ->title('<span class="bn">গন্তব্য অ্যাকাউন্ট (জমা)</span><span class="en">To Account</span>')
                ->orderable(false)
                ->width(180),
            Column::make('amount')
                ->title('<span class="bn">পরিমাণ</span><span class="en">Amount</span>')
                ->addClass('table-cell-right')
                ->width(130),
            Column::make('charge')
                ->title('<span class="bn">চার্জ / ফি</span><span class="en">Fee</span>')
                ->addClass('table-cell-right')
                ->width(110),
            Column::computed('details')
                ->title('<span class="bn">মন্তব্য ও এন্ট্রি</span><span class="en">Note & Creator</span>')
                ->orderable(false)
                ->width(180),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Account_Transfers_'.date('YmdHis');
    }
}
