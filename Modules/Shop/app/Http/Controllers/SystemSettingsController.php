<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Setting;
use Modules\Core\Support\LandingPageContent;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings and landing page content management page.
     */
    public function index(Request $request): View
    {
        $settings = LandingPageContent::all();
        $activeTab = $request->get('tab', session('active_tab', 'general'));

        return view('shop::settings.system', compact('settings', 'activeTab'));
    }

    /**
     * Update system settings and landing page content.
     */
    public function update(Request $request): RedirectResponse
    {
        $defaults = LandingPageContent::defaults();
        $rules = [
            'landing_page_enabled' => ['nullable', 'boolean'],
            'active_tab' => ['nullable', 'string'],
        ];

        foreach ($defaults as $key => $defaultVal) {
            if (is_array($defaultVal)) {
                $rules[$key] = ['nullable', 'array'];
            } else {
                $rules[$key] = ['nullable', 'string'];
            }
        }

        $validated = $request->validate($rules);

        if ($request->has('landing_page_enabled')) {
            Setting::setLandingPageEnabled($request->boolean('landing_page_enabled'));
        }

        $cleanData = [];
        foreach ($validated as $key => $val) {
            if ($key === 'landing_page_enabled' || $key === 'active_tab') {
                continue;
            }

            if (is_array($val)) {
                $filtered = array_values(array_filter($val, function ($row) {
                    if (! is_array($row)) {
                        return ! empty($row);
                    }

                    return ! empty(array_filter($row, fn ($item) => ! is_null($item) && $item !== ''));
                }));
                $cleanData[$key] = $filtered;
            } else {
                $cleanData[$key] = $val;
            }
        }

        LandingPageContent::saveMany($cleanData);

        $redirect = $request->filled('active_tab')
            ? redirect()->route('system-settings.index', ['tab' => $request->input('active_tab')])
            : redirect()->route('system-settings.index');

        return $redirect
            ->with('status', 'সিস্টেম সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে (System settings saved successfully)')
            ->with('active_tab', $request->input('active_tab', 'general'));
    }

    /**
     * AJAX Toggle for landing page state.
     */
    public function toggleLanding(Request $request): JsonResponse
    {
        $current = Setting::isLandingPageEnabled();
        $newState = $request->has('state') ? $request->boolean('state') : ! $current;

        Setting::setLandingPageEnabled($newState);

        return response()->json([
            'success' => true,
            'enabled' => $newState,
            'message' => $newState
                ? 'ল্যান্ডিং পেজ সক্রিয় করা হয়েছে (Landing page enabled)'
                : 'ল্যান্ডিং পেজ নিষ্ক্রিয় করা হয়েছে (Landing page disabled)',
        ]);
    }
}
