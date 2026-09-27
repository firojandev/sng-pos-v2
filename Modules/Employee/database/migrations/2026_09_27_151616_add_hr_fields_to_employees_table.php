<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Employees become company records (their shop is where they work) with a
 * full HR profile. Existing employees get their company, an employee code,
 * and departments/designations created from the text entered so far.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->string('employee_code', 30)->nullable()->after('company_id');
            $table->foreignId('department_id')->nullable()->after('department')->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('designation')->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->after('designation_id')->constrained()->nullOnDelete();
            $table->enum('employment_type', ['permanent', 'probation', 'contract', 'casual'])->default('permanent')->after('shift_id');
            $table->date('confirmation_date')->nullable()->after('joining_date');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('nid', 30)->nullable();
            $table->string('tin', 30)->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('mfs_number', 20)->nullable();
            $table->string('device_user_id', 30)->nullable();
            $table->date('separation_date')->nullable();
            $table->string('separation_reason')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });

        $defaultCompanyId = DB::table('companies')->where('id', 1)->value('id') ?? DB::table('companies')->orderBy('id')->value('id');

        DB::table('employees')->whereNotNull('shop_id')->update([
            'company_id' => DB::raw('(SELECT shops.company_id FROM shops WHERE shops.id = employees.shop_id)'),
        ]);
        if ($defaultCompanyId) {
            DB::table('employees')->whereNull('company_id')->update(['company_id' => $defaultCompanyId]);
        }

        foreach (DB::table('employees')->orderBy('id')->get(['id', 'company_id', 'department', 'designation']) as $employee) {
            if (! $employee->company_id) {
                continue;
            }

            $sequence = DB::table('employees')->where('company_id', $employee->company_id)->whereNotNull('employee_code')->count() + 1;

            DB::table('employees')->where('id', $employee->id)->update([
                'employee_code' => 'EMP-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                'department_id' => $this->lookupId('departments', $employee->company_id, $employee->department),
                'designation_id' => $this->lookupId('designations', $employee->company_id, $employee->designation),
            ]);
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->unique(['company_id', 'employee_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Foreign keys first: the unique index may be backing company_id's.
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['shift_id']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'employee_code']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'company_id', 'employee_code', 'department_id', 'designation_id', 'shift_id', 'employment_type',
                'confirmation_date', 'gender', 'date_of_birth', 'blood_group', 'nid', 'tin', 'father_name',
                'mother_name', 'marital_status', 'permanent_address', 'emergency_contact_name',
                'emergency_contact_phone', 'bank_name', 'bank_account_no', 'mfs_number', 'device_user_id',
                'separation_date', 'separation_reason',
            ]);
        });
    }

    private function lookupId(string $table, int $companyId, ?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $id = DB::table($table)->where('company_id', $companyId)->where('name', $name)->value('id');

        return $id ?? DB::table($table)->insertGetId(['company_id' => $companyId, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
    }
};
