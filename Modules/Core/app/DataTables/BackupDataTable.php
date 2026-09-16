<?php

namespace Modules\Core\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Modules\Core\Services\DatabaseBackupService;
use Yajra\DataTables\CollectionDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class BackupDataTable extends BaseDataTable
{
    public function __construct(protected DatabaseBackupService $backupService) {}

    /**
     * Build the DataTable class.
     */
    public function dataTable(Collection $query): CollectionDataTable
    {
        return (new CollectionDataTable($query))
            ->addIndexColumn()
            ->editColumn('filename', function (array $item) {
                $icon = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color:var(--teal-600); flex-shrink:0;"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/><path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/></svg>';
                $extBadge = '<span style="font-size:10.5px; font-weight:700; text-transform:uppercase; background:var(--paper-line); color:var(--ink-700); padding:2px 7px; border-radius:4px; border:1px solid var(--border); font-family:var(--font-mono, monospace);">.'.e($item['extension']).'</span>';

                return '<div style="display:flex; align-items:center; gap:9px;">'
                    .$icon
                    .'<span style="font-weight:600; font-family:var(--font-mono, monospace); font-size:12.5px; color:var(--ink-900);">'.e($item['filename']).'</span>'
                    .$extBadge
                    .'</div>';
            })
            ->editColumn('size', function (array $item) {
                return '<div style="font-size:12px; font-weight:600; color:var(--ink-700); font-family:var(--font-mono, monospace); text-align:center;">'
                    .'<span style="background:var(--paper); border:1px solid var(--border); padding:2px 8px; border-radius:5px; display:inline-block;">'.e($item['size_formatted']).'</span>'
                    .'</div>';
            })
            ->editColumn('created_at', function (array $item) {
                $date = $item['created_at']->format('d M, Y');
                $time = $item['created_at']->format('h:i A');
                $diff = $item['age'];

                return '<div style="display:flex; flex-direction:column; gap:2px; white-space:nowrap;">'
                    .'<div style="display:flex; align-items:center; gap:5px; font-weight:600; font-size:12.5px; color:var(--ink-900);">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.6;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>'
                    .'<span>'.$date.' '.$time.'</span>'
                    .'</div>'
                    .'<div style="font-size:11px; color:var(--ink-500); margin-left:17px;">'.$diff.'</div>'
                    .'</div>';
            })
            ->addColumn('status', function (array $item) {
                return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সম্পন্ন" label-en="Ready" />');
            })
            ->addColumn('action', function (array $item) {
                $downloadUrl = route('backup.download', ['filename' => $item['filename']]);
                $deleteUrl = route('backup.destroy', ['filename' => $item['filename']]);

                $inspectUrl = route('backup.inspect', ['filename' => $item['filename']]);
                $restoreUrl = route('backup.restore', ['filename' => $item['filename']]);

                $restoreBtn = '<button type="button" class="btn btn-soft-primary btn-xs btn-open-restore-modal" data-inspect-url="'.e($inspectUrl).'" data-restore-url="'.e($restoreUrl).'" data-filename="'.e($item['filename']).'" data-size="'.e($item['size_formatted']).'" data-date="'.e($item['created_at_formatted']).'" title="পুনরুদ্ধার / Restore" style="display:inline-flex; align-items:center; gap:4px;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>'
                    .'<span class="bn">রিস্টোর</span><span class="en" style="display:none;">Restore</span>'
                    .'</button>';

                $downloadBtn = '<a href="'.e($downloadUrl).'" class="btn btn-soft-teal btn-xs" title="ডাউনলোড / Download" style="display:inline-flex; align-items:center; gap:4px;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>'
                    .'<span class="bn">ডাউনলোড</span><span class="en" style="display:none;">Download</span>'
                    .'</a>';

                $deleteBtn = '<button type="button" class="btn btn-soft-danger btn-xs btn-delete-backup" data-url="'.e($deleteUrl).'" data-filename="'.e($item['filename']).'" title="মুছে ফেলুন / Delete" style="display:inline-flex; align-items:center; gap:4px;">'
                    .'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>'
                    .'<span class="bn">ডিলিট</span><span class="en" style="display:none;">Delete</span>'
                    .'</button>';

                return '<div class="row-actions" style="display:flex; align-items:center; justify-content:flex-end; gap:6px;">'
                    .$restoreBtn
                    .$downloadBtn
                    .$deleteBtn
                    .'</div>';
            })
            ->rawColumns(['filename', 'size', 'created_at', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(): Collection
    {
        return $this->backupService->getBackups();
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->setTableId('backup-data-table')
            ->orderBy([3, 'desc'])
            ->buttons([
                Button::make('reload')->text('Reload')->addClass('btn btn-soft-teal btn-xs'),
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
            Column::computed('DT_RowIndex')
                ->title('#')
                ->width(45)
                ->addClass('table-cell-center'),
            Column::make('filename')
                ->title('<span class="bn">ফাইলের নাম</span><span class="en">File Name</span>')
                ->searchable(true)
                ->orderable(true),
            Column::make('size')
                ->title('<span class="bn">সাইজ</span><span class="en">Size</span>')
                ->width(110)
                ->addClass('table-cell-center')
                ->orderable(true),
            Column::make('created_at')
                ->title('<span class="bn">ব্যাকআপের সময়</span><span class="en">Date &amp; Time</span>')
                ->width(190)
                ->orderable(true),
            Column::computed('status')
                ->title('<span class="bn">স্ট্যাটাস</span><span class="en">Status</span>')
                ->width(90)
                ->addClass('table-cell-center'),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Actions</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(235)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'DatabaseBackup_'.date('YmdHis');
    }
}
