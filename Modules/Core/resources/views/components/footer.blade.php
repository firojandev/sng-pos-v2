@php
    $siteTitle = $siteTitle ?? \Modules\Core\Models\Setting::getSiteTitle();
    $siteTitleBn = $siteTitleBn ?? ($siteTitle === 'SNGPOS' ? 'SNGPOS' : $siteTitle);
    $showTermsAndPolicy = \Modules\Core\Models\Setting::isTermsAndPolicyEnabled();
    $showCreditText = \Modules\Core\Models\Setting::isCreditTextEnabled();
    $creditText = $showCreditText ? \Modules\Core\Models\Setting::getCreditText() : '';

    $landingContent = \Modules\Core\Support\LandingPageContent::all();
    $supportPhone = $landingContent['support_phone'] ?? '+880 1886 861430';
    $supportEmail = $landingContent['support_email'] ?? 'support@softngear.com';
    $officeAddress = $landingContent['office_address'] ?? 'Shop 407, 3rd Floor, Shwapnochura Plaza, Rajshahi';
    $cleanPhone = preg_replace('/[^0-9]/', '', $supportPhone);
@endphp
<footer class="app-footer">
    <div class="copy">
        <span class="bn">&copy; {{ now()->year }} <b>{{ $siteTitleBn }}</b> &middot; সর্বস্বত্ব সংরক্ষিত</span>
        <span class="en" style="display:none;">&copy; {{ now()->year }} <b>{{ $siteTitle }}</b> &middot; All
            rights reserved</span>
    </div>
    <div class="meta">
        <span class="ver">v1.0.0</span>
        <a href="{{ route('home') }}#faq" class="support-modal-trigger"><span class="bn">সহায়তা</span><span class="en" style="display:none;">Support</span></a>
        @if ($showTermsAndPolicy)
            <a href="{{ route('privacy-policy') }}"><span class="bn">গোপনীয়তা নীতি</span><span class="en" style="display:none;">Privacy Policy</span></a>
            <a href="{{ route('terms') }}"><span class="bn">ব্যবহারের শর্তাবলী</span><span class="en" style="display:none;">Terms & Conditions</span></a>
        @endif
    </div>
    @if ($showCreditText && !empty($creditText))
        <div class="credit-text" style="text-align:right; font-size:11px; color:var(--ink-400); margin-top:4px; font-weight:400; opacity:0.8;">
            {{ $creditText }}
        </div>
    @endif
</footer>

{{-- Support & Contact Popup Modal --}}
<div class="modal-backdrop" id="supportModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:16px;">
    <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
        <div class="modal-head" style="margin-bottom:18px; padding-bottom:14px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
            <div style="display:flex; align-items:center; gap:10px;">
                <div style="width:36px; height:36px; border-radius:10px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <x-core::icon name="help-circle" size="20" />
                </div>
                <div>
                    <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                        <span class="bn">সহায়তা ও যোগাযোগ</span>
                        <span class="en" style="display:none;">Support & Contact</span>
                    </div>
                    <div style="font-size:12px; color:var(--ink-500); margin-top:2px;">
                        <span class="bn">যেকোনো প্রশ্ন বা কারিগরি সহায়তায় আমরা পাশে আছি</span>
                        <span class="en" style="display:none;">We are here to help with any questions or support</span>
                    </div>
                </div>
            </div>
            <button type="button" class="support-modal-close" style="width:28px; height:28px; font-size:20px; cursor:pointer; background:none; border:none; color:var(--ink-400); border-radius:6px; display:flex; align-items:center; justify-content:center; line-height:1;" aria-label="Close">&times;</button>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px;">
            {{-- Phone & WhatsApp Card --}}
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--green-100); color:var(--green-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                        <x-core::icon name="phone" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">ফোন / হোয়াটসঅ্যাপ</span>
                            <span class="en" style="display:none;">Phone / WhatsApp</span>
                        </div>
                        <div style="font-size:15px; font-weight:700; color:var(--ink-900); font-family:var(--font-mono, monospace); margin-top:3px;">
                            {{ $supportPhone }}
                        </div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:6px; margin-top:2px;">
                    <x-core::button
                        :href="'tel:+' . $cleanPhone"
                        size="xs"
                        variant="secondary"
                        icon="phone"
                        title="কল করুন / Call"
                    >
                        <span class="bn">কল</span>
                        <span class="en" style="display:none;">Call</span>
                    </x-core::button>
                    <x-core::button
                        :href="'https://wa.me/' . $cleanPhone"
                        target="_blank"
                        size="xs"
                        color="primary"
                        icon="message-square"
                        title="হোয়াটসঅ্যাপে চ্যাট করুন / WhatsApp Chat"
                    >
                        <span class="bn">WhatsApp</span>
                        <span class="en" style="display:none;">WhatsApp</span>
                    </x-core::button>
                </div>
            </div>

            {{-- Support Email Card --}}
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div style="display:flex; align-items:flex-start; gap:12px;">
                    <div style="width:36px; height:36px; border-radius:8px; background:var(--blue-100); color:var(--blue-ink); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                        <x-core::icon name="mail" size="18" />
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                            <span class="bn">সাপোর্ট ইমেইল</span>
                            <span class="en" style="display:none;">Support Email</span>
                        </div>
                        <div style="font-size:13.5px; font-weight:700; color:var(--ink-900); font-family:var(--font-mono, monospace); margin-top:3px; word-break:break-all;">
                            {{ $supportEmail }}
                        </div>
                    </div>
                </div>
                <div style="margin-top:2px;">
                    <x-core::button
                        :href="'mailto:' . $supportEmail"
                        size="xs"
                        variant="secondary"
                        icon="send"
                        title="ইমেইল পাঠান / Send Email"
                    >
                        <span class="bn">ইমেইল</span>
                        <span class="en" style="display:none;">Email</span>
                    </x-core::button>
                </div>
            </div>

            {{-- Office Location Card --}}
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px 16px; display:flex; align-items:flex-start; gap:12px;">
                <div style="width:36px; height:36px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px;">
                    <x-core::icon name="map-pin" size="18" />
                </div>
                <div>
                    <div style="font-size:11.5px; font-weight:600; color:var(--ink-500); text-transform:uppercase; letter-spacing:0.5px;">
                        <span class="bn">অফিস ঠিকানা</span>
                        <span class="en" style="display:none;">Office Location</span>
                    </div>
                    <div style="font-size:13px; font-weight:600; color:var(--ink-800); margin-top:4px; line-height:1.5;">
                        {{ $officeAddress }}
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end;">
            <x-core::button type="button" variant="secondary" size="sm" class="support-modal-close">
                <span class="bn">বন্ধ করুন</span>
                <span class="en" style="display:none;">Close</span>
            </x-core::button>
        </div>
    </div>
</div>

<script>
$(function () {
    function openSupportModal() {
        $('#supportModal').css('display', 'flex').addClass('open');
    }

    function closeSupportModal() {
        $('#supportModal').removeClass('open').hide();
    }

    window.openSupportModal = openSupportModal;
    window.closeSupportModal = closeSupportModal;

    $(document).on('click', 'a[href="{{ route('home') }}#faq"], .support-modal-trigger', function (e) {
        e.preventDefault();
        openSupportModal();
    });

    $(document).on('click', '.support-modal-close', function (e) {
        e.preventDefault();
        closeSupportModal();
    });

    $(document).on('click', '#supportModal', function (e) {
        if ($(e.target).is('#supportModal')) {
            closeSupportModal();
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#supportModal').hasClass('open')) {
            closeSupportModal();
        }
    });
});
</script>
