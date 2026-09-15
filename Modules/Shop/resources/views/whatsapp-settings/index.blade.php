<x-core::layout
    title="হোয়াটসঅ্যাপ সেটিংস"
    title-en="WhatsApp Settings"
    subtitle="আপনার ব্যক্তিগত হোয়াটসঅ্যাপ নম্বর পেয়ার করে ইনভয়েস লিংক সরাসরি গ্রাহককে পাঠানোর ব্যবস্থা পরিচালনা করুন"
    subtitle-en="Pair your personal WhatsApp account to send invoice links directly to customers"
    active="whatsapp-settings"
>
    @if (session('status'))
        <div style="background:var(--green-100); border:1px solid var(--green-ic-bg); color:var(--green-ink); border-radius:12px; padding:12px 18px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px;">
            <x-core::icon name="check-circle" size="18" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <style>
        .wa-qr-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #ffffff;
            border: 2px dashed var(--border);
            border-radius: 16px;
            min-height: 280px;
            text-align: center;
            position: relative;
        }
        .wa-qr-img {
            max-width: 240px;
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }
        .wa-instruction-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }
        .wa-step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(37, 211, 102, 0.12);
            color: #16a34a;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        @media (max-width: 900px) {
            .wa-settings-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div style="width:100%; max-width:1160px;">
        <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px; align-items:start;" class="wa-settings-grid">
            {{-- Left Column: Session Card & QR Scanner --}}
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
                    {{-- Header with Status Badge --}}
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border); flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div style="width:38px; height:38px; border-radius:10px; background:rgba(37, 211, 102, 0.12); color:#16a34a; display:flex; align-items:center; justify-content:center;">
                                <x-core::icon name="message-circle" size="20" />
                            </div>
                            <div>
                                <h3 style="font-size:16px; font-weight:700; color:var(--ink-900); margin:0;">
                                    <span class="bn">ব্যক্তিগত হোয়াটসঅ্যাপ অ্যাকাউন্ট</span>
                                    <span class="en">Personal WhatsApp Account</span>
                                </h3>
                                <div style="font-size:12px; color:var(--ink-600); margin-top:2px;">
                                    <span class="bn">সেশন: <strong>main</strong> &bull; whatsapp-web.js সাইডকার</span>
                                    <span class="en">Session: <strong>main</strong> &bull; whatsapp-web.js Sidecar</span>
                                </div>
                            </div>
                        </div>

                        <div id="waStatusBadgeContainer">
                            @if ($sessionStatus === 'ready')
                                <x-core::badge color="green" size="sm" dot>
                                    <span class="bn">সংযুক্ত (Connected)</span>
                                    <span class="en">Connected</span>
                                </x-core::badge>
                            @elseif ($sessionStatus === 'qr')
                                <x-core::badge color="gold" size="sm" dot>
                                    <span class="bn">কিউআর কোড স্ক্যান করুন</span>
                                    <span class="en">Scan QR Code</span>
                                </x-core::badge>
                            @elseif ($isRunning)
                                <x-core::badge color="secondary" size="sm">
                                    <span class="bn">সংযোগ বিচ্ছিন্ন</span>
                                    <span class="en">Disconnected</span>
                                </x-core::badge>
                            @else
                                <x-core::badge color="danger" size="sm">
                                    <span class="bn">সার্ভিস বন্ধ</span>
                                    <span class="en">Service Stopped</span>
                                </x-core::badge>
                            @endif
                        </div>
                    </div>

                    {{-- Dynamic State Display --}}
                    <div id="waStateReady" style="{{ $sessionStatus === 'ready' ? '' : 'display:none;' }}">
                        <div style="padding:20px; background:var(--green-100); border:1px solid var(--green-ic-bg); border-radius:12px; margin-bottom:20px;">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div style="width:44px; height:44px; border-radius:50%; background:#16a34a; color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <x-core::icon name="check" size="24" />
                                </div>
                                <div style="flex:1;">
                                    <div style="font-size:15px; font-weight:700; color:var(--green-ink);">
                                        <span class="bn">হোয়াটসঅ্যাপ সফলভাবে সংযুক্ত আছে!</span>
                                        <span class="en">WhatsApp is Successfully Connected!</span>
                                    </div>
                                    <div style="font-size:13px; color:var(--ink-700); margin-top:3px;" id="waConnectedDetails">
                                        @if (!empty($sessionInfo['pushname']) || !empty($sessionInfo['phone']))
                                            <span class="bn">নম্বর: <strong>+{{ $sessionInfo['phone'] ?? '' }}</strong> &bull; নাম: <strong>{{ $sessionInfo['pushname'] ?? 'হোয়াটসঅ্যাপ ইউজার' }}</strong></span>
                                            <span class="en">Phone: <strong>+{{ $sessionInfo['phone'] ?? '' }}</strong> &bull; Name: <strong>{{ $sessionInfo['pushname'] ?? 'WhatsApp User' }}</strong></span>
                                        @else
                                            <span class="bn">আপনার ব্যক্তিগত হোয়াটসঅ্যাপ সক্রিয় আছে এবং ইনভয়েস পাঠাতে প্রস্তুত।</span>
                                            <span class="en">Your personal WhatsApp account is active and ready to send invoices.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                            <div style="font-size:12.5px; color:var(--ink-600);">
                                <span class="bn">ইনভয়েস মডাল থেকে "হোয়াটসঅ্যাপ" চাপলেই সরাসরি এই নম্বর থেকে মেসেজ পাঠানো হবে।</span>
                                <span class="en">Clicking "WhatsApp" in the invoice modal will send messages directly from this number.</span>
                            </div>
                            <x-core::button
                                type="button"
                                color="danger"
                                size="sm"
                                icon="log-out"
                                id="btnDisconnectWhatsApp"
                            >
                                <span class="bn">সংযোগ বিচ্ছিন্ন করুন</span>
                                <span class="en">Disconnect</span>
                            </x-core::button>
                        </div>
                    </div>

                    {{-- QR Code Area --}}
                    <div id="waStateQr" style="{{ $sessionStatus === 'qr' ? '' : 'display:none;' }}">
                        <div class="wa-qr-container">
                            <div id="waQrImageWrapper">
                                @if (!empty($qrCode))
                                    <img src="{{ $qrCode }}" alt="WhatsApp QR Code" class="wa-qr-img" id="waQrImg" />
                                @else
                                    <div style="display:flex; flex-direction:column; align-items:center; gap:10px; color:var(--ink-600);">
                                        <div class="spinner-border" style="width:36px; height:36px; border:3px solid #cbd5e1; border-top-color:#16a34a; border-radius:50%; animation:spin 1s linear infinite;"></div>
                                        <div>
                                            <span class="bn">QR কোড তৈরি হচ্ছে, অনুগ্রহ করে অপেক্ষা করুন...</span>
                                            <span class="en">Generating QR code, please wait...</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div style="margin-top:14px; font-size:13px; font-weight:600; color:var(--ink-900);">
                                <x-core::icon name="maximize" size="14" style="vertical-align:middle; margin-right:4px;" />
                                <span class="bn">আপনার মোবাইলের WhatsApp দিয়ে এই QR কোডটি স্ক্যান করুন</span>
                                <span class="en">Scan this QR code with your mobile WhatsApp</span>
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-top:4px;">
                                <span class="bn">স্ক্যান করার সাথে সাথে এটি স্বয়ংক্রিয়ভাবে সংযুক্ত হয়ে যাবে</span>
                                <span class="en">It will connect automatically once scanned</span>
                            </div>
                        </div>

                        <div style="margin-top:16px; display:flex; justify-content:center;">
                            <x-core::button
                                type="button"
                                variant="secondary"
                                size="sm"
                                icon="refresh-cw"
                                id="btnRefreshQr"
                            >
                                <span class="bn">পুনরায় লোড করুন</span>
                                <span class="en">Reload QR</span>
                            </x-core::button>
                        </div>
                    </div>

                    {{-- Disconnected / Not Started Area --}}
                    <div id="waStateDisconnected" style="{{ in_array($sessionStatus, ['ready', 'qr']) ? 'display:none;' : '' }}">
                        <div style="padding:28px 20px; text-align:center; background:var(--paper); border-radius:12px; border:1px solid var(--border); margin-bottom:16px;">
                            <div style="width:54px; height:54px; border-radius:16px; background:rgba(37, 211, 102, 0.12); color:#16a34a; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                                <x-core::icon name="qr-code" size="28" />
                            </div>
                            <h4 style="font-size:15.5px; font-weight:700; color:var(--ink-900); margin:0 0 6px;">
                                <span class="bn">হোয়াটসঅ্যাপ সংযোগ শুরু করুন</span>
                                <span class="en">Start WhatsApp Connection</span>
                            </h4>
                            <p style="font-size:13px; color:var(--ink-600); margin:0 0 18px; max-width:440px; margin-left:auto; margin-right:auto;">
                                <span class="bn">বোতামে ক্লিক করে কিউআর কোড জেনারেট করুন এবং আপনার ব্যক্তিগত হোয়াটসঅ্যাপ অ্যাপ দিয়ে স্ক্যান করে যুক্ত করুন।</span>
                                <span class="en">Click the button below to generate a QR code and scan it with your personal WhatsApp app to connect.</span>
                            </p>
                            <x-core::button
                                type="button"
                                color="primary"
                                size="sm"
                                icon="zap"
                                id="btnStartWhatsApp"
                            >
                                <span class="bn">হোয়াটসঅ্যাপ সেশন শুরু ও QR কোড প্রদর্শন</span>
                                <span class="en">Start WhatsApp & Show QR Code</span>
                            </x-core::button>
                        </div>

                        @if (! $isInstalled)
                            <div style="padding:14px 18px; background:var(--gold-100); border:1px solid rgba(217, 119, 6, 0.2); border-radius:10px; font-size:12.5px; color:var(--gold-ink); display:flex; align-items:flex-start; gap:10px;">
                                <x-core::icon name="alert-triangle" size="18" style="flex-shrink:0; margin-top:2px;" />
                                <div>
                                    <span class="bn"><strong>সাইডকার মডিউল ইনস্টল নেই:</strong> অনুগ্রহ করে সার্ভার বা লোকাল মেশিনে নিচের কমান্ডটি চালান:</span>
                                    <span class="en"><strong>Sidecar module not installed:</strong> Please run the following command in terminal:</span>
                                    <code style="display:block; margin-top:6px; background:#ffffff; padding:4px 8px; border-radius:6px; font-family:monospace; color:#0f172a; border:1px solid rgba(0,0,0,0.08);">php artisan whatsapp:sidecar:install</code>
                                </div>
                            </div>
                        @elseif (! $isRunning)
                            <div style="padding:14px 18px; background:var(--blue-100); border:1px solid var(--blue-ic-bg); border-radius:10px; font-size:12.5px; color:var(--blue-ink); display:flex; align-items:flex-start; gap:10px;">
                                <x-core::icon name="info" size="18" style="flex-shrink:0; margin-top:2px;" />
                                <div>
                                    <span class="bn"><strong>সাইডকার সার্ভিস বন্ধ:</strong> আপনি "হোয়াটসঅ্যাপ সেশন শুরু" বাটনে চাপলে এটি স্বয়ংক্রিয়ভাবে চালু করার চেষ্টা করবে। অথবা টার্মিনালে চালাতে পারেন:</span>
                                    <span class="en"><strong>Sidecar service stopped:</strong> Clicking "Start WhatsApp" will attempt to launch it, or you can run in terminal:</span>
                                    <code style="display:block; margin-top:6px; background:#ffffff; padding:4px 8px; border-radius:6px; font-family:monospace; color:#0f172a; border:1px solid rgba(0,0,0,0.08);">php artisan whatsapp:sidecar:start</code>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Test Message Card (When Connected) --}}
                <div id="waTestMessageCard" style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card); {{ $sessionStatus === 'ready' ? '' : 'display:none;' }}">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                        <x-core::icon name="send" size="18" style="color:var(--teal-800);" />
                        <h4 style="font-size:15px; font-weight:700; color:var(--ink-900); margin:0;">
                            <span class="bn">টেস্ট বার্তা পাঠান (Test Connection)</span>
                            <span class="en">Send Test Message (Verify Connection)</span>
                        </h4>
                    </div>
                    <p style="font-size:12.5px; color:var(--ink-600); margin:0 0 14px;">
                        <span class="bn">সংযোগ সফল হয়েছে কিনা নিশ্চিত হতে যেকোনো একটি হোয়াটসঅ্যাপ নম্বরে টেস্ট বার্তা পাঠিয়ে যাচাই করতে পারেন।</span>
                        <span class="en">Send a test WhatsApp message to any phone number to verify that your connection is functioning properly.</span>
                    </p>

                    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                        <div style="flex:1; min-width:200px;">
                            <x-core::input
                                type="text"
                                id="testPhoneInput"
                                placeholder="01XXXXXXXXX"
                                size="sm"
                                icon="phone"
                                :no-margin="true"
                            />
                        </div>
                        <x-core::button
                            type="button"
                            color="primary"
                            size="sm"
                            icon="send"
                            id="btnSendTestMessage"
                        >
                            <span class="bn">টেস্ট বার্তা পাঠান</span>
                            <span class="en">Send Test Message</span>
                        </x-core::button>
                    </div>
                </div>
            </div>

            {{-- Right Column: Step-by-step Instructions --}}
            <div style="display:flex; flex-direction:column; gap:20px;">
                <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                        <div style="width:34px; height:34px; border-radius:8px; background:rgba(37, 211, 102, 0.12); color:#16a34a; display:flex; align-items:center; justify-content:center;">
                            <x-core::icon name="smartphone" size="18" />
                        </div>
                        <h4 style="font-size:15px; font-weight:700; color:var(--ink-900); margin:0;">
                            <span class="bn">মোবাইল থেকে কিভাবে স্ক্যান করবেন?</span>
                            <span class="en">How to scan from mobile?</span>
                        </h4>
                    </div>

                    <div class="wa-instruction-step">
                        <div class="wa-step-num">
                            <span class="bn">১</span>
                            <span class="en">1</span>
                        </div>
                        <div style="font-size:13.5px; color:var(--ink-900); line-height:1.5;">
                            <span class="bn">আপনার মোবাইল ফোনে <strong>WhatsApp</strong> অ্যাপটি ওপেন করুন।</span>
                            <span class="en">Open the <strong>WhatsApp</strong> app on your mobile phone.</span>
                        </div>
                    </div>

                    <div class="wa-instruction-step">
                        <div class="wa-step-num">
                            <span class="bn">২</span>
                            <span class="en">2</span>
                        </div>
                        <div style="font-size:13.5px; color:var(--ink-900); line-height:1.5;">
                            <span class="bn">উপরের ডানপাশের <strong>৩টি ডট (⋮)</strong> মেনু (Android) অথবা নিচের <strong>Settings</strong> (iPhone) এ ট্যাপ করুন।</span>
                            <span class="en">Tap the <strong>three dots (⋮)</strong> menu (Android) or <strong>Settings</strong> (iPhone).</span>
                        </div>
                    </div>

                    <div class="wa-instruction-step">
                        <div class="wa-step-num">
                            <span class="bn">৩</span>
                            <span class="en">3</span>
                        </div>
                        <div style="font-size:13.5px; color:var(--ink-900); line-height:1.5;">
                            <span class="bn">তালিকা থেকে <strong>Linked Devices (সংযুক্ত ডিভাইস)</strong> অপশনে ক্লিক করুন।</span>
                            <span class="en">Select <strong>Linked Devices</strong> from the options.</span>
                        </div>
                    </div>

                    <div class="wa-instruction-step">
                        <div class="wa-step-num">
                            <span class="bn">৪</span>
                            <span class="en">4</span>
                        </div>
                        <div style="font-size:13.5px; color:var(--ink-900); line-height:1.5;">
                            <span class="bn"><strong>Link a Device</strong> বোতাম চাপুন এবং স্ক্রিনে প্রদর্শিত <strong>QR কোডটি</strong> স্ক্যান করুন।</span>
                            <span class="en">Tap <strong>Link a Device</strong> and scan the <strong>QR code</strong> on your screen.</span>
                        </div>
                    </div>

                    <div style="padding:14px; background:var(--paper); border-radius:10px; border:1px solid var(--border); margin-top:20px; font-size:12px; color:var(--ink-600); line-height:1.5;">
                        <div style="font-weight:700; color:var(--ink-900); margin-bottom:4px; display:flex; align-items:center; gap:6px;">
                            <x-core::icon name="shield-check" size="14" style="color:#16a34a;" />
                            <span class="bn">নিরাপত্তা ও সুবিধা:</span>
                            <span class="en">Security & Benefits:</span>
                        </div>
                        <div class="bn">
                            &bull; এটি আপনার ব্যক্তিগত WhatsApp ওয়েবের মতোই কাজ করে।<br>
                            &bull; কোনো পেইড Meta API প্রয়োজন হয় না।<br>
                            &bull; একবার স্ক্যান করলে এটি স্থায়ীভাবে সংরক্ষিত থাকবে।
                        </div>
                        <div class="en">
                            &bull; Works just like official WhatsApp Web.<br>
                            &bull; No paid Meta Business API required.<br>
                            &bull; Session persists automatically once paired.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

    <script>
        $(function () {
            var pollingInterval = null;
            var currentStatus = '{{ $sessionStatus }}';

            function isEn() {
                return $('body').hasClass('lang-en') || $('html').hasClass('lang-en');
            }

            function updateUIForStatus(status, qr, info) {
                currentStatus = status;

                if (status === 'ready') {
                    $('#waStateReady').show();
                    $('#waStateQr').hide();
                    $('#waStateDisconnected').hide();
                    $('#waTestMessageCard').show();
                    $('#waStatusBadgeContainer').html(
                        '<span class="badge badge-green badge-sm badge-dot">' +
                            '<span class="bn">সংযুক্ত (Connected)</span>' +
                            '<span class="en">Connected</span>' +
                        '</span>'
                    );

                    if (info && (info.phone || info.pushname)) {
                        var phoneStr = info.phone ? '+' + info.phone : '';
                        var nameStr = info.pushname || '';
                        $('#waConnectedDetails').html(
                            '<span class="bn">নম্বর: <strong>' + phoneStr + '</strong> &bull; নাম: <strong>' + (nameStr || 'হোয়াটসঅ্যাপ ইউজার') + '</strong></span>' +
                            '<span class="en">Phone: <strong>' + phoneStr + '</strong> &bull; Name: <strong>' + (nameStr || 'WhatsApp User') + '</strong></span>'
                        );
                    }
                    stopPolling();
                } else if (status === 'qr') {
                    $('#waStateReady').hide();
                    $('#waStateQr').show();
                    $('#waStateDisconnected').hide();
                    $('#waTestMessageCard').hide();
                    $('#waStatusBadgeContainer').html(
                        '<span class="badge badge-gold badge-sm badge-dot">' +
                            '<span class="bn">কিউআর কোড স্ক্যান করুন</span>' +
                            '<span class="en">Scan QR Code</span>' +
                        '</span>'
                    );

                    if (qr) {
                        $('#waQrImageWrapper').html('<img src="' + qr + '" alt="WhatsApp QR Code" class="wa-qr-img" id="waQrImg" />');
                    }
                    startPolling();
                } else {
                    $('#waStateReady').hide();
                    $('#waStateQr').hide();
                    $('#waStateDisconnected').show();
                    $('#waTestMessageCard').hide();
                    $('#waStatusBadgeContainer').html(
                        '<span class="badge badge-secondary badge-sm">' +
                            '<span class="bn">সংযোগ বিচ্ছিন্ন</span>' +
                            '<span class="en">Disconnected</span>' +
                        '</span>'
                    );
                    stopPolling();
                }
            }

            function startPolling() {
                if (pollingInterval) return;
                pollingInterval = setInterval(function () {
                    $.ajax({
                        url: '{{ route('whatsapp-settings.status') }}',
                        type: 'GET',
                        headers: { 'Accept': 'application/json' },
                        success: function (res) {
                            if (res.status && res.status !== currentStatus) {
                                updateUIForStatus(res.status, res.qr, res.info);
                                if (res.status === 'ready' && typeof Swal !== 'undefined') {
                                    var en = isEn();
                                    Swal.fire({
                                        icon: 'success',
                                        title: en ? 'Connected!' : 'সংযুক্ত হয়েছে!',
                                        text: en ? 'Your personal WhatsApp is successfully connected.' : 'আপনার ব্যক্তিগত হোয়াটসঅ্যাপ সফলভাবে কানেক্ট হয়েছে।',
                                        timer: 2500,
                                        showConfirmButton: false
                                    });
                                }
                            } else if (res.status === 'qr' && res.qr) {
                                $('#waQrImageWrapper').html('<img src="' + res.qr + '" alt="WhatsApp QR Code" class="wa-qr-img" id="waQrImg" />');
                            }
                        }
                    });
                }, 3000);
            }

            function stopPolling() {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }

            // If initial status is QR, start polling immediately
            if (currentStatus === 'qr') {
                startPolling();
            }

            // Start Session / Generate QR
            $(document).on('click', '#btnStartWhatsApp, #btnRefreshQr', function (e) {
                e.preventDefault();
                var $btn = $(this);
                $btn.prop('disabled', true);
                var en = isEn();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: en ? 'Starting WhatsApp...' : 'হোয়াটসঅ্যাপ চালু হচ্ছে...',
                        text: en ? 'Generating QR code, please wait a few seconds.' : 'QR কোড তৈরি হচ্ছে, অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন।',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                }

                $.ajax({
                    url: '{{ route('whatsapp-settings.start') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function (res) {
                        $btn.prop('disabled', false);
                        if (typeof Swal !== 'undefined') {
                            Swal.close();
                        }
                        if (res.success) {
                            updateUIForStatus(res.status, res.qr, res.info);
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: en ? 'Failed' : 'ব্যর্থ হয়েছে',
                                    text: res.message || (en ? 'Could not generate QR code.' : 'QR কোড তৈরি করা যায়নি।')
                                });
                            }
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (en ? 'Failed to start session.' : 'সেশন শুরু করতে সমস্যা হয়েছে।');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: en ? 'Error' : 'সমস্যা হয়েছে',
                                text: msg
                            });
                        }
                    }
                });
            });

            // Disconnect Session
            $(document).on('click', '#btnDisconnectWhatsApp', function (e) {
                e.preventDefault();
                var $btn = $(this);
                var en = isEn();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: en ? 'Disconnect WhatsApp?' : 'সংযোগ বিচ্ছিন্ন করতে চান?',
                        text: en ? 'Disconnecting will require scanning the QR code again to reconnect.' : 'সংযোগ বিচ্ছিন্ন করলে পুনরায় QR কোড স্ক্যান করে কানেক্ট করতে হবে।',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: en ? 'Yes, Disconnect' : 'হ্যাঁ, বিচ্ছিন্ন করুন',
                        cancelButtonText: en ? 'Cancel' : 'বাতিল'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            doDisconnect();
                        }
                    });
                } else {
                    if (confirm(en ? 'Disconnect WhatsApp?' : 'সংযোগ বিচ্ছিন্ন করতে চান?')) {
                        doDisconnect();
                    }
                }

                function doDisconnect() {
                    $btn.prop('disabled', true);
                    $.ajax({
                        url: '{{ route('whatsapp-settings.disconnect') }}',
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function (res) {
                            $btn.prop('disabled', false);
                            updateUIForStatus('disconnected');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: en ? 'Disconnected' : 'সংযোগ বিচ্ছিন্ন হয়েছে',
                                    text: res.message || (en ? 'WhatsApp session disconnected.' : 'হোয়াটসঅ্যাপ সংযোগ বিচ্ছিন্ন হয়েছে।'),
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        },
                        error: function (xhr) {
                            $btn.prop('disabled', false);
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (en ? 'Failed to disconnect.' : 'সংযোগ বিচ্ছিন্ন করতে সমস্যা হয়েছে।');
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: en ? 'Error' : 'ব্যর্থ হয়েছে',
                                    text: msg
                                });
                            }
                        }
                    });
                }
            });

            // Send Test Message
            $(document).on('click', '#btnSendTestMessage', function (e) {
                e.preventDefault();
                var phone = $('#testPhoneInput').val();
                var en = isEn();

                if (!phone || phone.trim() === '') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: en ? 'Enter Phone Number' : 'ফোন নম্বর লিখুন',
                            text: en ? 'Please enter a valid mobile number.' : 'অনুগ্রহ করে একটি সঠিক মোবাইল নম্বর লিখুন।'
                        });
                    }
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true);

                $.ajax({
                    url: '{{ route('whatsapp-settings.test') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    data: { phone: phone },
                    success: function (res) {
                        $btn.prop('disabled', false);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: en ? 'Success!' : 'সফল!',
                                text: res.message || (en ? 'Test message sent successfully!' : 'টেস্ট বার্তা সফলভাবে পাঠানো হয়েছে!')
                            });
                        }
                    },
                    error: function (xhr) {
                        $btn.prop('disabled', false);
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (en ? 'Could not send test message.' : 'টেস্ট বার্তা পাঠানো যায়নি।');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: en ? 'Failed' : 'ব্যর্থ হয়েছে',
                                text: msg
                            });
                        }
                    }
                });
            });
        });
    </script>
</x-core::layout>
