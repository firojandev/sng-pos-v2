<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Customer\Http\Requests\StoreCustomerRequest;
use Modules\Customer\Http\Requests\UpdateCustomerRequest;
use Modules\Customer\Models\Customer;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::withSum('sales', 'due_amount')->latest()->paginate(10);

        return view('customer::index', compact('customers'));
    }

    public function create(): View
    {
        return view('customer::create', ['customer' => new Customer()]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')->with('status', 'গ্রাহক সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Customer $customer): View
    {
        return view('customer::edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return redirect()->route('customers.index')->with('status', 'গ্রাহক হালনাগাদ করা হয়েছে');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->sales()->exists()) {
            return redirect()->route('customers.index')->with('status', 'এই গ্রাহকের বিক্রয় রেকর্ড আছে, মুছে ফেলা যাবে না');
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('status', 'গ্রাহক মুছে ফেলা হয়েছে');
    }
}
