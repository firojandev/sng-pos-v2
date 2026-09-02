<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\SubCategory;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class SubCategoriesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<SubCategory>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (SubCategory $subCategory) {
                return '<div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'
                    .e($subCategory->name)
                    .'</div>';
            })
            ->addColumn('parent_category', function (SubCategory $subCategory) {
                if ($subCategory->category) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'
                        .e($subCategory->category->name)
                        .'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('products_count', function (SubCategory $subCategory) {
                $count = (int) ($subCategory->products_count ?? 0);

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft">{{ $count }} টি</x-core::badge>', ['count' => $count]);
            })
            ->editColumn('description', function (SubCategory $subCategory) {
                if (! $subCategory->description) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="color:var(--ink-700); font-size:13px;">'.e($subCategory->description).'</span>';
            })
            ->addColumn('action', function (SubCategory $subCategory) {
                return view('product::sub-categories.datatables-actions', compact('subCategory'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('categories.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('parent_category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('description', function ($query, $keyword) {
                $query->where('categories.description', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'parent_category', 'products_count', 'description', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<SubCategory>
     */
    public function query(SubCategory $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['category'])
            ->withCount('products')
            ->select([
                'categories.id',
                'categories.shop_id',
                'categories.parent_id',
                'categories.type',
                'categories.name',
                'categories.description',
                'categories.created_at',
            ]);

        if ($parentId = request('parent_id')) {
            $query->where('categories.parent_id', $parentId);
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax('', 'data.parent_id = $("#filter-parent-category").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">নাম</span><span class="en">Name</span>')->width(180),
            Column::computed('parent_category')->title('<span class="bn">মূল ক্যাটাগরি</span><span class="en">Parent Category</span>')->width(180),
            Column::make('products_count')->title('<span class="bn">পণ্য সংখ্যা</span><span class="en">Products</span>')->addClass('table-cell-center')->width(120)->searchable(false),
            Column::make('description')->title('<span class="bn">বিবরণ</span><span class="en">Description</span>'),
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
        return 'SubCategories_'.date('YmdHis');
    }
}
