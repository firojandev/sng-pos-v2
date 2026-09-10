<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Core\Models\Setting;

class SystemSettingsController extends Controller
{
    /**
     * Display the system settings page.
     */
    public function index(): View
    {
        $settings = [
            'landing_page_enabled' => Setting::isLandingPageEnabled(),
            'site_title' => Setting::get('site_title', config('app.name', 'MasterPOS')),
            'support_phone' => Setting::get('support_phone', '+880 1886 861430'),
            'support_email' => Setting::get('support_email', 'support@softngear.com'),
            'office_address' => Setting::get('office_address', 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi'),
            'meta_description' => Setting::get('meta_description', 'বাংলাদেশের আধুনিক ও দ্রুততম ক্লাউড POS এবং ব্যবসা পরিচালনা সফটওয়্যার।'),
        ];

        return view('shop::settings.system', compact('settings'));
    }

    /**
     * Update general system settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'landing_page_enabled' => ['nullable', 'boolean'],
            'site_title' => ['nullable', 'string', 'max:100'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:100'],
            'office_address' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ]);

        $landingEnabled = $request->boolean('landing_page_enabled');
        Setting::setLandingPageEnabled($landingEnabled);

        if (isset($validated['site_title'])) {
            Setting::set('site_title', $validated['site_title'], 'string', 'system');
        }
        if (isset($validated['support_phone'])) {
            Setting::set('support_phone', $validated['support_phone'], 'string', 'system');
        }
        if (isset($validated['support_email'])) {
            Setting::set('support_email', $validated['support_email'], 'string', 'system');
        }
        if (isset($validated['office_address'])) {
            Setting::set('office_address', $validated['office_address'], 'string', 'system');
        }
        if (isset($validated['meta_description'])) {
            Setting::set('meta_description', $validated['meta_description'], 'string', 'system');
        }

        return redirect()
            ->route('system-settings.index')
            ->with('status', 'সিস্টেম সেটিংস সফলভাবে সংরক্ষণ করা হয়েছে (System settings saved successfully)');
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
