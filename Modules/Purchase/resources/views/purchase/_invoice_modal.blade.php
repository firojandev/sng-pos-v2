@php
    $shop = auth()->user()?->shop ?? $purchase->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop ? $shop->getEffectivePrinterSetting() : \Modules\Shop\Models\PrinterSetting::getDefaultForShop(1);
@endphp
<div class="modal-backdrop" id="purchaseInvoiceModal" style="z-index:1050;">
    <div class="modal-box" style="width:{{ $printerSetting->isThermal() ? '460px' : ($printerSetting->isA5() ? '580px' : '760px') }}; max-width:96vw; max-height:94vh; padding:0; border-radius:12px; background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal Header: "Purchase Invoice" with Close Button --}}
        <div class="modal-head" style="padding:14px 20px; border-bottom:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; background:var(--card, #ffffff);">
            <h3 style="font-size:18px; font-weight:700; color:var(--ink-900, #0f172a); margin:0; font-family:'Noto Sans Bengali', sans-serif;">
                <span class="bn">ক্রয় ইনভয়েস</span>
                <span class="en" style="display:none;">Purchase Invoice</span>
            </h3>
            <x-core::button
                type="button"
                variant="ghost"
                size="sm"
                icon="x"
                icon-only
                class="modal-close-btn"
                onclick="closeModal('purchaseInvoiceModal')"
            />
        </div>

        {{-- Modal Body: Canvas containing the Invoice Sheet --}}
        <div class="modal-body" style="padding:20px; background:var(--paper, #f4f5f7); overflow-y:auto; overflow-x:hidden; flex:1;">
            @include('purchase::purchase._invoice_sheet')
        </div>

        {{-- Modal Footer: Download and Print Buttons --}}
        <div class="modal-foot" style="padding:10px 18px; background:var(--card, #ffffff); border-top:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
            {{-- Download Button --}}
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="download"
                class="btn-download-purchase-invoice"
                onclick="downloadPurchaseInvoice('{{ $purchase->invoice_no }}')"
                title="ডাউনলোড / Download PDF"
                style="white-space:nowrap !important; flex-shrink:0;"
            >
                <span class="bn">ডাউনলোড</span>
                <span class="en" style="display:none;">Download</span>
            </x-core::button>

            {{-- Print Button --}}
            <x-core::button
                type="button"
                color="primary"
                size="sm"
                icon="printer"
                class="btn-print-purchase-invoice"
                onclick="printPurchaseInvoice('{{ route('purchase.print-invoice', $purchase) }}')"
                title="প্রিন্ট করুন / Print"
                style="white-space:nowrap !important; flex-shrink:0;"
            >
                <span class="bn">প্রিন্ট করুন</span>
                <span class="en" style="display:none;">Print</span>
            </x-core::button>
        </div>
    </div>
</div>

<script>
    if (typeof window.downloadPurchaseInvoice !== 'function') {
        window.downloadPurchaseInvoice = function(invoiceNo) {
            var $btn = $('.btn-download-purchase-invoice');
            $btn.prop('disabled', true).css('opacity', '0.7');

            var modal = document.getElementById('purchaseInvoiceModal');
            var element = modal ? (modal.querySelector('#purchaseInvoiceSheet') || modal.querySelector('.purchase-invoice-sheet') || modal.querySelector('.purchase-thermal-receipt-sheet') || modal.querySelector('.modal-body')) : document.getElementById('purchaseInvoiceSheet');
            if (!element) {
                $btn.prop('disabled', false).css('opacity', '1');
                return;
            }

            var loadScript = function(src, callback) {
                var existing = document.querySelector('script[src="' + src + '"]');
                if (existing) {
                    if (existing.getAttribute('data-loaded') === 'true') {
                        callback();
                    } else {
                        existing.addEventListener('load', callback);
                    }
                    return;
                }
                var s = document.createElement('script');
                s.src = src;
                s.onload = function() {
                    s.setAttribute('data-loaded', 'true');
                    callback();
                };
                s.onerror = function() {
                    console.error('Failed to load: ' + src);
                };
                document.head.appendChild(s);
            };

            var ensurePdfLibraries = function(cb) {
                if (window.htmlToImage && (window.jspdf || window.jsPDF)) {
                    cb();
                    return;
                }
                loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', function() {
                    loadScript('https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js', function() {
                        cb();
                    });
                });
            };

            ensurePdfLibraries(function() {
                var isThermal = {{ $printerSetting->isThermal() ? 'true' : 'false' }};
                var isA5 = {{ $printerSetting->isA5() ? 'true' : 'false' }};
                var paperWidth = {{ ($printerSetting->paper_width ?: 80) }};
                var filename = 'Purchase-Invoice-' + (invoiceNo || '{{ $purchase->invoice_no }}') + '.pdf';

                var readyPromise = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();

                readyPromise.then(function() {
                    return window.htmlToImage.toPng(element, {
                        quality: 0.98,
                        pixelRatio: 2.5,
                        backgroundColor: '#ffffff',
                        cacheBust: true,
                        style: {
                            background: '#ffffff',
                            color: '#0f172a'
                        }
                    });
                }).then(function(dataUrl) {
                    var img = new Image();
                    img.onload = function() {
                        var imgWidth = img.naturalWidth || img.width;
                        var imgHeight = img.naturalHeight || img.height;
                        var jsPDF = window.jspdf ? window.jspdf.jsPDF : window.jsPDF;

                        var pdf;
                        if (isThermal) {
                            var paperWidthMm = paperWidth || 80;
                            var pdfHeightMm = (imgHeight * paperWidthMm) / imgWidth;
                            pdf = new jsPDF({
                                unit: 'mm',
                                format: [paperWidthMm, pdfHeightMm + 2],
                                orientation: 'portrait'
                            });
                            pdf.addImage(img, 'PNG', 0, 1, paperWidthMm, pdfHeightMm);
                        } else {
                            var format = isA5 ? 'a5' : 'a4';
                            pdf = new jsPDF({
                                unit: 'mm',
                                format: format,
                                orientation: 'portrait'
                            });
                            var pageWidth = pdf.internal.pageSize.getWidth();
                            var pageHeight = pdf.internal.pageSize.getHeight();
                            var margin = 6;
                            var printWidth = pageWidth - (margin * 2);
                            var printHeight = (imgHeight * printWidth) / imgWidth;

                            if (printHeight <= pageHeight - (margin * 2)) {
                                pdf.addImage(img, 'PNG', margin, margin, printWidth, printHeight);
                            } else {
                                var heightLeft = printHeight;
                                var position = margin;
                                pdf.addImage(img, 'PNG', margin, position, printWidth, printHeight);
                                heightLeft -= (pageHeight - margin * 2);
                                while (heightLeft > 0) {
                                    position = position - pageHeight + (margin * 2);
                                    pdf.addPage();
                                    pdf.addImage(img, 'PNG', margin, position, printWidth, printHeight);
                                    heightLeft -= (pageHeight - margin * 2);
                                }
                            }
                        }
                        pdf.save(filename);
                        $btn.prop('disabled', false).css('opacity', '1');
                    };
                    img.onerror = function() {
                        $btn.prop('disabled', false).css('opacity', '1');
                    };
                    img.src = dataUrl;
                }).catch(function(err) {
                    console.error('PDF export error:', err);
                    $btn.prop('disabled', false).css('opacity', '1');
                    window.open('{{ route('purchase.print-invoice', $purchase) }}?autoprint=1', '_blank');
                });
            });
        };
    }

    if (typeof window.printPurchaseInvoice !== 'function') {
        window.printPurchaseInvoice = function(url) {
            var printUrl = url + (url.indexOf('?') > -1 ? '&' : '?') + 'autoprint=1';
            var printWindow = window.open(printUrl, '_blank');
            if (printWindow) {
                printWindow.focus();
                return;
            }

            let iframe = document.getElementById('purchase-print-iframe');
            if (iframe) {
                iframe.remove();
            }
            iframe = document.createElement('iframe');
            iframe.id = 'purchase-print-iframe';
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
    }

    $(document).off('click.purchaseInvoiceModal').on('click.purchaseInvoiceModal', '#purchaseInvoiceModal', function (e) {
        if ($(e.target).is('#purchaseInvoiceModal')) {
            closeModal('purchaseInvoiceModal');
        }
    });
</script>
