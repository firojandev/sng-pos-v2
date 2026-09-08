<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\User\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function edit(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $user->loadMissing(['shop', 'roles']);

        return view('user::profile', compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $user->name = $request->validated('name');
        $user->username = $request->validated('username');
        $user->email = $request->validated('email');

        if (! $user->isShopOwner()) {
            $user->phone = $request->validated('phone');
        }

        if ($request->boolean('remove_avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = null;
        } elseif ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->validated('password'));
        }

        if ($request->filled('pin')) {
            $user->pin = $request->validated('pin');
        }

        if ($request->boolean('regenerate_support_pin')) {
            $user->support_pin = User::generateUniqueSupportPin();
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('status', 'আপনার প্রোফাইল সফলভাবে হালনাগাদ করা হয়েছে (Profile updated successfully)');
    }
}
