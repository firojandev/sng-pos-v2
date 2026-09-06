<x-core::layout
    title="প্রোফাইল পরিচালনা"
    title-en="Manage Profile"
    subtitle="আপনার ব্যক্তিগত তথ্য, পাসওয়ার্ড এবং পিন কোড হালনাগাদ করুন"
    subtitle-en="Update your personal details, password and PIN codes"
    active="profile"
>
    <div style="max-width:980px; margin:0 auto;">
        <div style="display:grid; grid-template-columns:290px 1fr; gap:20px; align-items:start;">
            {{-- Left Column: User Summary Card --}}
            <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card); text-align:center;">
                <div style="width:72px; height:72px; border-radius:18px; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:28px; margin:0 auto 14px; box-shadow:0 4px 14px rgba(13,148,136,0.35);">
                    {{ mb_substr($user->name ?? '?', 0, 1) }}
                </div>

                <div style="font-size:16px; font-weight:700; color:var(--ink-900); line-height:1.3;">
                    {{ $user->name }}
                </div>

                @if ($user->username)
                    <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                        {{ '@' . $user->username }}
                    </div>
                @endif

                <div style="display:flex; align-items:center; justify-content:center; gap:6px; margin-top:10px; flex-wrap:wrap;">
                    @php
                        $roleName = $user->roles->first()?->name ?? ($user->isSuperAdmin() ? 'Super Admin' : 'User');
                    @endphp
                    <span style="font-size:11px; font-weight:700; color:#0f766e; background:rgba(13, 148, 136, 0.12); padding:3px 8px; border-radius:6px;">
                        {{ $roleName }}
                    </span>
                    @if ($user->shop)
                        <span style="display:inline-flex; align-items:center; gap:4px; font-size:11px; font-weight:600; color:var(--ink-600); background:var(--paper-line); padding:3px 8px; border-radius:6px;" title="{{ $user->shop->name }}">
                            <x-core::icon name="store" size="12" />
                            <span>{{ Str::limit($user->shop->name, 16) }}</span>
                        </span>
                    @endif
                </div>

                <div style="border-top:1px solid var(--border); margin:18px 0; padding-top:16px; text-align:left; display:flex; flex-direction:column; gap:12px;">
                    <div>
                        <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">ইমেইল অ্যাড্রেস</span>
                            <span class="en" style="display:none;">Email Address</span>
                        </div>
                        <div style="font-size:12.5px; font-weight:500; color:var(--ink-800); margin-top:2px; word-break:break-all;">
                            {{ $user->email }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">ফোন নম্বর</span>
                            <span class="en" style="display:none;">Phone Number</span>
                        </div>
                        <div style="font-size:12.5px; font-weight:500; color:var(--ink-800); margin-top:2px;">
                            {{ $user->phone ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size:11px; font-weight:600; color:var(--ink-400); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">যোগদানের তারিখ</span>
                            <span class="en" style="display:none;">Joined Date</span>
                        </div>
                        <div style="font-size:12.5px; font-weight:500; color:var(--ink-800); margin-top:2px;">
                            {{ $user->created_at?->format('d M, Y') ?? '—' }}
                        </div>
                    </div>
                </div>

                @if ($user->support_pin)
                    <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px; text-align:left;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <span style="font-size:11px; font-weight:700; color:var(--ink-600); display:flex; align-items:center; gap:5px;">
                                <x-core::icon name="key" size="13" />
                                <span class="bn">সাপোর্ট পিন</span>
                                <span class="en" style="display:none;">Support PIN</span>
                            </span>
                            <span style="font-family:monospace; font-size:13px; font-weight:800; color:#0f766e; letter-spacing:1px;">
                                #{{ $user->support_pin }}
                            </span>
                        </div>
                        <p style="font-size:10.5px; color:var(--ink-400); margin-top:6px; line-height:1.4; margin-bottom:0;">
                            <span class="bn">কাস্টমার সাপোর্টে দ্রুত সহায়তার জন্য এই কোডটি প্রদান করুন।</span>
                            <span class="en" style="display:none;">Provide this unique code for fast customer support assistance.</span>
                        </p>
                    </div>
                @endif
            </div>

            {{-- Right Column: Edit Profile Form --}}
            <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    {{-- Section 1: Basic Information --}}
                    <div style="margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="user" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ব্যক্তিগত তথ্য</span>
                            <span class="en" style="display:none;">Personal Information</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <x-core::input
                            name="name"
                            label="পুরো নাম"
                            label-en="Full Name"
                            value="{{ old('name', $user->name) }}"
                            placeholder="আপনার পুরো নাম লিখুন"
                            placeholder-en="Enter your full name"
                            size="sm"
                            :required="true"
                        />

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <x-core::input
                                name="username"
                                label="ইউজারনেম (ঐচ্ছিক)"
                                label-en="Username (Optional)"
                                value="{{ old('username', $user->username) }}"
                                placeholder="যেমন: user123"
                                placeholder-en="e.g. user123"
                                size="sm"
                            />

                            <x-core::input
                                name="phone"
                                type="text"
                                label="ফোন নম্বর (ঐচ্ছিক)"
                                label-en="Phone Number (Optional)"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="যেমন: 017xxxxxxxx"
                                placeholder-en="e.g. 017xxxxxxxx"
                                size="sm"
                            />
                        </div>

                        <x-core::input
                            name="email"
                            type="email"
                            label="ইমেইল অ্যাড্রেস"
                            label-en="Email Address"
                            value="{{ old('email', $user->email) }}"
                            placeholder="user@example.com"
                            placeholder-en="user@example.com"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    {{-- Section 2: Security & Password --}}
                    <div style="margin:24px 0 16px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="lock" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">পাসওয়ার্ড পরিবর্তন (ঐচ্ছিক)</span>
                            <span class="en" style="display:none;">Change Password (Optional)</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <x-core::input
                            name="current_password"
                            type="password"
                            label="বর্তমান পাসওয়ার্ড"
                            label-en="Current Password"
                            placeholder="••••••••"
                            placeholder-en="••••••••"
                            size="sm"
                            :password-toggle="true"
                            helper="পাসওয়ার্ড পরিবর্তন করতে চাইলে বর্তমান পাসওয়ার্ড প্রদান করুন।"
                        />

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <x-core::input
                                name="password"
                                type="password"
                                label="নতুন পাসওয়ার্ড"
                                label-en="New Password"
                                placeholder="কমপক্ষে ৮ অক্ষর"
                                placeholder-en="Minimum 8 characters"
                                size="sm"
                                :password-toggle="true"
                            />

                            <x-core::input
                                name="password_confirmation"
                                type="password"
                                label="নতুন পাসওয়ার্ড নিশ্চিত করুন"
                                label-en="Confirm New Password"
                                placeholder="••••••••"
                                placeholder-en="••••••••"
                                size="sm"
                                :password-toggle="true"
                            />
                        </div>
                    </div>

                    {{-- Section 3: PIN & Support PIN --}}
                    <div style="margin:24px 0 16px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="shield" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ইউজার পিন ও সাপোর্ট পিন</span>
                            <span class="en" style="display:none;">User PIN & Support PIN</span>
                        </span>
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start;">
                        <div>
                            <x-core::input
                                name="pin"
                                type="text"
                                maxlength="4"
                                label="ইউজার পিন (৪ সংখ্যা)"
                                label-en="User PIN (4 Digits)"
                                value="{{ old('pin') }}"
                                placeholder="পরিবর্তন করতে চাইলে ৪ ডিজিট লিখুন"
                                placeholder-en="Enter 4 digits to change"
                                size="sm"
                                helper="লগইন ও দ্রুত অনুমোদন পিনের জন্য ৪ সংখ্যার পিন ব্যবহার করুন।"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px; margin-top:20px;">
                            <x-core::checkbox
                                name="regenerate_support_pin"
                                value="1"
                                size="sm"
                                label="নতুন সাপোর্ট পিন তৈরি করুন"
                                label-en="Regenerate Support PIN"
                                description="বর্তমান সাপোর্ট পিনটি অকার্যকর করে একটি নতুন ৬-সংখ্যার কোড তৈরি করা হবে।"
                            />
                        </div>
                    </div>

                    {{-- Form Footer Actions --}}
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-top:28px; padding-top:16px; border-top:1px solid var(--border);">
                        <x-core::button
                            type="submit"
                            color="primary"
                            size="sm"
                            icon="check"
                        >
                            <span class="bn">পরিবর্তন সংরক্ষণ করুন</span>
                            <span class="en" style="display:none;">Save Changes</span>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-core::layout>
