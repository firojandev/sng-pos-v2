<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Support\TenantContext;
use Modules\Core\Support\TenantRules;
use Modules\Shop\Models\Shop;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Stock leaves one of the current shop's warehouses and may go to any
            // warehouse of the company's shops.
            'from_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->where('shop_id', app(TenantContext::class)->shopId()), 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', Rule::exists('warehouses', 'id')->whereIn('shop_id', $this->companyShopIds()), 'different:from_warehouse_id'],
            'note' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', TenantRules::catalogExists('products')],
            'items.*.batch_id' => ['required', Rule::exists('batches', 'id')->where('shop_id', app(TenantContext::class)->shopId())->where('warehouse_id', $this->input('from_warehouse_id'))],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * @return list<int>
     */
    private function companyShopIds(): array
    {
        return Shop::where('company_id', app(TenantContext::class)->companyId())->pluck('id')->all();
    }
}
