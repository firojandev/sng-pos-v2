<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Modules\Product\Models\ProductModel;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class ModelsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<ProductModel>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (ProductModel $model) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'
                    .e($model->name)
                    .'</div>';
            })
            ->addColumn('brand', function (ProductModel $model) {
                if ($model->brand) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'
                        .e($model->brand->name)
                        .'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('action', function (ProductModel $model) {
                return view('product::models.datatables-actions', compact('model'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('product_models.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('brand', function ($query, $keyword) {
                $query->whereHas('brand', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['name', 'brand', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<ProductModel>
     */
    public function query(ProductModel $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['brand'])
            ->select([
                'product_models.id',
                'product_models.shop_id',
                'product_models.brand_id',
                'product_models.name',
                'product_models.created_at',
            ]);

        if ($brandId = request('brand_id')) {
            $query->where('product_models.brand_id', $brandId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax('', 'data.brand_id = $("#filter-brand").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">মডেলের নাম</span><span class="en">Model Name</span>'),
            Column::computed('brand')->title('<span class="bn">ব্র্যান্ড</span><span class="en">Brand</span>')->width(240),
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
        return 'Models_'.date('YmdHis');
    }
}
