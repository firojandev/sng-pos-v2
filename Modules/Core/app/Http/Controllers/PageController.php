<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function dashboard(): View|RedirectResponse
    {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('shops.index');
        }

        return view('core::dashboard');
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

    private function placeholder(string $active, string $title, string $titleEn, string $subtitle, string $subtitleEn): View
    {
        return view('core::pages.placeholder', compact('active', 'title', 'titleEn', 'subtitle', 'subtitleEn'));
    }
}
