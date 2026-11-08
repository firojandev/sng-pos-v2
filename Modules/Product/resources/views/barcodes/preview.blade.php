<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Barcode labels</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --label-paper: white; --label-ink: black; }
        body { margin:0; font-family:'Noto Sans Bengali','SolaimanLipi',sans-serif; background:var(--paper); color:var(--ink-900); }
        .preview-toolbar { position:sticky; top:0; z-index:20; background:var(--card); border-bottom:1px solid var(--border); box-shadow:var(--shadow-sm); }
        .preview-toolbar-inner { max-width:1240px; margin:0 auto; padding:20px 28px; display:flex; align-items:center; justify-content:space-between; gap:20px; }
        .preview-heading { display:flex; align-items:center; gap:12px; }
        .preview-heading-icon { display:grid; place-items:center; width:42px; height:42px; border-radius:12px; background:var(--blue-100); color:var(--blue-ink); flex-shrink:0; }
        .preview-heading-icon svg { width:22px; height:22px; }
        .preview-heading h1 { font-family:'Noto Sans Bengali','SolaimanLipi',sans-serif; font-size:18px; font-weight:700; margin:0 0 4px; color:var(--ink-900); }
        .preview-heading p { font-size:12px; color:var(--ink-400); margin:0; }
        .preview-actions { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .preview-summary { max-width:1240px; padding:20px 28px 0; margin:0 auto; }
        .preview-meta { display:flex; align-items:center; flex-wrap:wrap; gap:8px; }
        .preview-chip { display:inline-flex; align-items:center; gap:6px; padding:7px 11px; border:1px solid var(--border); border-radius:8px; background:var(--card); color:var(--ink-600); font-size:11px; }
        .preview-chip svg { width:14px; height:14px; color:var(--ink-400); }
        .preview-chip strong { color:var(--ink-900); font-weight:600; }
        .preview-status { display:flex; align-items:center; gap:8px; font-size:12px; color:var(--ink-600); padding:14px 0; }
        .preview-status-dot { width:7px; height:7px; border-radius:50%; background:var(--ink-400); flex-shrink:0; }
        .preview-status.is-ready .preview-status-dot { background:var(--green-ink); }
        .preview-status.is-error { color:var(--red-ink); }
        .preview-status.is-error .preview-status-dot { background:var(--red-ink); }
        .preview-print-tip { display:flex; gap:10px; padding:12px 14px; background:var(--blue-100); color:var(--blue-ink); border:1px solid var(--blue-ic-bg); border-radius:10px; font-size:11px; line-height:1.7; }
        .preview-print-tip svg { width:17px; height:17px; flex-shrink:0; margin-top:2px; }
        .preview-canvas { padding:26px 20px 40px; overflow-x:auto; }
        .preview-sheet { width:max-content; margin:0 auto 28px; }
        .preview-sheet:last-child { margin-bottom:0; }
        .preview-page-caption { display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; color:var(--ink-400); font-size:11px; }
        @media (max-width:760px) { .preview-toolbar-inner { align-items:flex-start; flex-direction:column; padding:16px; gap:14px; } .preview-summary { padding:16px 16px 0; } .preview-canvas { padding:20px 16px; } .preview-heading h1 { font-size:16px; } .preview-toolbar { position:static; } }
        .label-page { box-sizing:border-box; padding:{{ $sheetMargin }}mm {{ $horizontalMargin }}mm; width:{{ $settings['paper'] === 'a4' ? 210 : $settings['width'] }}mm; height:{{ $settings['paper'] === 'a4' ? 297 : $settings['height'] }}mm; display:grid; grid-template-columns:repeat({{ $columns }}, {{ $settings['width'] }}mm); grid-auto-rows:{{ $settings['height'] }}mm; align-content:start; background:var(--label-paper); color:var(--label-ink); margin:0; box-shadow:var(--shadow-card); }
        .barcode-label { box-sizing:border-box; width:{{ $settings['width'] }}mm; height:{{ $settings['height'] }}mm; padding:1mm; display:flex; flex-direction:column; justify-content:center; border:0.15mm dashed var(--label-ink); text-align:center; background:var(--label-paper); color:var(--label-ink); font-size:8pt; line-height:1.25; overflow:hidden; }
        .barcode-label svg { display:block; margin:0 auto; flex-shrink:0; }
        .label-line { width:100%; overflow-wrap:anywhere; flex-shrink:0; }
        @page { size:{{ $settings['paper'] === 'a4' ? 'A4' : $settings['width'].'mm '.$settings['height'].'mm' }}; margin:0; }
        @media print {
            html, body { margin:0 !important; padding:0 !important; }
            .screen-only { display:none !important; }
            body.labels-ready .preview-canvas,
            body.labels-ready .preview-canvas * { visibility:visible !important; }
            .preview-canvas { padding:0; overflow:visible; }
            .preview-sheet { margin:0; break-after:page; break-inside:avoid; }
            .preview-sheet:last-child { break-after:auto; }
            .label-page { margin:0; box-shadow:none; }
            body:not(.labels-ready) .preview-canvas { display:none; }
            .barcode-label { break-inside:avoid; }
        }
    </style>
</head>
<body>
    <header class="preview-toolbar screen-only">
        <div class="preview-toolbar-inner">
            <div class="preview-heading">
                <div class="preview-heading-icon"><x-core::icon name="scan-line" /></div>
                <div><h1><span class="bn">বারকোড লেবেল প্রিভিউ</span><span class="en">Barcode label preview</span></h1><p><span class="bn">লেবেল দেখে প্রিন্ট করুন অথবা PDF ডাউনলোড করুন</span><span class="en">Review your labels, then print or save a PDF</span></p></div>
            </div>
            <div class="preview-actions">
                <x-core::button size="sm" :href="route('barcodes.index')" variant="secondary" icon="arrow-left"><span class="bn">বারকোড ব্যবস্থাপনা</span><span class="en">Barcode Management</span></x-core::button>
                <x-core::button size="sm" id="print-labels" color="primary" icon="printer" disabled><span class="bn">প্রিন্ট করুন</span><span class="en">Print labels</span></x-core::button>
                <x-core::button size="sm" id="download-labels" variant="secondary" icon="download" disabled><span class="bn">PDF ডাউনলোড</span><span class="en">Download PDF</span></x-core::button>
            </div>
        </div>
    </header>
    <div class="preview-summary screen-only">
        <div class="preview-meta">
            <span class="preview-chip"><x-core::icon name="tags" /><strong>{{ $pages->sum(fn ($page) => $page->count()) }}</strong><span class="bn">লেবেল</span><span class="en">labels</span></span>
            <span class="preview-chip"><x-core::icon name="files" /><strong>{{ $pages->count() }}</strong><span class="bn">পৃষ্ঠা</span><span class="en">pages</span></span>
            <span class="preview-chip"><x-core::icon name="printer" /><strong>{{ $settings['paper'] === 'a4' ? 'A4' : 'Thermal' }}</strong></span>
            <span class="preview-chip"><x-core::icon name="ruler" /><strong>{{ $settings['width'] }} × {{ $settings['height'] }} mm</strong></span>
            <span class="preview-chip"><x-core::icon name="barcode" /><strong>{{ $settings['format'] === 'EAN13' ? 'EAN-13' : 'Code 128' }}</strong></span>
        </div>
        <div class="preview-status" role="status" aria-live="polite"><span class="preview-status-dot"></span><span id="label-message"><span class="bn">লেবেল প্রস্তুত হচ্ছে…</span><span class="en">Preparing your labels…</span></span></div>
        <div class="preview-print-tip"><x-core::icon name="info" /><div><strong><span class="bn">সঠিক মাপে প্রিন্ট করুন</span><span class="en">Print at actual size</span></strong><br><span class="bn">স্কেল ১০০% রাখুন, হেডার ও ফুটার বন্ধ করুন এবং মার্জিন শূন্য দিন। প্রিন্টারে উপযুক্ত কাগজের মাপ নির্বাচন করুন।</span><span class="en">Choose 100% scale, turn off headers and footers, and set margins to zero. Match your printer paper size to this preview.</span></div></div>
    </div>
    <main class="preview-canvas">
    @foreach ($pages as $labels)
        <section class="preview-sheet" aria-label="Page {{ $loop->iteration }}">
        <div class="preview-page-caption screen-only"><span><span class="bn">পৃষ্ঠা</span><span class="en">Page</span> {{ $loop->iteration }} / {{ $pages->count() }}</span><span>{{ $labels->count() }} <span class="bn">লেবেল</span><span class="en">labels</span></span></div>
        <div class="label-page">
            @foreach ($labels as $product)
                <div class="barcode-label" data-product="{{ $product->id }}">
                    @if (!empty($settings['show_name']))<div class="label-line">{{ $product->name }}</div>@endif
                    @if (!empty($settings['show_sku']) && $product->sku)<div class="label-line">{{ $product->sku }}</div>@endif
                    @if (!empty($settings['show_price']))<div class="label-line">{{ number_format((float) $product->sale_price, 2) }} / {{ $product->baseUnit()?->name ?? 'base unit' }}</div>@endif
                    <svg class="barcode" data-number="{{ $product->barcode }}" aria-label="{{ $product->barcode }}"></svg>
                    @if (!empty($settings['show_number']))<div class="label-line">{{ $product->barcode }}</div>@endif
                </div>
            @endforeach
        </div>
        </section>
    @endforeach
    </main>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.12.1/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script type="module">
        $(async function () {
            const settings = @json($settings);
            const text = (bn, en) => $('body').hasClass('lang-en') ? en : bn;
            function setEnabled(selector, enabled) {
                $(selector).prop('disabled', !enabled).toggleClass('disabled', !enabled).attr('aria-disabled', String(!enabled));
            }
            function showStatus(message, state) {
                $('#label-message').text(message);
                $('.preview-status').removeClass('is-ready is-error').addClass(state ? 'is-' + state : '');
            }
            try {
                await document.fonts.ready;
                $('.barcode').each(function () {
                    const svg = $(this);
                    const label = svg.closest('.barcode-label');
                    const availableWidth = label.innerWidth() - parseFloat(label.css('padding-left')) - parseFloat(label.css('padding-right'));
                    const options = {
                        format:settings.format, width:1, height:Number(settings.barcode_height) * 96 / 25.4,
                        displayValue:false, margin:0, marginLeft:11, marginRight:11
                    };
                    JsBarcode(this, svg.attr('data-number'), options);
                    const naturalWidth = Number(svg.attr('width'));
                    const moduleWidth = Math.floor(Math.min(2, Math.max(1, (availableWidth - 1) / naturalWidth)) * 100) / 100;
                    if (moduleWidth > 1) {
                        JsBarcode(this, svg.attr('data-number'), {
                            ...options, width:moduleWidth,
                            marginLeft:11 * moduleWidth, marginRight:11 * moduleWidth
                        });
                    }
                });
                $('.barcode-label').each(function () {
                    const label = $(this);
                    const available = label.innerWidth() - parseFloat(label.css('padding-left')) - parseFloat(label.css('padding-right'));
                    const availableHeight = label.innerHeight() - parseFloat(label.css('padding-top')) - parseFloat(label.css('padding-bottom'));
                    let contentHeight = 0;
                    label.children().each(function () { contentHeight += $(this).outerHeight(true); });
                    if (label.find('svg').width() > available + 0.5 || contentHeight > availableHeight + 0.5) {
                        throw new Error('Content does not fit. Go back and increase label dimensions or hide information. Barcodes will not be compressed or clipped.');
                    }
                });
                $('body').addClass('labels-ready');
                showStatus(text('লেবেল প্রস্তুত — প্রিন্ট অথবা PDF ডাউনলোড করুন।', 'Your labels are ready to print or download.'), 'ready');
                setEnabled('#print-labels', true);
                const pdfAvailable = Boolean(window.htmlToImage && window.jspdf?.jsPDF);
                setEnabled('#download-labels', pdfAvailable);
                if (!pdfAvailable) { showStatus(text('প্রিন্ট প্রস্তুত। PDF টুল লোড হয়নি, পেজ রিফ্রেশ করুন।', 'Ready to print. PDF tools did not load; refresh to retry.'), 'error'); }
            } catch (error) {
                showStatus(error.message || 'Unable to load barcode renderer. Reload to try again.', 'error');
                return;
            }
            $(document).on('click', '#print-labels', function () { window.print(); });
            $(document).on('click', '#download-labels', async function () {
                const button = $(this);
                const originalLabel = button.find('.btn-text').html();
                setEnabled('#download-labels', false);
                button.attr('aria-busy', 'true').find('.btn-text').text(text('PDF তৈরি হচ্ছে…', 'Creating PDF…'));
                showStatus(text('PDF তৈরি হচ্ছে…', 'Creating your PDF…'));
                try {
                    const thermal = settings.paper === 'thermal';
                    const pageWidth = thermal ? Number(settings.width) : 210;
                    const pageHeight = thermal ? Number(settings.height) : 297;
                    const pdf = new window.jspdf.jsPDF({unit:'mm', format:[pageWidth, pageHeight], orientation:pageWidth > pageHeight ? 'landscape' : 'portrait', compress:true});
                    const images = {};
                    const pages = $('.label-page').toArray();
                    for (let pageIndex = 0; pageIndex < pages.length; pageIndex++) {
                        if (pageIndex) { pdf.addPage([pageWidth, pageHeight], pageWidth > pageHeight ? 'landscape' : 'portrait'); }
                        const labels = $(pages[pageIndex]).find('.barcode-label').toArray();
                        for (let i = 0; i < labels.length; i++) {
                            const id = $(labels[i]).attr('data-product');
                            if (!images[id]) { images[id] = await htmlToImage.toPng(labels[i], {pixelRatio:4}); }
                            pdf.addImage(images[id], 'PNG', {{ $horizontalMargin }} + (i % {{ $columns }}) * Number(settings.width), {{ $sheetMargin }} + Math.floor(i / {{ $columns }}) * Number(settings.height), Number(settings.width), Number(settings.height), id, 'FAST');
                        }
                    }
                    pdf.save('product-barcode-labels.pdf');
                    showStatus(text('PDF ডাউনলোড হয়েছে।', 'PDF downloaded. Your labels are still ready to print.'), 'ready');
                } catch (error) {
                    showStatus('PDF failed: ' + error.message + '. You can still print the preview.', 'error');
                } finally {
                    setEnabled('#download-labels', true);
                    button.removeAttr('aria-busy').find('.btn-text').html(originalLabel);
                }
            });
        });
    </script>
</body>
</html>
