<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;

class UsersDataTable extends BaseDataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder<User>  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'datatables.users-action')
            ->editColumn('created_at', function (User $user) {
                return $user->created_at ? $user->created_at->format('d M, Y') : '—';
            })
            ->rawColumns(['action'])
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<User>
     */
    public function query(User $model): QueryBuilder
    {
        return $model->newQuery()->select(['id', 'name', 'email', 'created_at']);
    }

    /**
     * Optional method if you want to use the html builder.
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
            Column::make('id')->title('#ID')->width(70),
            Column::make('name')->title('নাম (Name)'),
            Column::make('email')->title('ইমেইল (Email)'),
            Column::make('created_at')->title('তারিখ (Joined)')->width(130),
            Column::computed('action')
                ->title('অ্যাকশন (Action)')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('table-cell-right'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_'.date('YmdHis');
    }
}
