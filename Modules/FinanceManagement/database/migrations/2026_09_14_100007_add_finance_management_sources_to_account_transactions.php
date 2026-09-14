<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The account_transactions.source enum values, before this migration adds to them.
     *
     * @var string[]
     */
    private array $existingSources = [
        'opening_balance',
        'sale',
        'purchase',
        'income',
        'expense',
        'sale_return',
        'purchase_return',
        'transfer_in',
        'transfer_out',
        'manual_adjustment',
    ];

    /**
     * The Finance Management source values introduced by this module.
     *
     * @var string[]
     */
    private array $newSources = [
        'debt_received',
        'debt_repaid',
        'lend_given',
        'lend_repaid',
        'security_money_paid',
        'security_money_received',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('source', array_merge($this->existingSources, $this->newSources))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('source', $this->existingSources)->change();
        });
    }
};
