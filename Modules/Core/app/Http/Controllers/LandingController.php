<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Setting;
use Modules\Shop\Models\Plan;

class LandingController extends Controller
{
    /**
     * Display the public landing page or redirect if disabled.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! Setting::isLandingPageEnabled()) {
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }

            return redirect()->route('login');
        }

        return $this->renderLandingPage();
    }

    /**
     * Preview the landing page regardless of the enabled status.
     */
    public function preview(): View
    {
        return $this->renderLandingPage(isPreview: true);
    }

    /**
     * Render the landing page view with plans and stats.
     */
    protected function renderLandingPage(bool $isPreview = false): View
    {
        $plans = Plan::query()
            ->with('features')
            ->where('is_active', true)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $user = auth()->user();

        return view('core::landing.index', compact('plans', 'user', 'isPreview'));
    }
}
