<?php

namespace Modules\Company\DataTables;

use App\DataTables\BaseDataTable;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Blade;
use Modules\Company\Models\Company;
use Revoltify\Subscriptionify\Enums\SubscriptionStatus;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class CompaniesDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<Company>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('name', function (Company $company) {
                $name = e($company->name);
                $slugBadge = $company->isDefault()
                    ? Blade::render('<x-core::badge color="gold" size="xs" label="একক দোকানসমূহ" label-en="Standalone shops" />')
                    : Blade::render('<x-core::badge color="grey" size="xs" variant="outline">{{ $slug }}</x-core::badge>', ['slug' => $company->slug]);

                return '<div style="display:flex; flex-direction:column; align-items:flex-start; gap:4px; max-width:220px;">'
                    .'<span style="font-weight:700; color:var(--ink-900); font-size:13.5px; line-height:1.2; max-width:220px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.$name.'">'.$name.'</span>'
                    .$slugBadge
                    .'</div>';
            })
            ->addColumn('owners', function (Company $company) {
                if ($company->owners->isEmpty()) {
                    return '<span style="color:var(--ink-400);">—</span>';
                }

                return '<span style="font-size:12.5px; color:var(--ink-700);">'.e($company->owners->pluck('name')->join(', ')).'</span>';
            })
            ->editColumn('shops_count', function (Company $company) {
                $count = $company->isDefault() ? (int) $company->standalone_count : (int) $company->shops_count;
                $color = $count > 1 ? 'teal' : 'grey';

                return '<span style="white-space:nowrap;">'.Blade::render('<x-core::badge :color="$color" size="xs" variant="soft" :dot="true">{{ $count }}</x-core::badge>', ['color' => $color, 'count' => $count]).'</span>';
            })
            ->addColumn('subscription', function (Company $company) {
                $subscription = $company->activeSubscription;
                if (! $subscription || ! $subscription->plan) {
                    return '<span style="color:var(--ink-400); font-size:12px;">—</span>';
                }

                $statusKey = $subscription->status instanceof SubscriptionStatus
                    ? $subscription->status->value
                    : (string) $subscription->status;
                $color = match ($statusKey) {
                    'active' => 'teal',
                    'trialing', 'trial' => 'blue',
                    default => 'grey',
                };
                $statusBadge = Blade::render('<x-core::badge :color="$color" size="xs" variant="soft">{{ $label }}</x-core::badge>', [
                    'color' => $color,
                    'label' => $subscription->statusLabel()['bn'] ?? $statusKey,
                ]);

                return '<div style="display:flex; flex-direction:column; align-items:flex-start; gap:3px;">'
                    .'<span style="font-weight:600; font-size:13px; color:var(--ink-800);">'.e($subscription->plan->name).'</span>'
                    .'<span style="white-space:nowrap;">'.$statusBadge.'</span>'
                    .'</div>';
            })
            ->editColumn('status', function (Company $company) {
                if ($company->status === 'active') {
                    return Blade::render('<x-core::badge color="green" size="xs" :dot="true" label="সক্রিয়" label-en="Active" />');
                }

                return Blade::render('<x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />');
            })
            ->addColumn('action', function (Company $company) {
                return view('company::datatables-actions', compact('company'))->render();
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('companies.name', 'like', "%{$keyword}%")
                        ->orWhere('companies.slug', 'like', "%{$keyword}%")
                        ->orWhereHas('shops', fn ($shops) => $shops->where('shops.name', 'like', "%{$keyword}%"));
                });
            })
            ->filterColumn('owners', function ($query, $keyword) {
                $query->whereHas('owners', fn ($owners) => $owners->where('users.name', 'like', "%{$keyword}%"));
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('companies.status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['name', 'owners', 'shops_count', 'subscription', 'status', 'action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<Company>
     */
    public function query(Company $model): QueryBuilder
    {
        // Standalone shops' private records are listed under the Default Company.
        return $model->newQuery()
            ->where('companies.type', '!=', Company::TYPE_STANDALONE)
            ->with(['activeSubscription.plan', 'owners'])
            ->withCount(['shops', 'children as standalone_count' => fn ($query) => $query->where('type', Company::TYPE_STANDALONE)])
            ->latest('companies.id');
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
            Column::make('name')->title('<span class="bn">কোম্পানির নাম</span><span class="en">Company</span>')->width(220),
            Column::computed('owners')->title('<span class="bn">মালিক</span><span class="en">Owners</span>')->searchable(true)->width(180),
            Column::make('shops_count')->title('<span class="bn">দোকান</span><span class="en">Shops</span>')->addClass('table-cell-center')->width(80)->searchable(false),
            Column::computed('subscription')->title('<span class="bn">সাবস্ক্রিপশন</span><span class="en">Subscription</span>')->width(140),
            Column::make('status')->title('<span class="bn">অবস্থা</span><span class="en">Status</span>')->addClass('table-cell-center')->width(90),
            Column::computed('action')
                ->title('<span class="bn">অ্যাকশন</span><span class="en">Action</span>')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Companies_'.date('YmdHis');
    }
}
