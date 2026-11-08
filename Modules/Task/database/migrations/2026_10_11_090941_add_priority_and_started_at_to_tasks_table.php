<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A task's priority, and when it was picked up (first moved to "in
     * progress"), which the daily work report needs. Tasks already in
     * progress or completed are taken as picked up when last changed.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('priority', 20)->default('medium')->after('status');
            $table->timestamp('started_at')->nullable()->after('deadline');
        });

        DB::table('tasks')->whereIn('status', ['in_progress', 'completed'])->update(['started_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['priority', 'started_at']);
        });
    }
};
