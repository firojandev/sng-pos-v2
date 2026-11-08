<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Company roles: named sets of company-level permissions (HR, payroll,
 * accounting, tasks) a company admin gives to company employees. Shop
 * roles stay per shop for the POS.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('company_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('permissions');
            $table->timestamps();
            $table->unique(['company_id', 'name']);
        });

        Schema::table('company_user', function (Blueprint $table) {
            $table->foreignId('company_role_id')->nullable()->after('role')->constrained('company_roles')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_user', function (Blueprint $table) {
            $table->dropForeign(['company_role_id']);
            $table->dropColumn('company_role_id');
        });

        Schema::dropIfExists('company_roles');
    }
};
