<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('phone', 30)->nullable()->unique()->after('email');
            $table->string('pin')->nullable()->after('password');
            $table->string('support_pin', 10)->nullable()->after('pin');
        });

        // Auto-generate 6-digit support_pin for any existing users
        $users = DB::table('users')->whereNull('support_pin')->get(['id']);
        foreach ($users as $u) {
            $pin = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            DB::table('users')->where('id', $u->id)->update(['support_pin' => $pin]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropUnique(['phone']);
            $table->dropColumn(['username', 'phone', 'pin', 'support_pin']);
        });
    }
};
