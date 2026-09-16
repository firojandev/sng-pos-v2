@php
    $shop = auth()->user()?->shop ?? $sale->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop?->printerSetting ?? \Modules\Shop\Models\PrinterSetting::getDefaultForShop($shop->id ?? 1);
@endphp
<div class="modal-backdrop" id="saleInvoiceModal" style="z-index:1050;">
    <div class="modal-box" style="width:{{ $printerSetting->isThermal() ? '500px' : ($printerSetting->isA5() ? '600px' : '760px') }}; max-width:96vw; max-height:94vh; padding:0; border-radius:12px; background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal Header: "Sale Invoice" with Close Button --}}
        <div class="modal-head" style="padding:14px 20px; border-bottom:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; background:var(--card, #ffffff);">
            <h3 style="font-size:18px; font-weight:700; color:var(--ink-900, #0f172a); margin:0; font-family:'Noto Sans Bengali', sans-serif;">
                <span class="bn">বিক্রয় ইনভয়েস</span>
                <span class="en">Sale Invoice</span>
            </h3>
            <x-core::button
                type="button"
                variant="ghost"
                size="sm"
                icon="x"
                icon-only
                class="modal-close-btn"
                onclick="closeModal('saleInvoiceModal')"
            />
        </div>

        {{-- Modal Body: Gray Canvas containing the White Invoice Sheet --}}
        <div class="modal-body" style="padding:20px; background:#f4f5f7; overflow-y:auto; overflow-x:hidden; flex:1;">
            @include('sales::sales._invoice_sheet')
        </div>

        {{-- Modal Footer: Actions Bar --}}
        {{-- Modal Footer: Actions Bar (Strictly One Row) --}}
        <div class="modal-foot" style="padding:10px 16px; background:var(--card, #ffffff); border-top:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; flex-wrap:nowrap !important; gap:8px; overflow-x:auto; -webkit-overflow-scrolling:touch;">
            <div style="display:flex; align-items:center; gap:6px; flex-wrap:nowrap !important; flex-shrink:0;">
                {{-- Send via Email --}}
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="mail"
                    id="btnSendSaleEmail"
                    data-url="{{ route('sales.send-email', $sale) }}"
                    data-email="{{ $sale->customer?->email ?? '' }}"
                    title="ইমেইল পাঠান / Send Email"
                    style="white-space:nowrap !important; flex-shrink:0;"
                >
                    <span class="bn">ইমেইল পাঠান</span>
                    <span class="en">Send Email</span>
                </x-core::button>

                {{-- Copy Public Link --}}
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="copy"
                    id="btnCopyPublicInvoiceLink"
                    data-url="{{ $sale->public_url }}"
                    title="লিংক কপি / Copy Link"
                    style="white-space:nowrap !important; flex-shrink:0;"
                >
                    <span class="bn">লিংক কপি</span>
                    <span class="en">Copy Link</span>
                </x-core::button>

                {{-- WhatsApp Send (via personal WhatsApp sidecar) --}}
                @php
                    $rawPhone = preg_replace('/[^0-9]/', '', (string) ($sale->customer?->phone ?? ''));
                    $customerPhone = $rawPhone ? (str_starts_with($rawPhone, '88') ? $rawPhone : '88' . $rawPhone) : '';
                @endphp
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="share-2"
                    id="btnShareWhatsApp"
                    data-url="{{ route('sales.send-whatsapp', $sale) }}"
                    data-phone="{{ $customerPhone }}"
                    title="হোয়াটসঅ্যাপ / WhatsApp"
                    style="white-space:nowrap !important; flex-shrink:0;"
                >
                    <span class="bn">হোয়াটসঅ্যাপ</span>
                    <span class="en">WhatsApp</span>
                </x-core::button>
            </div>

            {{-- Print Button --}}
            <x-core::button
                type="button"
                color="primary"
                size="sm"
                icon="printer"
                class="btn-print-sale-invoice"
                onclick="printSaleInvoice('{{ route('sales.print-invoice', $sale) }}')"
                title="প্রিন্ট করুন / Print"
                style="white-space:nowrap !important; flex-shrink:0;"
            >
                <span class="bn">প্রিন্ট করুন</span>
                <span class="en">Print</span>
            </x-core::button>
        </div>
    </div>
</div>

<script>
    window.printSaleInvoice = function(url) {
        var printUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'autoprint=1';
        var printWindow = window.open(printUrl, '_blank');
        if (printWindow) {
            printWindow.focus();
            return;
        }

        let iframe = document.getElementById('sale-print-iframe');
        if (iframe) {
            iframe.remove();
        }
        iframe = document.createElement('iframe');
        iframe.id = 'sale-print-iframe';
        iframe.style.position = 'fixed';
        iframe.style.right = '0';
        iframe.style.bottom = '0';
        iframe.style.width = '100px';
        iframe.style.height = '100px';
        iframe.style.opacity = '0.01';
        iframe.style.border = '0';
        iframe.onload = function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                window.location.href = printUrl;
            }
        };
        iframe.src = printUrl;
        document.body.appendChild(iframe);
    };

    $(document).off('click.saleInvoiceModal').on('click.saleInvoiceModal', '#saleInvoiceModal', function (e) {
        if ($(e.target).is('#saleInvoiceModal')) {
            closeModal('saleInvoiceModal');
        }
    });

    function isEn() {
        return $('body').hasClass('lang-en') || $('html').hasClass('lang-en');
    }

    // Copy public invoice link
    $(document).off('click.copyPublicInvoice').on('click.copyPublicInvoice', '#btnCopyPublicInvoiceLink', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        var en = isEn();
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function () {
                if (typeof window.showToast === 'function') {
                    window.showToast(en ? 'Public invoice link copied!' : 'ইনভয়েসের পাবলিক লিংক কপি করা হয়েছে!', 'success');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: en ? 'Link Copied' : 'লিংক কপি করা হয়েছে',
                        text: url,
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        } else {
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(url).select();
            document.execCommand('copy');
            $temp.remove();
            if (typeof window.showToast === 'function') {
                window.showToast(en ? 'Public invoice link copied!' : 'ইনভয়েসের পাবলিক লিংক কপি করা হয়েছে!', 'success');
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: en ? 'Link Copied' : 'লিংক কপি করা হয়েছে',
                    text: url,
                    timer: 2500,
                    showConfirmButton: false
                });
            }
        }
    });

    // Send invoice via email
    $(document).off('click.sendSaleEmail').on('click.sendSaleEmail', '#btnSendSaleEmail', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');
        var customerEmail = String($btn.attr('data-email') || $btn.data('email') || '').trim();
        var en = isEn();

        function doSend(emailToSend) {
            $btn.prop('disabled', true).addClass('loading');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Sending email...' : 'ইমেইল পাঠানো হচ্ছে...',
                    text: en ? 'Please wait.' : 'অনুগ্রহ করে অপেক্ষা করুন।',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    email: emailToSend
                },
                success: function (res) {
                    $btn.prop('disabled', false).removeClass('loading');
                    $btn.attr('data-email', emailToSend);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: en ? 'Email Sent Successfully!' : 'ইমেইল সফলভাবে পাঠানো হয়েছে!',
                            text: res.message || (en ? 'Invoice has been sent to customer email.' : 'গ্রাহকের ইমেইলে ইনভয়েস পৌঁছে গেছে।'),
                            confirmButtonText: en ? 'OK' : 'ঠিক আছে'
                        });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).removeClass('loading');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (en ? 'Could not send email. Please try again.' : 'ইমেইল পাঠানো যায়নি। অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: en ? 'Failed' : 'ব্যর্থ হয়েছে',
                            text: msg,
                            confirmButtonText: en ? 'OK' : 'ঠিক আছে'
                        });
                    }
                }
            });
        }

        if (customerEmail !== '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Send Invoice Email' : 'ইনভয়েস ইমেইল পাঠান',
                    text: en
                        ? 'Invoice will be sent to (' + customerEmail + '). Do you want to proceed?'
                        : 'গ্রাহকের সংরক্ষিত ইমেইল (' + customerEmail + ') ঠিকানায় ইনভয়েস পাঠানো হবে। আপনি কি পাঠাতে চান?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: en ? 'Yes, Send' : 'হ্যাঁ, পাঠান',
                    cancelButtonText: en ? 'Cancel' : 'বাতিল'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        doSend(customerEmail);
                    }
                });
            } else {
                doSend(customerEmail);
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Enter Email Address' : 'ইমেইল ঠিকানা লিখুন',
                    text: en ? 'Customer has no saved email. Please enter recipient email:' : 'গ্রাহকের কোনো ইমেইল ঠিকানা সংরক্ষিত নেই। অনুগ্রহ করে প্রাপকের ইমেইল লিখুন:',
                    input: 'email',
                    inputPlaceholder: 'customer@example.com',
                    showCancelButton: true,
                    confirmButtonText: en ? 'Send Email' : 'ইমেইল পাঠান',
                    cancelButtonText: en ? 'Cancel' : 'বাতিল',
                    inputValidator: function (value) {
                        if (!value) {
                            return en ? 'Please provide a valid email address!' : 'একটি সঠিক ইমেইল ঠিকানা প্রদান করুন!';
                        }
                    }
                }).then(function (result) {
                    if (result.isConfirmed && result.value) {
                        doSend(result.value);
                    }
                });
            } else {
                var manualEmail = prompt(en ? 'Please enter customer email address:' : 'অনুগ্রহ করে গ্রাহকের ইমেইল ঠিকানা লিখুন:');
                if (manualEmail) {
                    doSend(manualEmail);
                }
            }
        }
    });

    // Send invoice via WhatsApp (personal number via sidecar)
    $(document).off('click.sendSaleWhatsApp').on('click.sendSaleWhatsApp', '#btnShareWhatsApp', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');
        var customerPhone = String($btn.attr('data-phone') || $btn.data('phone') || '').trim();
        var en = isEn();

        function doWaSend(phoneToSend) {
            $btn.prop('disabled', true).addClass('loading');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Sending via WhatsApp...' : 'হোয়াটসঅ্যাপে পাঠানো হচ্ছে...',
                    text: en ? 'Please wait.' : 'অনুগ্রহ করে অপেক্ষা করুন।',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    phone: phoneToSend
                },
                success: function (res) {
                    $btn.prop('disabled', false).removeClass('loading');
                    $btn.attr('data-phone', phoneToSend);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: en ? 'Sent Successfully!' : 'সফলভাবে পাঠানো হয়েছে!',
                            text: res.message || (en ? 'Invoice has reached customer WhatsApp.' : 'গ্রাহকের হোয়াটসঅ্যাপে ইনভয়েস পৌঁছে গেছে।'),
                            confirmButtonText: en ? 'OK' : 'ঠিক আছে'
                        });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).removeClass('loading');
                    var resp = xhr.responseJSON || {};
                    var msg = resp.message || (en ? 'Could not send WhatsApp message.' : 'হোয়াটসঅ্যাপে পাঠানো যায়নি।');
                    var fallbackUrl = resp.fallback_url || '';

                    if (typeof Swal !== 'undefined') {
                        var actionsHtml = msg;
                        if (resp.settings_url) {
                            actionsHtml += '<br><br><a href="' + resp.settings_url + '" target="_blank" style="display:inline-flex; align-items:center; gap:6px; text-decoration:none; padding:7px 16px; border-radius:6px; background:#2563eb; color:#ffffff; font-weight:600; font-size:13px;">' + (en ? 'Go to QR Code Scan Page' : 'QR কোড স্ক্যান পেজে যান') + '</a>';
                        }
                        Swal.fire({
                            icon: 'warning',
                            title: en ? 'WhatsApp Not Connected' : 'হোয়াটসঅ্যাপ সংযুক্ত নেই',
                            html: actionsHtml,
                            showCancelButton: !!fallbackUrl,
                            confirmButtonText: fallbackUrl ? (en ? 'Send via WhatsApp Web' : 'WhatsApp Web দিয়ে পাঠান') : (en ? 'OK' : 'ঠিক আছে'),
                            cancelButtonText: en ? 'Cancel' : 'বাতিল'
                        }).then(function (result) {
                            if (result.isConfirmed && fallbackUrl) {
                                window.open(fallbackUrl, '_blank');
                            }
                        });
                    } else if (fallbackUrl) {
                        window.open(fallbackUrl, '_blank');
                    }
                }
            });
        }

        if (customerPhone !== '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Send Invoice via WhatsApp' : 'হোয়াটসঅ্যাপে ইনভয়েস পাঠান',
                    text: en
                        ? 'Invoice will be sent to (' + customerPhone + ') from your personal WhatsApp.'
                        : 'গ্রাহকের নম্বর (' + customerPhone + ') এ আপনার ব্যক্তিগত হোয়াটসঅ্যাপ থেকে ইনভয়েস পাঠানো হবে।',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: en ? 'Yes, Send' : 'হ্যাঁ, পাঠান',
                    cancelButtonText: en ? 'Cancel' : 'বাতিল'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        doWaSend(customerPhone);
                    }
                });
            } else {
                doWaSend(customerPhone);
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: en ? 'Enter Phone Number' : 'ফোন নম্বর লিখুন',
                    text: en ? 'Customer has no saved phone number. Please enter recipient phone number:' : 'গ্রাহকের কোনো ফোন নম্বর সংরক্ষিত নেই। অনুগ্রহ করে প্রাপকের ফোন নম্বর লিখুন:',
                    input: 'text',
                    inputPlaceholder: '01XXXXXXXXX',
                    showCancelButton: true,
                    confirmButtonText: en ? 'Send WhatsApp' : 'হোয়াটসঅ্যাপে পাঠান',
                    cancelButtonText: en ? 'Cancel' : 'বাতিল',
                    inputValidator: function (value) {
                        if (!value || value.replace(/[^0-9]/g, '').length < 10) {
                            return en ? 'Please provide a valid phone number!' : 'একটি সঠিক ফোন নম্বর প্রদান করুন!';
                        }
                    }
                }).then(function (result) {
                    if (result.isConfirmed && result.value) {
                        doWaSend(result.value.replace(/[^0-9]/g, ''));
                    }
                });
            } else {
                var manualPhone = prompt(en ? 'Please enter customer phone number:' : 'অনুগ্রহ করে গ্রাহকের ফোন নম্বর লিখুন:');
                if (manualPhone) {
                    doWaSend(manualPhone.replace(/[^0-9]/g, ''));
                }
            }
        }
    });
</script>
