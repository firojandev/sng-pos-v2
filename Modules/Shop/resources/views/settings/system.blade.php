<x-core::layout
    title="সিস্টেম সেটিংস"
    title-en="System Settings"
    subtitle="ল্যান্ডিং পেজ, সাপোর্ট এবং সাধারণ সিস্টেম কনফিগারেশন পরিচালনা করুন"
    subtitle-en="Manage public landing page, support info and general system configuration"
    active="system-settings"
>
    @if (session('status'))
        <div style="background:var(--green-100); border:1px solid var(--green-ic-bg); color:var(--green-ink); border-radius:12px; padding:12px 18px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px;">
            <x-core::icon name="check-circle" size="18" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div style="max-width:960px; margin:0 auto; display:flex; flex-direction:column; gap:24px;">

        {{-- Landing Page Status Card --}}
        <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:20px; flex-wrap:wrap;">
                <div style="display:flex; gap:16px; align-items:center;">
                    <div style="width:48px; height:48px; border-radius:12px; background:linear-gradient(135deg, rgba(37,99,235,0.15), rgba(14,165,233,0.2)); color:var(--blue-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <x-core::icon name="globe" size="24" />
                    </div>
                    <div>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <h3 style="margin:0; font-size:18px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">পাবলিক ল্যান্ডিং পেজ</span>
                                <span class="en">Public Landing Page</span>
                            </h3>
                            <x-core::badge
                                id="landingStatusBadge"
                                :color="$settings['landing_page_enabled'] ? 'green' : 'grey'"
                                size="sm"
                                :dot="true"
                                :label="$settings['landing_page_enabled'] ? 'চালু আছে (Active)' : 'বন্ধ আছে (Disabled)'"
                                :label-en="$settings['landing_page_enabled'] ? 'Active' : 'Disabled'"
                            />
                        </div>
                        <p style="margin:6px 0 0; font-size:13px; color:var(--ink-500); line-height:1.5;">
                            <span class="bn">ল্যান্ডিং পেজ চালু থাকলে ওয়েবসাইটের মূল ডোমেইনে (/) প্রবেশ করলে আধুনিক ল্যান্ডিং পেজটি প্রদর্শিত হবে। বন্ধ থাকলে ভিজিটর সরাসরি লগইন পেজে চলে যাবে।</span>
                            <span class="en">When enabled, visitors navigating to the root URL (/) will see the public landing page. When disabled, visitors are redirected straight to the login page.</span>
                        </p>
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:12px; flex-shrink:0;">
                    <a href="{{ route('landing') }}" target="_blank" class="preview-btn" style="text-decoration:none;">
                        <x-core::button size="sm" variant="secondary" icon="external-link">
                            <span class="bn">প্রিভিউ দেখুন</span>
                            <span class="en">Preview Landing</span>
                        </x-core::button>
                    </a>

                    <x-core::toggle
                        name="landing_page_toggle"
                        id="landing_page_toggle"
                        value="1"
                        :checked="$settings['landing_page_enabled']"
                        color="primary"
                        size="md"
                    />
                </div>
            </div>

            <div style="margin-top:20px; padding-top:16px; border-top:1px dashed var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; font-size:12.5px; color:var(--ink-500);">
                <div>
                    <span class="bn">কমান্ড লাইন থেকেও চালু বা বন্ধ করতে পারেন:</span>
                    <span class="en">You can also toggle via CLI:</span>
                    <code style="background:var(--paper); padding:2px 8px; border-radius:6px; font-family:monospace; color:var(--teal-800); border:1px solid var(--border); margin-left:6px;">php artisan landing:toggle [on|off]</code>
                </div>
                <div>
                    <span class="bn">সরাসরি প্রিভিউ লিঙ্ক:</span>
                    <span class="en">Direct Preview URL:</span>
                    <a href="{{ route('landing') }}" target="_blank" style="color:var(--teal-800); font-weight:600; text-decoration:underline; margin-left:4px;">{{ url('/landing') }}</a>
                </div>
            </div>
        </div>

        {{-- Detailed System Configuration Form --}}
        <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
            <div style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border);">
                <h4 style="margin:0; font-size:16px; font-weight:700; color:var(--ink-900);">
                    <span class="bn">সাধারণ তথ্য ও কন্টাক্ট ইনফরমেশন</span>
                    <span class="en">General Information & Contact</span>
                </h4>
                <p style="margin:4px 0 0; font-size:13px; color:var(--ink-500);">
                    <span class="bn">এই তথ্যগুলো ল্যান্ডিং পেজের হেডার, ফুটার ও যোগাযোগ সেকশনে প্রদর্শিত হবে।</span>
                    <span class="en">These details are displayed in the landing page header, footer, and contact section.</span>
                </p>
            </div>

            <form action="{{ route('system-settings.update') }}" method="POST" id="systemSettingsForm">
                @csrf
                <input type="hidden" name="landing_page_enabled" id="hiddenLandingEnabled" value="{{ $settings['landing_page_enabled'] ? '1' : '0' }}">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <x-core::input
                            name="site_title"
                            label="অ্যাপ্লিকেশনের নাম (App Name)"
                            size="sm"
                            :value="old('site_title', $settings['site_title'])"
                            placeholder="যেমন: MasterPOS"
                        />
                    </div>

                    <div>
                        <x-core::input
                            name="support_phone"
                            label="হেল্পলাইন / মোবাইল নম্বর (Phone)"
                            size="sm"
                            :value="old('support_phone', $settings['support_phone'])"
                            placeholder="+880 1886 861430"
                        />
                    </div>

                    <div>
                        <x-core::input
                            name="support_email"
                            label="সাপোর্ট ইমেইল (Support Email)"
                            size="sm"
                            type="email"
                            :value="old('support_email', $settings['support_email'])"
                            placeholder="support@softngear.com"
                        />
                    </div>

                    <div>
                        <x-core::input
                            name="office_address"
                            label="অফিসের ঠিকানা (Office Address)"
                            size="sm"
                            :value="old('office_address', $settings['office_address'])"
                            placeholder="Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi"
                        />
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <x-core::input
                        name="meta_description"
                        label="এসইও মেটা বিবরণ (Meta Description)"
                        size="sm"
                        :value="old('meta_description', $settings['meta_description'])"
                        placeholder="বাংলাদেশের আধুনিক ও দ্রুততম ক্লাউড POS এবং ব্যবসা পরিচালনা সফটওয়্যার।"
                    />
                </div>

                <div style="margin-top:24px; padding-top:16px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:12px;">
                    <x-core::button type="submit" size="sm" color="primary" icon="check">
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en">Save Changes</span>
                    </x-core::button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        $(function () {
            // Live AJAX toggle for landing page status
            $('#landing_page_toggle').on('change', function () {
                const isChecked = $(this).is(':checked');
                $('#hiddenLandingEnabled').val(isChecked ? '1' : '0');

                $.ajax({
                    url: "{{ route('system-settings.toggle-landing') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        state: isChecked ? 1 : 0
                    },
                    success: function (res) {
                        if (res.success) {
                            if (window.toast) {
                                window.toast(res.message, res.message);
                            }
                            const badge = $('#landingStatusBadge');
                            if (res.enabled) {
                                badge.removeClass('badge-grey').addClass('badge-green');
                                badge.find('.bn').text('চালু আছে (Active)');
                                badge.find('.en').text('Active');
                            } else {
                                badge.removeClass('badge-green').addClass('badge-grey');
                                badge.find('.bn').text('বন্ধ আছে (Disabled)');
                                badge.find('.en').text('Disabled');
                            }
                        }
                    },
                    error: function () {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি',
                                text: 'সেটিংস পরিবর্তন করতে ব্যর্থ হয়েছে।'
                            });
                        }
                    }
                });
            });
        });
    </script>
    @endpush
</x-core::layout>
