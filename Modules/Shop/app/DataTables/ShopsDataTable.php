<?php

namespace Modules\Shop\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Shop\Models\Shop;
use Modules\Shop\Models\Subscription;
use Revoltify\Subscriptionify\Enums\SubscriptionStatus;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class ShopsDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Shop>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Shop $shop) {
                $slug = e($shop->slug);
                $name = e($shop->name);
                $storeCode = $shop->store_code ? e($shop->store_code) : null;
                $slugBadge = Blade::render('<x-core::badge color="grey" size="xs" variant="outline">{{ $slug }}</x-core::badge>', ['slug' => $slug]);
                $codeBadge = $storeCode ? Blade::render('<x-core::badge color="teal" size="xs" variant="soft">#{{ $code }}</x-core::badge>', ['code' => $storeCode]) : '';

                return '<div style="display:flex; flex-direction:column; align-items:flex-start; gap:4px;">'
                    .'<span style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.2;">'.$name.'</span>'
                    .'<div style="display:flex; align-items:center; gap:4px; flex-wrap:wrap;">'
                    .$slugBadge
                    .$codeBadge
                    .'</div>'
                    .'</div>';
            })
            ->editColumn('phone', function (Shop $shop) {
                if (! $shop->phone) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-family:var(--font-mono, monospace); font-size:12.5px; color:var(--ink-700);">'
                    .e($shop->phone)
                    .'</span>';
            })
            ->addColumn('subscription', function (Shop $shop) {
                $subscription = $shop->activeSubscription;
                if ($subscription && $subscription->plan) {
                    $planName = e($subscription->plan->name);
                    $statusKey = $subscription->status instanceof SubscriptionStatus
                        ? $subscription->status->value
                        : (string) $subscription->status;

                    $color = match ($statusKey) {
                        'active' => 'teal',
                        'trialing', 'trial' => 'blue',
                        'past_due' => 'gold',
                        default => 'grey',
                    };

                    $label = Subscription::statusLabels()[$statusKey]['bn'] ?? $statusKey;
                    $statusBadge = Blade::render('<x-core::badge :color="$color" size="xs" variant="soft">{{ $label }}</x-core::badge>', ['color' => $color, 'label' => $label]);

                    return '<div style="display:flex; flex-direction:column; align-items:flex-start; gap:3px;">'
                        .'<span style="font-weight:600; font-size:13px; color:var(--ink-800);">'.$planName.'</span>'
                        .$statusBadge
                        .'</div>';
                }

                return '<span style="color:var(--ink-400); font-size:12px;">—</span>';
            })
            ->editColumn('admins_count', function (Shop $shop) {
                $count = (int) ($shop->admins_count ?? 0);

                return Blade::render('<x-core::badge color="teal" size="xs" variant="soft" :dot="true">{{ $count }}</x-core::badge>', ['count' => $count]);
            })
            ->editColumn('enabled_features', function (Shop $shop) {
                $count = is_array($shop->enabled_features) ? count($shop->enabled_features) : 0;

                return Blade::render('<x-core::badge color="grey" size="xs" variant="outline">{{ $count }} টি</x-core::badge>', ['count' => $count]);
            })
            ->editColumn('status', function (Shop $shop) {
                if ($shop->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Shop $shop) {
                return view('shop::datatables-actions', compact('shop'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('shops.name', 'like', "%{$keyword}%")
                        ->orWhere('shops.slug', 'like', "%{$keyword}%")
                        ->orWhere('shops.store_code', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('phone', function ($query, $keyword) {
                $query->where('shops.phone', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('shops.status', 'like', "%{$keyword}%");
            })
            ->filterColumn('subscription', function ($query, $keyword) {
                $query->whereHas('activeSubscription.plan', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['name', 'phone', 'subscription', 'admins_count', 'enabled_features', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Shop>
     */
    public function query(Shop $model): QueryBuilder
    {
        return $model->newQuery()
            ->withCount('admins')
            ->with(['activeSubscription.plan'])
            ->select([
                'shops.id',
                'shops.name',
                'shops.slug',
                'shops.store_code',
                'shops.phone',
                'shops.address',
                'shops.status',
                'shops.enabled_features',
                'shops.created_at',
            ]);
    }

    /**
     * Configure HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->defaultHtml();
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('name')->title('<span class="bn">দোকানের নাম</span><span class="en">Shop Name</span>')->width(200),
            Column::make('phone')->title('<span class="bn">মোবাইল</span><span class="en">Phone</span>')->width(130),
            Column::computed('subscription')->title('<span class="bn">সাবস্ক্রিপশন</span><span class="en">Subscription</span>')->width(140),
            Column::make('admins_count')->title('<span class="bn">এডমিন</span><span class="en">Admins</span>')->addClass('table-cell-center')->width(80)->searchable(false),
            Column::make('enabled_features')->title('<span class="bn">ফিচার</span><span class="en">Features</span>')->addClass('table-cell-center')->width(90)->orderable(false)->searchable(false),
            Column::make('status')->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')->addClass('table-cell-center')->width(90),
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
        return 'Shops_'.date('YmdHis');
    }
}
