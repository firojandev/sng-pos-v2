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
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->string('do_number')->nullable()->after('received_quantity');
            $table->date('do_date')->nullable()->after('do_number');
            $table->string('vehicle_number')->nullable()->after('do_date');
            $table->string('delivery_person_name')->nullable()->after('vehicle_number');
            $table->text('note')->nullable()->after('delivery_person_name');
        });

        // Backfill existing receipt items from parent purchases if available
        if (Schema::hasColumn('purchases', 'do_number')) {
            $itemsToUpdate = DB::table('purchase_receipt_items')
                ->whereNull('do_number')
                ->get(['id', 'purchase_id']);

            foreach ($itemsToUpdate as $item) {
                $purchase = DB::table('purchases')->where('id', $item->purchase_id)->first();
                if ($purchase && ! empty($purchase->do_number)) {
                    DB::table('purchase_receipt_items')->where('id', $item->id)->update([
                        'do_number' => $purchase->do_number,
                        'do_date' => $purchase->do_date ?? null,
                        'vehicle_number' => $purchase->vehicle_number ?? null,
                        'delivery_person_name' => $purchase->delivery_person_name ?? null,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_receipt_items', function (Blueprint $table) {
            $table->dropColumn([
                'do_number',
                'do_date',
                'vehicle_number',
                'delivery_person_name',
                'note',
            ]);
        });
    }
};
