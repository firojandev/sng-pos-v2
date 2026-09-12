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
        $registrationEnabled = Setting::isRegistrationEnabled();
        $termsAndPolicyEnabled = Setting::isTermsAndPolicyEnabled();
        $creditTextEnabled = Setting::isCreditTextEnabled();
        $creditText = Setting::getCreditText();

        return view('shop::settings.system', compact(
            'settings',
            'activeTab',
            'registrationEnabled',
            'termsAndPolicyEnabled',
            'creditTextEnabled',
            'creditText'
        ));
    }

    /**
     * Update system settings and landing page content.
     */
    public function update(Request $request): RedirectResponse
    {
        $defaults = LandingPageContent::defaults();
        $rules = [
            'landing_page_enabled' => ['nullable', 'boolean'],
            'registration_enabled' => ['nullable', 'boolean'],
            'show_terms_and_policy' => ['nullable', 'boolean'],
            'show_credit_text' => ['nullable', 'boolean'],
            'credit_text' => ['nullable', 'string', 'max:500'],
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

        if ($request->has('registration_enabled')) {
            Setting::setRegistrationEnabled($request->boolean('registration_enabled'));
        }

        if ($request->has('show_terms_and_policy')) {
            Setting::setTermsAndPolicyEnabled($request->boolean('show_terms_and_policy'));
        }

        if ($request->has('show_credit_text')) {
            Setting::setCreditTextEnabled($request->boolean('show_credit_text'));
        }

        if ($request->has('credit_text')) {
            Setting::setCreditText((string) $request->input('credit_text', ''));
        }

        $cleanData = [];
        foreach ($validated as $key => $val) {
            if (in_array($key, ['landing_page_enabled', 'registration_enabled', 'show_terms_and_policy', 'show_credit_text', 'credit_text', 'active_tab'], true)) {
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

    /**
     * AJAX Toggle for shop registration state.
     */
    public function toggleRegistration(Request $request): JsonResponse
    {
        $current = Setting::isRegistrationEnabled();
        $newState = $request->has('state') ? $request->boolean('state') : ! $current;

        Setting::setRegistrationEnabled($newState);

        return response()->json([
            'success' => true,
            'enabled' => $newState,
            'message' => $newState
                ? 'অনলাইন দোকান রেজিস্ট্রেশন সক্রিয় করা হয়েছে (Online registration enabled)'
                : 'অনলাইন দোকান রেজিস্ট্রেশন বন্ধ করা হয়েছে (Online registration disabled)',
        ]);
    }

    /**
     * AJAX Toggle for Terms & Policy footer links.
     */
    public function toggleTermsAndPolicy(Request $request): JsonResponse
    {
        $current = Setting::isTermsAndPolicyEnabled();
        $newState = $request->has('state') ? $request->boolean('state') : ! $current;

        Setting::setTermsAndPolicyEnabled($newState);

        return response()->json([
            'success' => true,
            'enabled' => $newState,
            'message' => $newState
                ? 'শর্তাবলী ও গোপনীয়তা নীতি লিংক সক্রিয় করা হয়েছে (Terms & Policy links enabled)'
                : 'শর্তাবলী ও গোপনীয়তা নীতি লিংক বন্ধ করা হয়েছে (Terms & Policy links disabled)',
        ]);
    }

    /**
     * AJAX Toggle for credit text visibility.
     */
    public function toggleCreditText(Request $request): JsonResponse
    {
        $current = Setting::isCreditTextEnabled();
        $newState = $request->has('state') ? $request->boolean('state') : ! $current;

        Setting::setCreditTextEnabled($newState);

        return response()->json([
            'success' => true,
            'enabled' => $newState,
            'message' => $newState
                ? 'ক্রেডিট টেক্সট সক্রিয় করা হয়েছে (Credit text enabled)'
                : 'ক্রেডিট টেক্সট বন্ধ করা হয়েছে (Credit text disabled)',
        ]);
    }
}
