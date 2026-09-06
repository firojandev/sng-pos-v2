<div class="modal-backdrop" id="saleInvoiceModal" style="z-index:1050;">
    <div class="modal-box" style="width:760px; max-width:96vw; max-height:94vh; padding:0; border-radius:12px; background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">
        
        {{-- Modal Header: "Successful" with Close Button --}}
        <div class="modal-head" style="padding:14px 20px; border-bottom:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; background:var(--card, #ffffff);">
            <h3 style="font-size:18px; font-weight:700; color:var(--ink-900, #0f172a); margin:0; font-family:'Noto Sans Bengali', sans-serif;">
                Successful
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

        {{-- Modal Footer: Full-Width Dark Print Button --}}
        <div class="modal-foot" style="padding:12px 20px; background:var(--card, #ffffff); border-top:1px solid var(--border, #e2e8f0);">
            <button
                type="button"
                class="btn-print-sale-invoice"
                style="width:100%; background:#1c1c1c; color:#ffffff; border:none; padding:11px 16px; border-radius:6px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; font-family:'Noto Sans Bengali', sans-serif;"
                onclick="printSaleInvoice('{{ route('sales.print-invoice', $sale) }}')"
            >
                <x-core::icon name="printer" size="16" />
                <span>প্রিন্ট করুন</span>
            </button>
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
</script>
