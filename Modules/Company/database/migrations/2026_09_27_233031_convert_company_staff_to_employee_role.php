<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Default Company staff become company employees (a company-level login);
 * "Member" is left for shop users linked to a company.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultIds = DB::table('companies')->where('type', 'default')->pluck('id');

        DB::table('company_user')->whereIn('company_id', $defaultIds)->where('role', 'Member')->update(['role' => 'Employee']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $defaultIds = DB::table('companies')->where('type', 'default')->pluck('id');

        DB::table('company_user')->whereIn('company_id', $defaultIds)->where('role', 'Employee')->update(['role' => 'Member']);
    }
};
