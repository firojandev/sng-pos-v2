<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A salary change also records the designation held before it, so an
 * employee's history (joining to current position) shows every designation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_revisions', function (Blueprint $table) {
            $table->foreignId('previous_designation_id')->nullable()->after('designation_id')->constrained('designations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salary_revisions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_designation_id');
        });
    }
};
