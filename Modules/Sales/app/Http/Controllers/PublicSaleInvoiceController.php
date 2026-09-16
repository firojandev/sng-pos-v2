<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Modules\Sales\Models\Sale;
use Modules\Shop\Models\PrinterSetting;
use Modules\Shop\Models\Shop;

class PublicSaleInvoiceController extends Controller
{
    /**
     * Show the public, unauthenticated customer sale invoice.
     */
    public function show(string $token): View
    {
        $sale = Sale::withoutGlobalScope('shop')
            ->where('public_token', $token)
            ->with([
                'shop.printerSetting',
                'customer',
                'warehouse',
                'items.product.units',
                'items.unit',
                'items.batch',
            ])
            ->firstOrFail();

        $shop = $sale->shop ?? Shop::first();
        $printerSetting = $shop?->printerSetting ?? PrinterSetting::getDefaultForShop($shop->id ?? 1);

        return view('sales::public.invoice', compact('sale', 'shop', 'printerSetting'));
    }
}
