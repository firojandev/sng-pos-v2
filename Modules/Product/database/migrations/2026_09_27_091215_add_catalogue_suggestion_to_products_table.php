<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A company can suggest one of its own products for the shared catalogue;
 * a Super Admin approves (shares it) or rejects the suggestion.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('suggested_at')->nullable()->after('company_id');
            $table->foreignId('suggested_by')->nullable()->after('suggested_at')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('suggested_by');
            $table->dropColumn('suggested_at');
        });
    }
};
