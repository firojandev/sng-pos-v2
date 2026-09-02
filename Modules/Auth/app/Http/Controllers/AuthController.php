<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'ইমেইল অথবা পাসওয়ার্ড সঠিক নয়']);
        }

        $request->session()->regenerate();

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
