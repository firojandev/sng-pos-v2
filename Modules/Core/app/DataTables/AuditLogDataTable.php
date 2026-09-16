<?php

namespace Modules\Core\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Core\Models\AuditLog;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class AuditLogDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<AuditLog>  $query
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('created_at', function (AuditLog $log) {
                $time = $log->created_at ? $log->created_at->format('h:i A') : '—';
                $date = $log->created_at ? $log->created_at->format('d M, Y') : '—';
                $diff = $log->created_at ? $log->created_at->diffForHumans() : '';

                return '<div style="display:flex; flex-direction:column; gap:2px; white-space:nowrap;">'
                    .'<div style="display:flex; align-items:center; gap:5px; font-weight:600; font-size:12.5px; color:var(--ink-900);">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
                    .'<span>'.$date.' '.$time.'</span>'
                    .'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-left:17px;">'.$diff.'</div>'
                    .'</div>';
            })
            ->addColumn('user', function (AuditLog $log) {
                if (! $log->user) {
                    return '<div style="display:inline-flex; align-items:center; gap:6px; background:var(--paper-line); padding:3px 8px; border-radius:6px; white-space:nowrap;">'
                        .'<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6;"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>'
                        .'<span style="font-size:12px; font-weight:600; color:var(--ink-600);">সিস্টেম / System</span>'
                        .'</div>';
                }

                $user = $log->user;
                $initial = mb_substr($user->name, 0, 1);
                $avatar = $user->avatar_url
                    ? '<img src="'.e($user->avatar_url).'" alt="'.e($user->name).'" style="width:30px; height:30px; border-radius:6px; object-fit:cover; flex-shrink:0;">'
                    : '<div style="width:30px; height:30px; border-radius:6px; background:var(--teal-700); color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex-shrink:0;">'.e($initial).'</div>';

                $subtext = $user->email ?? $user->phone ?? 'ID #'.$user->id;

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:8px;">'
                    .$avatar
                    .'<div style="min-width:0; max-width:140px;">'
                    .'<div style="font-weight:600; color:var(--ink-900); font-size:12.5px; line-height:1.3; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="'.e($user->name).'">'.e($user->name).'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="'.e($subtext).'">'.e($subtext).'</div>'
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('auditable_type', function (AuditLog $log) {
                $meta = $log->modelMeta();
                $badgeColor = $meta['color'] ?? 'teal';

                return '<div style="display:flex; flex-direction:column; gap:3px;">'
                    .'<div>'
                    .Blade::render('<x-core::badge :color="$color" size="xs" :icon="$icon" :label="$bn" :label-en="$en" />', [
                        'color' => $badgeColor,
                        'icon' => $meta['icon'] ?? 'file-text',
                        'bn' => $meta['bn'],
                        'en' => $meta['en'],
                    ])
                    .'</div>'
                    .'<div style="font-size:11px; font-family:var(--font-mono, monospace); color:var(--ink-500); display:flex; align-items:center; gap:3px;">'
                    .'<span>ID: #'.e($log->auditable_id).'</span>'
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('action', function (AuditLog $log) {
                $actionInfo = $log->actionLabel();
                $badgeColor = $actionInfo['color'] ?? 'grey';

                return Blade::render('<x-core::badge :color="$color" size="xs" :dot="true" :label="$bn" :label-en="$en" />', [
                    'color' => $badgeColor,
                    'bn' => $actionInfo['bn'],
                    'en' => $actionInfo['en'],
                ]);
            })
            ->addColumn('changes_summary', function (AuditLog $log) {
                if ($log->action === 'updated') {
                    $newValues = $log->new_values ?? [];
                    $fieldCount = count($newValues);
                    $fields = array_keys($newValues);
                    $previewFields = array_slice($fields, 0, 3);
                    $previewPills = '';
                    foreach ($previewFields as $f) {
                        $previewPills .= '<span style="display:inline-block; font-size:10.5px; font-family:var(--font-mono, monospace); background:var(--paper); border:1px solid var(--border); padding:1px 5px; border-radius:4px; color:var(--ink-700); margin-right:3px;">'.e($f).'</span>';
                    }
                    if ($fieldCount > 3) {
                        $previewPills .= '<span style="font-size:10.5px; color:var(--ink-400);">+'.($fieldCount - 3).'</span>';
                    }

                    return '<div style="font-size:12px;">'
                        .'<div style="font-weight:600; color:var(--ink-800); margin-bottom:2px;">'
                        .'<span class="bn">'.$fieldCount.'টি ফিল্ড পরিবর্তিত</span>'
                        .'<span class="en" style="display:none;">'.$fieldCount.' field(s) changed</span>'
                        .'</div>'
                        .'<div style="display:flex; align-items:center; flex-wrap:wrap; gap:2px;">'.$previewPills.'</div>'
                        .'</div>';
                }

                if ($log->action === 'created') {
                    $fieldCount = count($log->new_values ?? []);

                    return '<div style="font-size:12px; color:var(--ink-700); display:flex; align-items:center; gap:4px;">'
                        .'<span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:var(--green-500, #10b981);"></span>'
                        .'<span class="bn">নতুন রেকর্ড ('.$fieldCount.'টি ফিল্ড)</span>'
                        .'<span class="en" style="display:none;">New record ('.$fieldCount.' fields)</span>'
                        .'</div>';
                }

                if ($log->action === 'deleted') {
                    $hasSnapshot = ! empty($log->old_values);
                    $sub = $hasSnapshot
                        ? '<span class="bn" style="font-size:11px; color:var(--ink-400);">স্ন্যাপশট সংরক্ষিত</span><span class="en" style="display:none; font-size:11px; color:var(--ink-400);">Snapshot saved</span>'
                        : '';

                    return '<div style="font-size:12px; color:var(--red-600);">'
                        .'<div><span class="bn">রেকর্ড মুছে ফেলা হয়েছে</span><span class="en" style="display:none;">Record deleted</span></div>'
                        .$sub
                        .'</div>';
                }

                if ($log->action === 'restored') {
                    return '<div style="font-size:12px; color:var(--teal-700);">'
                        .'<span class="bn">রেকর্ড পুনরুদ্ধার করা হয়েছে</span>'
                        .'<span class="en" style="display:none;">Record restored</span>'
                        .'</div>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('client_info', function (AuditLog $log) {
                $ip = $log->ip_address ? e($log->ip_address) : '—';
                $dev = $log->browserAndDevice();

                $ipBadge = $log->ip_address
                    ? '<span style="font-size:11px; font-family:var(--font-mono, monospace); background:var(--paper-line); border:1px solid var(--border); padding:1px 6px; border-radius:4px; color:var(--ink-700);">'.$ip.'</span>'
                    : '<span style="color:var(--ink-400); font-size:11px;">—</span>';

                $devText = ($dev['browser'] !== 'System / Unknown' || $dev['platform'] !== 'Unknown')
                    ? '<div style="font-size:10.5px; color:var(--ink-500); margin-top:2px; max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($dev['platform'].' · '.$dev['browser']).'">'.e($dev['platform'].' · '.$dev['browser']).'</div>'
                    : '';

                return '<div>'.$ipBadge.$devText.'</div>';
            })
            ->addColumn('action_btn', function (AuditLog $log) {
                $url = route('audit-log.show', $log->id);

                return '<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end;">'
                    .'<button type="button" class="btn btn-soft-teal btn-xs btn-view-audit-detail" data-url="'.e($url).'" data-id="'.$log->id.'" title="বিস্তারিত / View Details">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>'
                    .'<span class="bn">বিস্তারিত</span><span class="en" style="display:none;">Details</span>'
                    .'</button>'
                    .'</div>';
            })
            ->filter(function ($query) {
                // Real-time custom filters
                if ($model = request('filter_model')) {
                    if ($model !== 'all') {
                        $query->where('audit_logs.auditable_type', $model);
                    }
                }

                if ($action = request('filter_action')) {
                    if ($action !== 'all') {
                        $query->where('audit_logs.action', $action);
                    }
                }

                if ($userId = request('filter_user_id')) {
                    if ($userId !== 'all') {
                        $query->where('audit_logs.user_id', $userId);
                    }
                }

                if ($dateFrom = request('filter_date_from')) {
                    $query->whereDate('audit_logs.created_at', '>=', $dateFrom);
                }

                if ($dateTo = request('filter_date_to')) {
                    $query->whereDate('audit_logs.created_at', '<=', $dateTo);
                }

                // Global search bar
                if ($keyword = request('search.value')) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('audit_logs.auditable_id', 'like', "%{$keyword}%")
                            ->orWhere('audit_logs.auditable_type', 'like', "%{$keyword}%")
                            ->orWhere('audit_logs.action', 'like', "%{$keyword}%")
                            ->orWhere('audit_logs.ip_address', 'like', "%{$keyword}%")
                            ->orWhereHas('user', function ($uq) use ($keyword) {
                                $uq->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%");
                            });
                    });
                }
            }, true)
            ->orderColumn('created_at', function ($query, $order) {
                $query->orderBy('audit_logs.created_at', $order);
            })
            ->orderColumn('user', function ($query, $order) {
                $query->leftJoin('users', 'users.id', '=', 'audit_logs.user_id')
                    ->orderBy('users.name', $order);
            })
            ->orderColumn('auditable_type', function ($query, $order) {
                $query->orderBy('audit_logs.auditable_type', $order)
                    ->orderBy('audit_logs.auditable_id', $order);
            })
            ->orderColumn('action', function ($query, $order) {
                $query->orderBy('audit_logs.action', $order);
            })
            ->setRowId('id')
            ->setRowAttr([
                'class' => 'clickable-audit-row',
                'data-url' => fn (AuditLog $log) => route('audit-log.show', $log->id),
                'style' => 'cursor:pointer;',
            ])
            ->rawColumns(['created_at', 'user', 'auditable_type', 'action', 'changes_summary', 'client_info', 'action_btn']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<AuditLog>
     */
    public function query(AuditLog $model): QueryBuilder
    {
        return $model->newQuery()
            ->select([
                'audit_logs.id',
                'audit_logs.shop_id',
                'audit_logs.user_id',
                'audit_logs.auditable_type',
                'audit_logs.auditable_id',
                'audit_logs.action',
                'audit_logs.ip_address',
                'audit_logs.user_agent',
                'audit_logs.old_values',
                'audit_logs.new_values',
                'audit_logs.created_at',
            ])
            ->with(['user' => fn ($q) => $q->select(['id', 'name', 'email', 'avatar', 'phone'])]);
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('audit-log-table')
            ->minifiedAjax(
                '',
                'data.filter_model = $("#filter-model").val(); data.filter_action = $("#filter-action").val(); data.filter_user_id = $("#filter-user").val(); data.filter_date_from = $("#filter-date-from").val(); data.filter_date_to = $("#filter-date-to").val();'
            )
            ->parameters([
                'order' => [[0, 'desc']],
            ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('created_at')
                ->title('<span class="bn">তারিখ ও সময়</span><span class="en">Date &amp; Time</span>')
                ->width(180),
            Column::computed('user')
                ->title('<span class="bn">ব্যবহারকারী</span><span class="en">User</span>')
                ->width(190),
            Column::make('auditable_type')
                ->title('<span class="bn">রেকর্ড / মডিউল</span><span class="en">Record / Module</span>')
                ->width(180),
            Column::make('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->addClass('table-cell-center')
                ->width(100),
            Column::computed('changes_summary')
                ->title('<span class="bn">পরিবর্তন সারসংক্ষেপ</span><span class="en">Changes Summary</span>')
                ->width(220),
            Column::computed('client_info')
                ->title('<span class="bn">আইপি ও ডিভাইস</span><span class="en">IP &amp; Device</span>')
                ->width(150),
            Column::computed('action_btn')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(90)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AuditLog_'.date('YmdHis');
    }
}
