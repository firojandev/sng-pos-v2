<?php

namespace Modules\Company\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\Company\Http\Requests\MoveShopToCompanyRequest;
use Modules\Company\Models\Company;
use Modules\Company\Services\ShopCompanyTransferService;
use Modules\Shop\Models\Shop;

/**
 * Super-admin tool that moves a shop into another company.
 */
class ShopCompanyController extends Controller
{
    public function update(MoveShopToCompanyRequest $request, Shop $shop, ShopCompanyTransferService $transfer): RedirectResponse
    {
        $target = Company::findOrFail($request->validated('company_id'));

        $transfer->move($shop, $target);

        return redirect()->route('shops.edit', $shop)
            ->with('status', "দোকানটি \"{$target->name}\" কোম্পানিতে স্থানান্তর করা হয়েছে");
    }
}
