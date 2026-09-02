<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Cashbox\Models\CashTransaction;
use Modules\Customer\Models\Customer;
use Modules\Finance\Models\Expense;
use Modules\Product\Models\Batch;
use Modules\Purchase\Models\Purchase;
use Modules\Sales\Models\Sale;
use Modules\Supplier\Models\Supplier;

class PageController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        $range = $request->query('range', 'today');
        $range = in_array($range, ['today', 'week', 'month', 'year', 'all'], true) ? $range : 'today';

        [$from, $to] = $this->rangeBounds($range);

        $saleTotal = Sale::query()
            ->when($from, fn ($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('sale_date', '<=', $to))
            ->sum('total');

        $purchaseTotal = Purchase::query()
            ->when($from, fn ($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('purchase_date', '<=', $to))
            ->sum('total');

        $expenseTotal = Expense::query()
            ->when($from, fn ($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('expense_date', '<=', $to))
            ->sum('amount');

        $balance = CashTransaction::selectRaw("SUM(CASE WHEN type = 'in' THEN amount ELSE -amount END) as balance")->value('balance') ?? 0;

        $totalStockQty = Batch::sum('quantity');

        $totalReceivable = (float) Customer::sum('opening_due') + (float) Sale::sum('due_amount');
        $totalPayable = (float) Supplier::sum('opening_due') + (float) Purchase::sum('due_amount');

        return view('core::dashboard', [
            'range' => $range,
            'balance' => $balance,
            'saleTotal' => $saleTotal,
            'purchaseTotal' => $purchaseTotal,
            'expenseTotal' => $expenseTotal,
            'totalStockQty' => $totalStockQty,
            'totalReceivable' => $totalReceivable,
            'totalPayable' => $totalPayable,
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function rangeBounds(string $range): array
    {
        return match ($range) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->toDateString()],
            'month' => [now()->startOfMonth()->toDateString(), now()->toDateString()],
            'year' => [now()->startOfYear()->toDateString(), now()->toDateString()],
            default => [null, null],
        };
    }

    public function sales(): View
    {
        return $this->placeholder('sales', 'বিক্রয়', 'Sales', 'সকল বিক্রয় লেনদেন পরিচালনা করুন', 'Manage all sales transactions');
    }

    public function purchase(): View
    {
        return $this->placeholder('purchase', 'ক্রয়', 'Purchase', 'সরবরাহকারীর কাছ থেকে ক্রয় পরিচালনা করুন', 'Manage purchases from suppliers');
    }

    public function stock(): View
    {
        return $this->placeholder('stock', 'স্টক', 'Stock', 'মজুদ ও পণ্যের অবস্থা দেখুন', 'View inventory and stock status');
    }

    public function customers(): View
    {
        return $this->placeholder('customers', 'গ্রাহক', 'Customers', 'গ্রাহকের তথ্য ও বাকি ব্যালেন্স দেখুন', 'View customer info and due balances');
    }

    public function suppliers(): View
    {
        return $this->placeholder('suppliers', 'সরবরাহকারী', 'Suppliers', 'সরবরাহকারীর তথ্য ও পরিশোধ্য দেখুন', 'View supplier info and payables');
    }

    public function income(): View
    {
        return $this->placeholder('income', 'আয়', 'Income', 'সকল আয়ের উৎস ট্র্যাক করুন', 'Track all sources of income');
    }

    public function expense(): View
    {
        return $this->placeholder('expense', 'ব্যয়', 'Expense', 'ব্যবসার সকল খরচ ট্র্যাক করুন', 'Track all business expenses');
    }

    public function tax(): View
    {
        return $this->placeholder('tax', 'ট্যাক্স ও ভ্যাট', 'Tax & VAT', 'ট্যাক্স ও ভ্যাট হার পরিচালনা করুন', 'Manage tax and VAT rates');
    }

    public function reports(): View
    {
        return $this->placeholder('reports', 'রিপোর্ট', 'Reports', 'বিস্তারিত ব্যবসায়িক রিপোর্ট দেখুন', 'View detailed business reports');
    }

    public function settings(): View
    {
        return $this->placeholder('settings', 'সেটিংস', 'Settings', 'দোকান ও অ্যাকাউন্ট সেটিংস পরিচালনা করুন', 'Manage shop and account settings');
    }

    public function styleguide(): View
    {
        return view('core::pages.styleguide');
    }

    private function placeholder(string $active, string $title, string $titleEn, string $subtitle, string $subtitleEn): View
    {
        return view('core::pages.placeholder', compact('active', 'title', 'titleEn', 'subtitle', 'subtitleEn'));
    }
}
