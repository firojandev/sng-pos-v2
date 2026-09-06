<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\StockTransfer;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class StockTransfersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<StockTransfer>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('transfer_no', function (StockTransfer $transfer) {
                $no = e($transfer->transfer_no ?: ('#TR-'.str_pad((string) $transfer->id, 4, '0', STR_PAD_LEFT)));
                $date = $transfer->created_at ? $transfer->created_at->format('d M, Y') : '';
                $requester = $transfer->requestedBy ? e($transfer->requestedBy->name) : '—';
                $url = route('stock-transfers.show', $transfer);

                return '<div style="display:flex; align-items:flex-start; gap:10px;">'
                    .'<div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">'
                    .'<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h11M15 7l-3-3M15 7l-3 3"/><path d="M20 17H9M9 17l3-3M9 17l3 3"/></svg>'
                    .'</div>'
                    .'<div style="min-width:0;">'
                    .'<a href="'.$url.'" class="btn-view-transfer-detail" data-url="'.$url.'" style="font-weight:700; font-family:var(--font-mono, monospace); color:var(--teal-800); font-size:13px; text-decoration:none; cursor:pointer;">'
                    .$no
                    .'</a>'
                    .'<div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">'
                    .$requester.' &middot; '.$date
                    .'</div>'
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('from_warehouse', function (StockTransfer $transfer) {
                if ($transfer->fromWarehouse) {
                    $branch = $transfer->fromWarehouse->branch ? '<div style="font-size:11px; color:var(--ink-400); margin-top:2px;">'.e($transfer->fromWarehouse->branch->name).'</div>' : '';

                    return '<div style="font-weight:600; color:var(--ink-800); font-size:12.5px;">'
                        .e($transfer->fromWarehouse->name)
                        .'</div>'.$branch;
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('to_warehouse', function (StockTransfer $transfer) {
                if ($transfer->toWarehouse) {
                    $branch = $transfer->toWarehouse->branch ? '<div style="font-size:11px; color:var(--ink-400); margin-top:2px;">'.e($transfer->toWarehouse->branch->name).'</div>' : '';

                    return '<div style="font-weight:600; color:var(--ink-800); font-size:12.5px;">'
                        .e($transfer->toWarehouse->name)
                        .'</div>'.$branch;
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('items_summary', function (StockTransfer $transfer) {
                $count = (int) ($transfer->items_count ?? 0);
                $qty = (float) ($transfer->total_quantity ?? 0);
                $formattedQty = rtrim(rtrim(number_format($qty, 2), '0'), '.');

                return '<div style="font-size:12px;">'
                    .'<span style="display:inline-block; font-weight:700; font-size:11.5px; padding:2px 8px; border-radius:6px; background:var(--paper-line); color:var(--ink-800); border:1px solid var(--border);">'
                    .$count.' টি আইটেম'
                    .'</span>'
                    .'<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-500); margin-top:3px;">'
                    .'মোট: <b>'.$formattedQty.'</b> একক'
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('status', function (StockTransfer $transfer) {
                $label = $transfer->statusLabel();

                return match ($transfer->status) {
                    'received' => Blade::render('<x-core::badge color="green" size="xs" :dot="true">{{ $label }}</x-core::badge>', ['label' => $label['bn']]),
                    'cancelled' => Blade::render('<x-core::badge color="red" size="xs" :dot="true">{{ $label }}</x-core::badge>', ['label' => $label['bn']]),
                    'dispatched' => Blade::render('<x-core::badge color="blue" size="xs" :dot="true">{{ $label }}</x-core::badge>', ['label' => $label['bn']]),
                    'approved' => Blade::render('<x-core::badge color="teal" size="xs" :dot="true">{{ $label }}</x-core::badge>', ['label' => $label['bn']]),
                    default => Blade::render('<x-core::badge color="gold" size="xs" :dot="true">{{ $label }}</x-core::badge>', ['label' => $label['bn']]),
                };
            })
            ->editColumn('created_at', function (StockTransfer $transfer) {
                return '<div style="font-size:12px; color:var(--ink-700); white-space:nowrap;">'
                    .($transfer->created_at ? $transfer->created_at->format('d M, Y') : '—')
                    .'<div style="font-size:11px; color:var(--ink-400); margin-top:2px;">'
                    .($transfer->created_at ? $transfer->created_at->format('h:i A') : '')
                    .'</div>'
                    .'</div>';
            })
            ->addColumn('action', function (StockTransfer $transfer) {
                return view('product::stock-transfers.datatables-actions', compact('transfer'))->render();
            })
            ->filter(function ($query) {
                if ($keyword = request('search.value')) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('stock_transfers.transfer_no', 'like', "%{$keyword}%")
                            ->orWhere('stock_transfers.note', 'like', "%{$keyword}%")
                            ->orWhereHas('fromWarehouse', fn ($wq) => $wq->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('toWarehouse', fn ($wq) => $wq->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('requestedBy', fn ($uq) => $uq->where('name', 'like', "%{$keyword}%"))
                            ->orWhereHas('items.product', fn ($pq) => $pq->where('name', 'like', "%{$keyword}%")->orWhere('sku', 'like', "%{$keyword}%"));
                    });
                }
            }, true)
            ->orderColumn('transfer_no', function ($query, $order) {
                $query->orderBy('stock_transfers.transfer_no', $order);
            })
            ->orderColumn('from_warehouse', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT name FROM warehouses WHERE warehouses.id = stock_transfers.from_warehouse_id), "") '.$order);
            })
            ->orderColumn('to_warehouse', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT name FROM warehouses WHERE warehouses.id = stock_transfers.to_warehouse_id), "") '.$order);
            })
            ->orderColumn('items_summary', function ($query, $order) {
                $query->orderByRaw('COALESCE((SELECT COUNT(*) FROM stock_transfer_items WHERE stock_transfer_items.stock_transfer_id = stock_transfers.id), 0) '.$order);
            })
            ->orderColumn('status', function ($query, $order) {
                $query->orderBy('stock_transfers.status', $order);
            })
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('stock_transfers.created_at', $order);
            })
            ->rawColumns(['transfer_no', 'from_warehouse', 'to_warehouse', 'items_summary', 'status', 'created_at', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<StockTransfer>
     */
    public function query(StockTransfer $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['fromWarehouse.branch', 'toWarehouse.branch', 'requestedBy', 'approvedBy', 'dispatchedBy', 'receivedBy', 'items.product'])
            ->select([
                'stock_transfers.id',
                'stock_transfers.shop_id',
                'stock_transfers.transfer_no',
                'stock_transfers.from_warehouse_id',
                'stock_transfers.to_warehouse_id',
                'stock_transfers.status',
                'stock_transfers.requested_by',
                'stock_transfers.approved_by',
                'stock_transfers.dispatched_by',
                'stock_transfers.received_by',
                'stock_transfers.approved_at',
                'stock_transfers.dispatched_at',
                'stock_transfers.received_at',
                'stock_transfers.note',
                'stock_transfers.created_at',
            ])
            ->withCount('items as items_count')
            ->withSum('items as total_quantity', 'quantity');

        if ($status = request('status')) {
            if ($status !== 'all' && array_key_exists($status, StockTransfer::statusLabels())) {
                $query->where('stock_transfers.status', $status);
            }
        }

        if ($fromId = request('from_warehouse_id')) {
            $query->where('stock_transfers.from_warehouse_id', $fromId);
        }

        if ($toId = request('to_warehouse_id')) {
            $query->where('stock_transfers.to_warehouse_id', $toId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('stock-transfers-data-table')
            ->minifiedAjax('', '
                data.status = $("#filter-status").val();
                data.from_warehouse_id = $("#filter-from-warehouse").val();
                data.to_warehouse_id = $("#filter-to-warehouse").val();
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
                ->title('<span class="bn">ট্রান্সফার নং</span><span class="en">Transfer No</span>')
                ->width(180),
            Column::computed('from_warehouse')
                ->title('<span class="bn">উৎস গুদাম (হতে)</span><span class="en">From</span>')
                ->width(170),
            Column::computed('to_warehouse')
                ->title('<span class="bn">গন্তব্য গুদাম (প্রতি)</span><span class="en">To</span>')
                ->width(170),
            Column::computed('items_summary')
                ->title('<span class="bn">আইটেম ও পরিমাণ</span><span class="en">Items &amp; Qty</span>')
                ->width(140),
            Column::make('status')
                ->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')
                ->addClass('table-cell-center')
                ->width(120),
            Column::make('created_at')
                ->title('<span class="bn">তারিখ</span><span class="en">Date</span>')
                ->width(120),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(140)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'StockTransfers_'.date('YmdHis');
    }
}
