<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('printer_settings')) {
            Schema::create('printer_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('shop_id')->constrained('shops')->cascadeOnDelete();
                $table->string('printer_type', 20)->default('thermal'); // a4, a5, thermal
                $table->string('orientation', 20)->default('portrait'); // portrait, landscape
                $table->decimal('paper_width', 8, 2)->default(80.00);
                $table->decimal('paper_height', 8, 2)->nullable(); // null for continuous roll on thermal
                $table->string('unit', 10)->default('mm'); // mm, inch
                $table->decimal('page_margin', 4, 1)->default(2.0); // in mm
                $table->boolean('auto_print')->default(false);
                $table->boolean('show_header_logo')->default(true);
                $table->boolean('show_shop_info')->default(true);
                $table->boolean('show_customer_due')->default(true);
                $table->boolean('show_footer_note')->default(true);
                $table->unsignedTinyInteger('print_copies')->default(1);
                $table->timestamps();

                $table->unique('shop_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printer_settings');
    }
};
