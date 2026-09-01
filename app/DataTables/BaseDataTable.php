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
        return $this->builder()
            ->setTableId($this->getTableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->responsive(true)
            ->autoWidth(false)
            ->parameters([
                'pageLength' => 10,
                'lengthMenu' => [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'সকল (All)']],
                'language' => [
                    'search' => 'খুঁজুন / Search:',
                    'searchPlaceholder' => 'এখানে লিখুন...',
                    'lengthMenu' => 'প্রতি পেজে _MENU_ টি রেকর্ড',
                    'info' => 'মোট _TOTAL_ টির মধ্যে _START_ থেকে _END_ দেখানো হচ্ছে',
                    'infoEmpty' => 'কোনো রেকর্ড নেই',
                    'infoFiltered' => '(_MAX_ টি রেকর্ড থেকে ফিল্টার করা)',
                    'zeroRecords' => 'কোনো ম্যাচিং রেকর্ড পাওয়া যায়নি',
                    'emptyTable' => 'টেবিলে কোনো তথ্য নেই',
                    'paginate' => [
                        'first' => 'প্রথম',
                        'previous' => 'পূর্ববর্তী',
                        'next' => 'পরবর্তী',
                        'last' => 'সর্বশেষ',
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
