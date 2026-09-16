<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
            $table->string('logo')->nullable()->after('address');
            $table->text('invoice_footer')->nullable()->after('logo');
            $table->string('currency_symbol', 10)->default('৳')->after('invoice_footer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['email', 'logo', 'invoice_footer', 'currency_symbol']);
        });
    }
};
