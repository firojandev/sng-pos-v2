<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('laravel-whatsapp.database.connection');
    }

    protected function table(): string
    {
        return config('laravel-whatsapp.database.prefix', '').'wa_messages';
    }

    public function up(): void
    {
        Schema::connection($this->getConnection())->table($this->table(), function (Blueprint $table) {
            $table->foreignId('shop_id')->nullable()->after('id')->index();
            $table->foreignId('sale_id')->nullable()->after('shop_id')->index();
        });
    }

    public function down(): void
    {
        Schema::connection($this->getConnection())->table($this->table(), function (Blueprint $table) {
            $table->dropIndex(['shop_id']);
            $table->dropIndex(['sale_id']);
            $table->dropColumn(['shop_id', 'sale_id']);
        });
    }
};
