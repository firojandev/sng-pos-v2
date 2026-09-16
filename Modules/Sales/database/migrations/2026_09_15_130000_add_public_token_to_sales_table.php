<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('sales', 'public_token')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->string('public_token', 64)->nullable()->unique()->after('invoice_no');
            });
        }

        // Backfill existing records with unique tokens
        $sales = DB::table('sales')->whereNull('public_token')->select('id')->get();
        foreach ($sales as $sale) {
            DB::table('sales')->where('id', $sale->id)->update([
                'public_token' => Str::random(32),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sales', 'public_token')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('public_token');
            });
        }
    }
};
