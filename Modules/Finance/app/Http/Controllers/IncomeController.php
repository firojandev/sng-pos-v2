<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Finance\Http\Requests\StoreIncomeRequest;
use Modules\Finance\Http\Requests\UpdateIncomeRequest;
use Modules\Finance\Models\Account;
use Modules\Finance\Models\Income;

class IncomeController extends Controller
{
    public function index(): View
    {
        $incomes = Income::with('account')->latest('income_date')->paginate(10);

        return view('finance::income.index', compact('incomes'));
    }

    public function create(): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::income.create', [
            'income' => new Income,
            'accounts' => $accounts,
        ]);
    }

    public function store(StoreIncomeRequest $request): RedirectResponse
    {
        Income::create($request->validated());

        return redirect()->route('income.index')->with('status', 'আয় সফলভাবে যোগ করা হয়েছে');
    }

    public function edit(Income $income): View
    {
        $accounts = Account::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('finance::income.edit', compact('income', 'accounts'));
    }

    public function update(UpdateIncomeRequest $request, Income $income): RedirectResponse
    {
        $income->update($request->validated());

        return redirect()->route('income.index')->with('status', 'আয় হালনাগাদ করা হয়েছে');
    }

    public function destroy(Income $income): RedirectResponse
    {
        $income->delete();

        return redirect()->route('income.index')->with('status', 'আয় মুছে ফেলা হয়েছে');
    }
}
