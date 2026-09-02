<?php

namespace Tests\Feature;

use App\DataTables\UsersDataTable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class UserDataTableTest extends TestCase
{
    public function test_users_datatable_generates_html_builder(): void
    {
        $dataTable = new UsersDataTable;
        $html = $dataTable->html();

        $this->assertEquals('users-data-table', $html->getTableAttribute('id'));
        $this->assertCount(5, $dataTable->getColumns());
    }

    public function test_users_datatable_query_returns_query_builder(): void
    {
        $dataTable = new UsersDataTable;
        $query = $dataTable->query(new User);

        $this->assertInstanceOf(Builder::class, $query);
    }
}
