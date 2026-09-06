<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth::login');
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $request->filled('login') && ! $request->filled('email')) {
            $request->validate([
                'email' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]);
        } else {
            $request->validate([
                'password' => ['required', 'string'],
            ]);
        }

        $identifier = trim((string) ($request->input('login') ?? $request->input('email')));
        $secret = (string) $request->input('password');
        $remember = $request->boolean('remember');

        $user = User::findByIdentifier($identifier);

        if (! $user) {
            return back()
                ->withInput($request->only('login', 'email'))
                ->withErrors([
                    'login' => 'ইউজারনেম, ইমেইল, ফোন অথবা পাসওয়ার্ড/পিন সঠিক নয়',
                    'email' => 'ইউজারনেম, ইমেইল, ফোন অথবা পাসওয়ার্ড/পিন সঠিক নয়',
                ]);
        }

        $authType = $user->verifySecret($secret);

        if (! $authType) {
            return back()
                ->withInput($request->only('login', 'email'))
                ->withErrors([
                    'login' => 'ইউজারনেম, ইমেইল, ফোন অথবা পাসওয়ার্ড/পিন সঠিক নয়',
                    'email' => 'ইউজারনেম, ইমেইল, ফোন অথবা পাসওয়ার্ড/পিন সঠিক নয়',
                ]);
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if ($authType === 'support_pin') {
            session([
                'is_support_login' => true,
                'support_logged_in_at' => now()->toIso8601String(),
            ]);
        }

        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('dashboard'));
        }

        $shops = $user->activeShops()->get();

        if ($shops->isEmpty() && $user->shop_id && $user->shop && $user->shop->status === 'active') {
            $user->shops()->syncWithoutDetaching([
                $user->shop_id => [
                    'role' => $user->roles->first()?->name ?? 'Admin',
                    'is_owner' => true,
                ],
            ]);
            $shops = collect([$user->shop]);
        }

        if ($shops->count() > 1) {
            return redirect()->route('shops.select');
        }

        if ($shops->count() === 1) {
            $singleShop = $shops->first();
            if ($user->shop_id !== $singleShop->id) {
                $user->shop_id = $singleShop->id;
                $user->save();
            }
            session(['current_shop_id' => $singleShop->id]);

            return redirect()->intended(route('dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
