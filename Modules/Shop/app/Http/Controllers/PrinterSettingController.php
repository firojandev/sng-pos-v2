<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Shop\Http\Requests\UpdatePrinterSettingRequest;
use Modules\Shop\Models\PrinterSetting;

class PrinterSettingController extends Controller
{
    /**
     * Show printer settings configuration page.
     */
    public function index(): View|RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if (! $user->shop && $user->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        $shop = $user->shop;
        if (! $shop) {
            abort(403, 'কোনো দোকান নির্বাচন বা বরাদ্দ করা নেই (No shop assigned)।');
        }

        if (! $user->isShopAdmin($shop)) {
            abort(403, 'প্রিন্টার সেটিংস দেখার বা পরিচালনা করার অনুমতি শুধুমাত্র শপ এডমিনের রয়েছে (Only shop admin can access printer settings)।');
        }

        $printerSetting = PrinterSetting::getDefaultForShop($shop->id);

        return view('shop::printer-settings.index', compact('shop', 'printerSetting'));
    }

    /**
     * Update printer settings for current shop.
     */
    public function update(UpdatePrinterSettingRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $shop = $user->shop;

        if (! $shop && $user->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        if (! $shop) {
            abort(403, 'কোনো দোকান নির্বাচন বা বরাদ্দ করা নেই (No shop assigned)।');
        }

        if (! $user->isShopAdmin($shop)) {
            abort(403, 'প্রিন্টার সেটিংস পরিবর্তন করার অনুমতি শুধুমাত্র শপ এডমিনের রয়েছে (Only shop admin can update printer settings)।');
        }

        $validated = $request->validated();

        $printerSetting = PrinterSetting::firstOrNew(['shop_id' => $shop->id]);
        $printerSetting->printer_type = $validated['printer_type'];
        $printerSetting->orientation = $validated['orientation'];
        $printerSetting->paper_width = $validated['paper_width'];
        $printerSetting->paper_height = $validated['paper_height'] ?? null;
        $printerSetting->unit = $validated['unit'];
        $printerSetting->page_margin = $validated['page_margin'] ?? 2.0;
        $printerSetting->auto_print = $request->boolean('auto_print');
        $printerSetting->show_header_logo = $request->boolean('show_header_logo');
        $printerSetting->show_shop_info = $request->boolean('show_shop_info');
        $printerSetting->show_customer_due = $request->boolean('show_customer_due');
        $printerSetting->show_footer_note = $request->boolean('show_footer_note');
        $printerSetting->print_copies = (int) ($validated['print_copies'] ?? 1);
        $printerSetting->save();

        return redirect()
            ->route('printer-settings.index')
            ->with('status', 'প্রিন্টার ও পেপার সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে (Printer settings saved successfully)');
    }
}
