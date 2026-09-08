<x-core::layout
    title="প্রোফাইল পরিচালনা"
    title-en="Manage Profile"
    subtitle="আপনার ব্যক্তিগত তথ্য, পাসওয়ার্ড এবং পিন কোড হালনাগাদ করুন"
    subtitle-en="Update your personal details, password and PIN codes"
    active="profile"
>
    <div style="max-width:980px;">
        <div style="display:grid; grid-template-columns:290px 1fr; gap:20px; align-items:start;">
            {{-- Left Column: User Summary Card --}}
            <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card); text-align:center;">
                <div style="position:relative; width:80px; height:80px; margin:0 auto 14px;">
                    <div id="leftAvatarWrapper" style="width:80px; height:80px; border-radius:20px; overflow:hidden; display:flex; align-items:center; justify-content:center; box-shadow:var(--shadow-sm); border:2px solid var(--border); background:var(--card);">
                        @if ($user->avatar_url)
                            <img id="leftAvatarImg" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" onerror="this.style.display='none'; document.getElementById('leftAvatarFallback').style.display='flex';" style="width:100%; height:100%; object-fit:cover; display:block;">
                            <div id="leftAvatarFallback" style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:32px;">
                                {{ mb_substr($user->name ?? '?', 0, 1) }}
                            </div>
                        @else
                            <img id="leftAvatarImg" src="" alt="{{ $user->name }}" style="display:none; width:100%; height:100%; object-fit:cover;">
                            <div id="leftAvatarFallback" style="display:flex; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:32px;">
                                {{ mb_substr($user->name ?? '?', 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <button type="button" id="leftAvatarChangeBtn" style="position:absolute; bottom:-4px; right:-4px; width:28px; height:28px; border-radius:50%; background:var(--teal-800); color:#ffffff; border:2px solid var(--card); display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:var(--shadow-sm); transition:transform 0.15s ease;" title="ছবি পরিবর্তন করুন / Change Photo">
                        <x-core::icon name="camera" size="13" />
                    </button>
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
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
                    @csrf
                    @method('PUT')

                    {{-- Section: Profile Photo --}}
                    <div style="margin-bottom:24px; padding:16px; background:var(--paper); border:1px solid var(--border); border-radius:14px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
                        <div style="width:68px; height:68px; border-radius:18px; overflow:hidden; border:2px solid var(--border); background:var(--card); display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:var(--shadow-sm);">
                            @if ($user->avatar_url)
                                <img id="formAvatarPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}" onerror="this.style.display='none'; document.getElementById('formAvatarFallback').style.display='flex';" style="width:100%; height:100%; object-fit:cover; display:block;">
                                <div id="formAvatarFallback" style="display:none; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:26px;">
                                    {{ mb_substr($user->name ?? '?', 0, 1) }}
                                </div>
                            @else
                                <img id="formAvatarPreview" src="" alt="{{ $user->name }}" style="display:none; width:100%; height:100%; object-fit:cover;">
                                <div id="formAvatarFallback" style="display:flex; width:100%; height:100%; background:linear-gradient(135deg, #0D9488 0%, #0891B2 100%); color:#ffffff; align-items:center; justify-content:center; font-family:'Noto Sans Bengali','SolaimanLipi','Baloo Da 2',sans-serif; font-weight:800; font-size:26px;">
                                    {{ mb_substr($user->name ?? '?', 0, 1) }}
                                </div>
                            @endif
                        </div>

                        <div style="flex:1; min-width:220px;">
                            <div style="font-size:13.5px; font-weight:700; color:var(--ink-900); margin-bottom:2px;">
                                <span class="bn">প্রোফাইল ছবি</span>
                                <span class="en" style="display:none;">Profile Photo</span>
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-bottom:10px;">
                                <span class="bn">JPG, PNG, WEBP বা GIF ফাইল (সর্বোচ্চ 2MB)</span>
                                <span class="en" style="display:none;">JPG, PNG, WEBP or GIF file (Max 2MB)</span>
                            </div>

                            <input type="file" id="avatarInput" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;">
                            <input type="hidden" id="removeAvatarInput" name="remove_avatar" value="0">

                            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                                <x-core::button
                                    type="button"
                                    id="btnUploadAvatar"
                                    variant="secondary"
                                    size="sm"
                                    icon="upload"
                                >
                                    <span class="bn">ছবি পরিবর্তন করুন</span>
                                    <span class="en" style="display:none;">Change Photo</span>
                                </x-core::button>

                                <x-core::button
                                    type="button"
                                    id="btnRemoveAvatar"
                                    color="danger"
                                    variant="soft"
                                    size="sm"
                                    icon="trash-2"
                                    style="{{ $user->avatar ? '' : 'display:none;' }}"
                                >
                                    <span class="bn">ছবি মুছুন</span>
                                    <span class="en" style="display:none;">Remove Photo</span>
                                </x-core::button>

                                <span id="avatarFileBadge" style="font-size:11.5px; color:var(--teal-800); font-weight:600; display:none; background:var(--teal-100); border:1px solid var(--teal-200); padding:2px 8px; border-radius:6px;"></span>
                            </div>

                            @error('avatar')
                                <div style="font-size:11.5px; color:var(--red-600); margin-top:6px; font-weight:500;">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>

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

                            @if ($user->isShopOwner())
                                <x-core::input
                                    name="phone"
                                    type="text"
                                    label="ফোন নম্বর (মালিকের নম্বর অপরিবর্তনযোগ্য)"
                                    label-en="Phone Number (Locked for Shop Owner)"
                                    value="{{ $user->phone }}"
                                    size="sm"
                                    :readonly="true"
                                    helper="নিরাপত্তার স্বার্থে দোকানের মালিকের ফোন নম্বর পরিবর্তন করা যাবে না।"
                                    helper-en="For security, shop owner phone number cannot be changed."
                                />
                            @else
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
                            @endif
                        </div>

                        <x-core::input
                            name="email"
                            type="email"
                            label="ইমেইল অ্যাড্রেস (ঐচ্ছিক)"
                            label-en="Email Address (Optional)"
                            value="{{ old('email', $user->email) }}"
                            placeholder="user@example.com"
                            placeholder-en="user@example.com"
                            size="sm"
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

    @push('scripts')
        <script>
            $(function () {
                const $avatarInput = $('#avatarInput');
                const $removeAvatarInput = $('#removeAvatarInput');
                const $btnUploadAvatar = $('#btnUploadAvatar');
                const $btnRemoveAvatar = $('#btnRemoveAvatar');
                const $leftAvatarChangeBtn = $('#leftAvatarChangeBtn');
                const $formAvatarPreview = $('#formAvatarPreview');
                const $formAvatarFallback = $('#formAvatarFallback');
                const $leftAvatarImg = $('#leftAvatarImg');
                const $leftAvatarFallback = $('#leftAvatarFallback');
                const $avatarFileBadge = $('#avatarFileBadge');

                // Trigger file selector from buttons
                $btnUploadAvatar.on('click', function () {
                    $avatarInput.trigger('click');
                });

                $leftAvatarChangeBtn.on('click', function () {
                    $avatarInput.trigger('click');
                });

                // Handle file selection
                $avatarInput.on('change', function () {
                    const file = this.files && this.files[0];
                    if (!file) {
                        return;
                    }

                    // Max 2MB (2 * 1024 * 1024 bytes)
                    if (file.size > 2 * 1024 * 1024) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ফাইল সাইজ খুব বড়',
                                text: 'ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।',
                                confirmButtonText: 'ঠিক আছে',
                            });
                        } else if (typeof toast === 'function') {
                            toast('ছবির সাইজ সর্বোচ্চ ২ মেগাবাইট (2MB) হতে পারবে।', 'Max photo size is 2MB');
                        }
                        $avatarInput.val('');
                        return;
                    }

                    const allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedMimes.includes(file.type)) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'অসমর্থিত ফরম্যাট',
                                text: 'শুধুমাত্র JPG, PNG, WEBP বা GIF ফরম্যাটের ছবি আপলোড করুন।',
                                confirmButtonText: 'ঠিক আছে',
                            });
                        } else if (typeof toast === 'function') {
                            toast('শুধুমাত্র JPG, PNG, WEBP বা GIF ফরম্যাটের ছবি আপলোড করুন।', 'Only JPG, PNG, WEBP or GIF files allowed');
                        }
                        $avatarInput.val('');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function (e) {
                        const previewSrc = e.target.result;
                        $formAvatarPreview.attr('src', previewSrc).show();
                        $formAvatarFallback.hide();

                        $leftAvatarImg.attr('src', previewSrc).show();
                        $leftAvatarFallback.hide();

                        $removeAvatarInput.val('0');
                        $btnRemoveAvatar.show();
                        $avatarFileBadge.text(file.name).show();
                    };
                    reader.readAsDataURL(file);
                });

                // Handle remove photo
                $btnRemoveAvatar.on('click', function () {
                    $avatarInput.val('');
                    $removeAvatarInput.val('1');

                    $formAvatarPreview.attr('src', '').hide();
                    $formAvatarFallback.css('display', 'flex').show();

                    $leftAvatarImg.attr('src', '').hide();
                    $leftAvatarFallback.css('display', 'flex').show();

                    $avatarFileBadge.hide().text('');
                    $btnRemoveAvatar.hide();
                });
            });
        </script>
    @endpush
</x-core::layout>
