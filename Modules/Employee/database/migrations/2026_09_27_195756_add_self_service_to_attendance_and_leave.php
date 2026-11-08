<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Employee self-service: clock in/out from the app (a "web" punch) and an
 * optional attachment on a leave application.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->enum('source', ['manual', 'device', 'import', 'web'])->default('manual')->change();
            $table->string('ip_address', 45)->nullable()->after('device_serial');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('reason');
            $table->string('attachment_name')->nullable()->after('attachment_path');
            $table->boolean('is_self_applied')->default(false)->after('attachment_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_name', 'is_self_applied']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn('ip_address');
            $table->enum('source', ['manual', 'device', 'import'])->default('manual')->change();
        });
    }
};
