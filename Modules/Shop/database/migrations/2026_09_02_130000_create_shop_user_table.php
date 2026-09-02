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
        Schema::create('shop_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_owner')->default(false);
            $table->timestamps();

            $table->unique(['shop_id', 'user_id']);
        });

        // Backfill existing shop_id relationships from users table into shop_user pivot table
        $users = DB::table('users')->whereNotNull('shop_id')->get();
        $now = now();
        foreach ($users as $user) {
            $shopExists = DB::table('shops')->where('id', $user->shop_id)->exists();
            if ($shopExists) {
                DB::table('shop_user')->updateOrInsert(
                    ['shop_id' => $user->shop_id, 'user_id' => $user->id],
                    [
                        'role' => 'Admin',
                        'is_owner' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_user');
    }
};
