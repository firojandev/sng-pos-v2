<?php

namespace Modules\Product\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Product\Models\Product;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class ProductsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Product>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Product $product) {
                $avatar = '';
                if ($product->image_url) {
                    $avatar = '<img src="'.e($product->image_url).'" alt="" style="width:32px; height:32px; border-radius:8px; object-fit:cover; flex:0 0 auto;">';
                } else {
                    $initial = mb_substr($product->name, 0, 1);
                    $avatar = '<div class="av" style="width:32px; height:32px; border-radius:8px; background:var(--teal-800); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; flex:0 0 auto;">'.e($initial).'</div>';
                }

                $sizeHtml = $product->size ? ' <span style="font-size:11.5px; color:var(--ink-500); font-weight:500;">('.e($product->size).')</span>' : '';

                return '<div class="row-avatar" style="display:flex; align-items:center; gap:10px;">'
                    .$avatar
                    .'<div><div style="font-weight:700; color:var(--ink-900); font-size:13.5px;">'
                    .e($product->name)
                    .$sizeHtml
                    .'</div></div></div>';
            })
            ->editColumn('sku', function (Product $product) {
                return '<span style="font-family:var(--font-mono, monospace); font-size:12.5px; font-weight:600; color:var(--ink-700);">'
                    .e($product->sku)
                    .'</span>';
            })
            ->addColumn('category', function (Product $product) {
                if ($product->category) {
                    $catName = e($product->category->name);
                    $subHtml = $product->subCategory ? ' <span style="color:var(--ink-500); font-size:11.5px;">/ '.e($product->subCategory->name).'</span>' : '';

                    return '<div style="font-weight:600; color:var(--ink-800); font-size:13px;">'.$catName.$subHtml.'</div>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('brand', function (Product $product) {
                if ($product->brand) {
                    return '<span style="font-weight:600; color:var(--ink-800); font-size:13px;">'
                        .e($product->brand->name)
                        .'</span>';
                }

                return '<span style="color:var(--ink-400);">—</span>';
            })
            ->addColumn('price', function (Product $product) {
                $sale = '৳'.number_format((float) $product->sale_price, 2);
                $purchase = '৳'.number_format((float) $product->purchase_price, 2);

                return '<div style="font-weight:700; color:var(--teal-700); font-size:13px;">'.$sale.'</div>'
                    .'<div style="font-size:11.5px; color:var(--ink-500);">ক্রয়: '.$purchase.'</div>';
            })
            ->addColumn('units', function (Product $product) {
                $units = $product->units->pluck('short_code')->implode(', ');

                return $units ?: '<span style="color:var(--ink-400);">—</span>';
            })
            ->editColumn('is_vat', function (Product $product) {
                if ($product->is_vat) {
                    $vat = rtrim(rtrim(number_format((float) $product->vat_percentage, 2), '0'), '.');

                    return Blade::render('<x-core::badge color="teal" size="xs" variant="soft">{{ $vat }}%</x-core::badge>', ['vat' => $vat]);
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নেই" label-en="None" />');
            })
            ->editColumn('status', function (Product $product) {
                if ($product->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Product $product) {
                return view('product::products.datatables-actions', compact('product'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('products.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('sku', function ($query, $keyword) {
                $query->where('products.sku', 'like', "%{$keyword}%");
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('brand', function ($query, $keyword) {
                $query->whereHas('brand', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('products.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'sku', 'category', 'brand', 'price', 'units', 'is_vat', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Product>
     */
    public function query(Product $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->with(['category', 'subCategory', 'brand', 'units'])
            ->select([
                'products.id',
                'products.shop_id',
                'products.category_id',
                'products.sub_category_id',
                'products.brand_id',
                'products.name',
                'products.sku',
                'products.size',
                'products.image_url',
                'products.purchase_price',
                'products.sale_price',
                'products.is_vat',
                'products.vat_percentage',
                'products.status',
                'products.created_at',
            ]);

        if ($categoryId = request('category_id')) {
            $query->where('products.category_id', $categoryId);
        }

        if ($brandId = request('brand_id')) {
            $query->where('products.brand_id', $brandId);
        }

        if ($status = request('status')) {
            if ($status !== 'all') {
                $query->where('products.status', $status);
            }
        }

        return $query;
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml()
            ->minifiedAjax('', 'data.category_id = $("#filter-category").val(); data.brand_id = $("#filter-brand").val(); data.status = $("#filter-status").val();');
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">পণ্য</span><span class="en">Product</span>')->width(220),
            Column::make('sku')->title('<span class="bn">SKU</span><span class="en">SKU</span>')->width(120),
            Column::computed('category')->title('<span class="bn">ক্যাটাগরি</span><span class="en">Category</span>')->width(160),
            Column::computed('brand')->title('<span class="bn">ব্র্যান্ড</span><span class="en">Brand</span>')->width(130),
            Column::computed('price')->title('<span class="bn">মূল্য</span><span class="en">Price</span>')->width(130),
            Column::computed('units')->title('<span class="bn">ইউনিট</span><span class="en">Units</span>')->width(100),
            Column::make('is_vat')->title('<span class="bn">ভ্যাট</span><span class="en">VAT</span>')->addClass('table-cell-center')->width(80),
            Column::make('status')->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')->addClass('table-cell-center')->width(90),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(125)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Products_'.date('YmdHis');
    }
}
