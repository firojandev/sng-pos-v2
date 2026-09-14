<?php

namespace Modules\FinanceManagement\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\FinanceManagement\Models\SecurityMoney;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class SecurityMoneysDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<SecurityMoney>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('date', function (SecurityMoney $securityMoney) {
                return '<span style="white-space:nowrap;">'.e($securityMoney->date->format('d M, Y')).'</span>';
            })
            ->editColumn('receiver_name', function (SecurityMoney $securityMoney) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'.e($securityMoney->receiver_name).'</div>';
            })
            ->editColumn('amount', function (SecurityMoney $securityMoney) {
                return '<span style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-800); font-size:14px; white-space:nowrap;">'
                    .'৳'.number_format((float) $securityMoney->amount, 2)
                    .'</span>';
            })
            ->addColumn('status', function (SecurityMoney $securityMoney) {
                $badges = [
                    'paid' => ['bn' => 'প্রদত্ত', 'en' => 'Paid', 'bg' => 'var(--gold-100)', 'color' => 'var(--gold-ink)'],
                    'received' => ['bn' => 'গৃহীত', 'en' => 'Received', 'bg' => 'var(--green-100)', 'color' => 'var(--green-ink)'],
                ];
                $badge = $badges[$securityMoney->status] ?? $badges['paid'];

                return '<span style="display:inline-block; font-size:11.5px; padding:3px 8px; border-radius:6px; font-weight:600; background:'.$badge['bg'].'; color:'.$badge['color'].'; white-space:nowrap;">'
                    .'<span class="bn">'.e($badge['bn']).'</span><span class="en" style="display:none;">'.e($badge['en']).'</span></span>';
            })
            ->editColumn('note', function (SecurityMoney $securityMoney) {
                if (! $securityMoney->note) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<div style="font-size:12.5px; color:var(--ink-600); max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($securityMoney->note).'">'.e($securityMoney->note).'</div>';
            })
            ->addColumn('action', function (SecurityMoney $securityMoney) {
                return view('financemanagement::security-money.datatables-actions', compact('securityMoney'))->render();
            })
            ->filterColumn('receiver_name', function ($query, $keyword) {
                $query->where('security_money.receiver_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('date', function ($query, $keyword) {
                $query->where('security_money.date', 'like', "%{$keyword}%");
            })
            ->filterColumn('amount', function ($query, $keyword) {
                $query->where('security_money.amount', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('security_money.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['date', 'receiver_name', 'amount', 'status', 'note', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SecurityMoney>
     */
    public function query(SecurityMoney $model): QueryBuilder
    {
        $query = $model->newQuery()->select([
            'security_money.id',
            'security_money.shop_id',
            'security_money.account_id',
            'security_money.receiver_name',
            'security_money.date',
            'security_money.amount',
            'security_money.status',
            'security_money.note',
            'security_money.created_at',
        ]);

        if ($status = request('status')) {
            $query->where('security_money.status', $status);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->orderBy(1, 'desc')
            ->minifiedAjax('', '
                data.status = $("#filter-security-money-status").val();
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
            Column::make('receiver_name')
                ->title('<span class="bn">গ্রহীতা</span><span class="en">Receiver</span>')
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
        return 'SecurityMoney_'.date('YmdHis');
    }
}
