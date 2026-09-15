@php
    $shop = auth()->user()?->shop ?? $sale->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop?->printerSetting ?? \Modules\Shop\Models\PrinterSetting::getDefaultForShop($shop->id ?? 1);
@endphp
<div class="modal-backdrop" id="saleInvoiceModal" style="z-index:1050;">
    <div class="modal-box" style="width:{{ $printerSetting->isThermal() ? '460px' : ($printerSetting->isA5() ? '580px' : '760px') }}; max-width:96vw; max-height:94vh; padding:0; border-radius:12px; background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal Header: "Sale Invoice" with Close Button --}}
        <div class="modal-head" style="padding:14px 20px; border-bottom:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; background:var(--card, #ffffff);">
            <h3 style="font-size:18px; font-weight:700; color:var(--ink-900, #0f172a); margin:0; font-family:'Noto Sans Bengali', sans-serif;">
                Sale Invoice
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
        <div class="modal-foot" style="padding:12px 20px; background:var(--card, #ffffff); border-top:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;">
            <div style="display:inline-flex; align-items:center; gap:8px; flex-wrap:wrap;">
                {{-- Send via Email --}}
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="mail"
                    id="btnSendSaleEmail"
                    data-url="{{ route('sales.send-email', $sale) }}"
                    data-email="{{ $sale->customer?->email ?? '' }}"
                    title="গ্রাহককে ইমেইল পাঠান / Send via Email"
                >
                    <span>ইমেইল পাঠান</span>
                </x-core::button>

                {{-- Copy Public Link --}}
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="copy"
                    id="btnCopyPublicInvoiceLink"
                    data-url="{{ $sale->public_url }}"
                    title="পাবলিক লিংক কপি করুন / Copy Public Link"
                >
                    <span>লিংক কপি</span>
                </x-core::button>

                {{-- WhatsApp Share --}}
                @php
                    $rawPhone = preg_replace('/[^0-9]/', '', (string) ($sale->customer?->phone ?? ''));
                    $customerPhone = $rawPhone ? (str_starts_with($rawPhone, '88') ? $rawPhone : '88' . $rawPhone) : '';
                    $waText = "প্রিয় গ্রাহক, আপনার বিক্রয় ইনভয়েস #{$sale->invoice_no} দেখতে এই লিংকে ক্লিক করুন: " . $sale->public_url;
                    $waUrl = "https://api.whatsapp.com/send?" . ($customerPhone ? "phone={$customerPhone}&" : "") . "text=" . urlencode($waText);
                @endphp
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="share-2"
                    id="btnShareWhatsApp"
                    title="হোয়াটসঅ্যাপে পাঠান / Share on WhatsApp"
                    onclick="window.open('{{ $waUrl }}', '_blank')"
                >
                    <span>হোয়াটসঅ্যাপ</span>
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
            >
                <span>প্রিন্ট করুন</span>
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

    // Copy public invoice link
    $(document).off('click.copyPublicInvoice').on('click.copyPublicInvoice', '#btnCopyPublicInvoiceLink', function (e) {
        e.preventDefault();
        var url = $(this).data('url');
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(url).then(function () {
                if (typeof window.showToast === 'function') {
                    window.showToast('ইনভয়েসের পাবলিক লিংক কপি করা হয়েছে!', 'success');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'লিংক কপি করা হয়েছে',
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
                window.showToast('ইনভয়েসের পাবলিক লিংক কপি করা হয়েছে!', 'success');
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'লিংক কপি করা হয়েছে',
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
        var customerEmail = $btn.data('email');

        function doSend(emailToSend) {
            $btn.prop('disabled', true).addClass('loading');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ইমেইল পাঠানো হচ্ছে...',
                    text: 'অনুগ্রহ করে অপেক্ষা করুন।',
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
                    $btn.data('email', emailToSend);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'ইমেইল সফলভাবে পাঠানো হয়েছে!',
                            text: res.message || 'গ্রাহকের ইমেইলে ইনভয়েস পৌঁছে গেছে।',
                            confirmButtonText: 'ঠিক আছে'
                        });
                    }
                },
                error: function (xhr) {
                    $btn.prop('disabled', false).removeClass('loading');
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'ইমেইল পাঠানো যায়নি। অনুগ্রহ করে পুনরায় চেষ্টা করুন।';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'ব্যর্থ হয়েছে',
                            text: msg,
                            confirmButtonText: 'ঠিক আছে'
                        });
                    }
                }
            });
        }

        if (customerEmail && customerEmail.trim() !== '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ইনভয়েস ইমেইল পাঠান',
                    text: 'গ্রাহকের সংরক্ষিত ইমেইল (' + customerEmail + ') ঠিকানায় ইনভয়েস পাঠানো হবে। আপনি কি পাঠাতে চান?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'হ্যাঁ, পাঠান',
                    cancelButtonText: 'বাতিল'
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
                    title: 'ইমেইল ঠিকানা লিখুন',
                    text: 'গ্রাহকের কোনো ইমেইল ঠিকানা সংরক্ষিত নেই। অনুগ্রহ করে প্রাপকের ইমেইল লিখুন:',
                    input: 'email',
                    inputPlaceholder: 'customer@example.com',
                    showCancelButton: true,
                    confirmButtonText: 'ইমেইল পাঠান',
                    cancelButtonText: 'বাতিল',
                    inputValidator: function (value) {
                        if (!value) {
                            return 'একটি সঠিক ইমেইল ঠিকানা প্রদান করুন!';
                        }
                    }
                }).then(function (result) {
                    if (result.isConfirmed && result.value) {
                        doSend(result.value);
                    }
                });
            } else {
                var manualEmail = prompt('অনুগ্রহ করে গ্রাহকের ইমেইল ঠিকানা লিখুন:');
                if (manualEmail) {
                    doSend(manualEmail);
                }
            }
        }
    });
</script>
