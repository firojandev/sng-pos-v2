<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Provident fund and income tax paid over from what payroll withheld. The
 * employer's PF share gets its own payable account, apart from the
 * employees' share.
 */
return new class extends Migration
{
    private array $existingSources = [
        'opening_balance', 'sale', 'purchase', 'income', 'expense', 'sale_return', 'purchase_return', 'transfer_in', 'transfer_out',
        'manual_adjustment', 'debt_received', 'debt_repaid', 'lend_given', 'lend_repaid', 'security_money_paid', 'security_money_received',
        'cash_in', 'cash_out', 'salary', 'employee_advance', 'employee_advance_repaid',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('statutory_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['pf', 'tax']);
            $table->date('period_from');
            $table->date('period_to');
            $table->decimal('employee_amount', 14, 2)->default(0);
            $table->decimal('employer_amount', 14, 2)->default(0);
            $table->decimal('amount', 14, 2);
            $table->string('payee')->nullable();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->string('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['company_id', 'type', 'period_from']);
        });

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('source', [...$this->existingSources, 'statutory_payment'])->change();
        });

        DB::table('ledger_accounts')->where('system_key', 'pf_payable')->update(['name' => 'কর্মচারীর পিএফ প্রদেয় (Employee PF Payable)']);
        DB::table('ledger_accounts')->where('system_key', 'tds_payable')->update(['name' => 'আয়কর প্রদেয় — উৎসে কর (Income Tax Payable)']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statutory_payments');

        Schema::table('account_transactions', function (Blueprint $table) {
            $table->enum('source', $this->existingSources)->change();
        });
    }
};
