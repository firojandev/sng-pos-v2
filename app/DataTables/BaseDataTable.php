<?php

namespace App\DataTables;

use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Services\DataTable;

abstract class BaseDataTable extends DataTable
{
    /**
     * Get default configured DataTable HTML builder.
     */
    protected function defaultHtml(): HtmlBuilder
    {
        $firstIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon" style="display:inline-block; vertical-align:middle;"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>';
        $prevIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon" style="display:inline-block; vertical-align:middle;"><polyline points="15 18 9 12 15 6"/></svg>';
        $nextIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon" style="display:inline-block; vertical-align:middle;"><polyline points="9 18 15 12 9 6"/></svg>';
        $lastIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="app-icon" style="display:inline-block; vertical-align:middle;"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>';

        return $this->builder()
            ->setTableId($this->getTableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(true)
            ->autoWidth(false)
            ->parameters([
                'pageLength' => 10,
                'pagingType' => 'full_numbers',
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
                'language' => [
                    'search' => '<span class="bn">খুঁজুন:</span><span class="en">Search:</span>',
                    'searchPlaceholder' => 'এখানে লিখুন...',
                    'lengthMenu' => '<span class="bn">প্রতি পেজে</span><span class="en">Show</span> _MENU_ <span class="bn">টি রেকর্ড</span><span class="en">records</span>',
                    'info' => '<span class="bn">মোট _TOTAL_ টির মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে</span><span class="en">Showing _START_ to _END_ of _TOTAL_ entries</span>',
                    'infoEmpty' => '<span class="bn">কোনো রেকর্ড নেই</span><span class="en">No records available</span>',
                    'infoFiltered' => '<span class="bn">(_MAX_ টি রেকর্ড থেকে ফিল্টার করা)</span><span class="en">(filtered from _MAX_ total entries)</span>',
                    'zeroRecords' => '<span class="bn">কোনো ম্যাচিং রেকর্ড পাওয়া যায়নি</span><span class="en">No matching records found</span>',
                    'emptyTable' => '<span class="bn">টেবিলে কোনো তথ্য নেই</span><span class="en">No data available in table</span>',
                    'paginate' => [
                        'first' => $firstIcon,
                        'previous' => $prevIcon,
                        'next' => $nextIcon,
                        'last' => $lastIcon,
                    ],
                ],
                'dom' => '<"table-toolbar"<"table-toolbar-start"l<"dt-buttons"B>><"table-toolbar-end"f>>rt<"table-footer"<"table-pagination-info"i><"table-pagination"p>>',
            ])
            ->buttons([
                Button::make('excel')->text('Excel')->addClass('btn btn-soft-dark btn-xs'),
                Button::make('csv')->text('CSV')->addClass('btn btn-soft-dark btn-xs'),
                Button::make('print')->text('Print')->addClass('btn btn-soft-dark btn-xs'),
                Button::make('reload')->text('Reload')->addClass('btn btn-soft-teal btn-xs'),
            ]);
    }

    /**
     * Get default table DOM identifier.
     */
    protected function getTableId(): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', class_basename($this)));
    }
}
