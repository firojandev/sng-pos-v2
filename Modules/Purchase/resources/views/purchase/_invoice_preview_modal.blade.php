@php
    $shop = auth()->user()?->shop ?? \Modules\Shop\Models\Shop::first();
    $printerSetting = $shop ? $shop->getEffectivePrinterSetting() : \Modules\Shop\Models\PrinterSetting::getDefaultForShop(1);
    $isThermal = $printerSetting->isThermal();
    $isCompact58 = ($printerSetting->paper_width ?: 80) <= 58;
@endphp

<div class="modal-backdrop" id="purchaseInvoicePreviewModal" style="z-index:1060;">
    <div class="modal-box" style="width:{{ $isThermal ? '500px' : ($printerSetting->isA5() ? '600px' : '760px') }}; max-width:96vw; max-height:94vh; padding:0; border-radius:12px; background:var(--card, #ffffff); border:1px solid var(--border, #e2e8f0); box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); display:flex; flex-direction:column; overflow:hidden;">

        {{-- Modal Header --}}
        <div class="modal-head" style="padding:12px 20px; border-bottom:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; background:var(--card, #ffffff);">
            <div style="display:flex; align-items:center; gap:8px;">
                <div style="width:30px; height:30px; border-radius:6px; background:var(--teal-100, #ccfbf1); color:var(--teal-800, #0f766e); display:flex; align-items:center; justify-content:center;">
                    <x-core::icon name="file-text" size="17" />
                </div>
                <div>
                    <h3 style="font-size:16px; font-weight:700; color:var(--ink-900, #0f172a); margin:0; font-family:'Noto Sans Bengali', sans-serif;">
                        <span class="bn">ক্রয় ইনভয়েস প্রিভিউ</span>
                        <span class="en" style="display:none;">Purchase Invoice Preview</span>
                    </h3>
                </div>
                <span style="font-size:11px; padding:2px 8px; border-radius:12px; background:var(--paper-line, #f1f5f9); color:var(--ink-600, #475569); font-weight:600; border:1px solid var(--border, #e2e8f0);">
                    <span class="bn">সংরক্ষণের পূর্বে যাচাই</span>
                    <span class="en" style="display:none;">Preview before save</span>
                </span>
            </div>
            <x-core::button
                type="button"
                variant="ghost"
                size="sm"
                icon="x"
                icon-only
                class="modal-close-btn"
                onclick="closeModal('purchaseInvoicePreviewModal')"
                style="width:30px; height:30px; padding:0; display:flex; align-items:center; justify-content:center;"
            />
        </div>

        {{-- Modal Body: Canvas containing Preview Sheet --}}
        <div class="modal-body" style="padding:18px; background:#f4f5f7; overflow-y:auto; overflow-x:hidden; flex:1;">
            @if ($isThermal)
                {{-- Purchase Thermal Preview Layout --}}
                <div class="purchase-thermal-receipt-sheet" id="purchaseThermalPreviewSheet" style="background:#ffffff; width:100%; max-width:{{ $printerSetting->getCssPaperWidth() }}; margin:0 auto; padding:10px 8px; color:#000000; font-family:'Noto Sans Bengali', monospace, sans-serif; font-size:{{ $isCompact58 ? '8.5px' : '10px' }}; line-height:1.35; box-sizing:border-box; box-shadow:0 2px 8px rgba(0,0,0,0.08); border:2px solid #cbd5e1; border-radius:6px;">
                    <div style="text-align:center; margin-bottom:4px;">
                        @if ($printerSetting->show_header_logo && !empty($shop?->logo))
                            <div style="margin-bottom:3px;">
                                <img src="{{ $shop->logo_url ?? asset($shop->logo) }}" alt="Logo" style="max-height:36px; max-width:120px; object-fit:contain;">
                            </div>
                        @endif
                        <div style="font-weight:800; font-size:{{ $isCompact58 ? '12px' : '14px' }}; color:#000000; line-height:1.2;">
                            {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
                        </div>
                        @if ($printerSetting->show_shop_info)
                            @if (!empty($shop?->address))
                                <div style="font-size:{{ $isCompact58 ? '8px' : '9px' }}; color:#333333; margin-top:1px;">{{ $shop->address }}</div>
                            @endif
                            @if (!empty($shop?->phone))
                                <div style="font-size:{{ $isCompact58 ? '8px' : '9px' }}; color:#333333;">মোবাইল: {{ $shop->phone }}</div>
                            @endif
                        @endif
                    </div>

                    <div style="text-align:center; margin:4px 0; border-top:1px dashed #000000; border-bottom:1px dashed #000000; padding:2px 0; font-weight:700; font-size:{{ $isCompact58 ? '9px' : '10.5px' }};">
                        ক্রয় চালান (প্রিভিউ)
                    </div>

                    <div style="font-size:{{ $isCompact58 ? '8px' : '9px' }}; line-height:1.4; margin-bottom:4px;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>ইনভয়েস: <b id="purchase-thermal-preview-invoice-no">—</b></span>
                            <span>তারিখ: <span id="purchase-thermal-preview-date">—</span></span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>সরবরাহকারী: <b id="purchase-thermal-preview-supplier-name">—</b></span>
                            <span id="purchase-thermal-preview-supplier-phone-wrap" style="display:none;">মোবাইল: <span id="purchase-thermal-preview-supplier-phone">—</span></span>
                        </div>
                        <div id="purchase-thermal-preview-supplier-addr-wrap" style="display:none;">
                            ঠিকানা: <span id="purchase-thermal-preview-supplier-address">—</span>
                        </div>
                        <div>গুদাম: <span id="purchase-thermal-preview-warehouse">—</span></div>
                    </div>

                    <table style="width:100% !important; border-collapse:collapse !important; margin:5px 0 !important; font-size:{{ $isCompact58 ? '8px' : '9.5px' }} !important;">
                        <thead>
                            <tr style="border-top:1px dashed #000; border-bottom:1px dashed #000; font-weight:700;">
                                <th style="text-align:left; padding:3px 2px; width:45%;">পণ্য</th>
                                <th style="text-align:center; padding:3px 2px; width:18%;">পরিমাণ</th>
                                <th style="text-align:right; padding:3px 2px; width:17%;">দর</th>
                                <th style="text-align:right; padding:3px 2px; width:20%;">মোট</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-thermal-preview-items-body"></tbody>
                    </table>

                    <div style="border-top:1px dashed #000; margin:4px 0;"></div>

                    <div style="font-size:{{ $isCompact58 ? '8px' : '9.5px' }}; line-height:1.45;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>সাব টোটাল:</span>
                            <span id="purchase-thermal-preview-subtotal">৳০.০০</span>
                        </div>
                        <div id="purchase-thermal-preview-discount-row" style="display:none; justify-content:space-between;">
                            <span>(-) ছাড়:</span>
                            <span id="purchase-thermal-preview-discount">৳০.০০</span>
                        </div>
                        <div id="purchase-thermal-preview-tax-row" style="display:none; justify-content:space-between;">
                            <span>(+) ভ্যাট:</span>
                            <span id="purchase-thermal-preview-tax">৳০.০০</span>
                        </div>
                        <div id="purchase-thermal-preview-transport-row" style="display:none; justify-content:space-between;">
                            <span>(+) পরিবহন:</span>
                            <span id="purchase-thermal-preview-transport">৳০.০০</span>
                        </div>
                        <div id="purchase-thermal-preview-adjustment-row" style="display:none; justify-content:space-between;">
                            <span id="purchase-thermal-preview-adjustment-lbl">সমন্বয়:</span>
                            <span id="purchase-thermal-preview-adjustment">৳০.০০</span>
                        </div>
                        <div style="border-top:1px dashed #000; margin:3px 0;"></div>
                        <div style="display:flex; justify-content:space-between; font-weight:700; font-size:{{ $isCompact58 ? '9.5px' : '11px' }};">
                            <span>মোট প্রদেয়:</span>
                            <span id="purchase-thermal-preview-grand-total">৳০.০০</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span>পরিশোধ:</span>
                            <span id="purchase-thermal-preview-paid">৳০.০০</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-weight:600;">
                            <span>বর্তমান বাকি:</span>
                            <span id="purchase-thermal-preview-due">৳০.০০</span>
                        </div>

                        <div id="purchase-thermal-preview-due-box" style="display:none; margin-top:4px; border:1px dashed #000; padding:3px 4px;">
                            <div style="display:flex; justify-content:space-between;">
                                <span>পূর্বের বাকি:</span>
                                <span id="purchase-thermal-preview-prev-due">৳০.০০</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; font-weight:700;">
                                <span>মোট বকেয়া:</span>
                                <span id="purchase-thermal-preview-total-due">৳০.০০</span>
                            </div>
                        </div>
                    </div>

                    @if ($printerSetting->show_footer_note && !empty($shop?->invoice_footer))
                        <div style="margin-top:8px; text-align:center; font-size:{{ $isCompact58 ? '7.5px' : '8.5px' }}; color:#333; border-top:1px dashed #000; padding-top:4px;">
                            {{ $shop->invoice_footer }}
                        </div>
                    @endif
                </div>
            @else
                {{-- Standard Purchase Preview Layout --}}
                <div class="purchase-invoice-sheet" id="purchaseStandardPreviewSheet" style="background:#ffffff; width:100%; max-width:{{ $printerSetting->isA5() ? '520px' : '700px' }}; margin:0 auto; padding:22px 26px; border:1px solid #e2e8f0; border-radius:4px; box-shadow:0 1px 4px rgba(0,0,0,0.06); color:#0f172a; font-family:'Noto Sans Bengali', sans-serif; box-sizing:border-box;">
                    {{-- Top Header with Shop Info --}}
                    <div style="display:flex; align-items:flex-start; gap:12px; margin-bottom:10px;">
                        @if ($printerSetting->show_header_logo)
                            <div style="flex-shrink:0; width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                                @if(!empty($shop?->logo))
                                    <img src="{{ $shop->logo_url ?? asset($shop->logo) }}" alt="Shop Logo" style="max-width:48px; max-height:48px; object-fit:contain;">
                                @else
                                    <svg width="46" height="46" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="7" y="19" width="34" height="23" rx="2" fill="#38bdf8" stroke="#0f172a" stroke-width="2"/>
                                        <rect x="19" y="27" width="10" height="15" fill="#0f172a"/>
                                        <rect x="11" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                                        <rect x="32" y="25" width="5" height="8" rx="1" fill="#f8fafc" stroke="#0f172a" stroke-width="1.5"/>
                                        <path d="M4 18L9 8H39L44 18H4Z" fill="#ea580c" stroke="#0f172a" stroke-width="2" stroke-linejoin="round"/>
                                        <path d="M4 18C4 20.5 6 22 8.5 22C11 22 13 20.5 13 18C13 20.5 15 22 17.5 22C20 22 22 20.5 22 18C22 20.5 24 22 26.5 22C29 22 31 20.5 31 18C31 20.5 33 22 35.5 22C38 22 40 20.5 40 18C40 20.5 41.8 22 44 22" stroke="#0f172a" stroke-width="2" fill="#f97316"/>
                                    </svg>
                                @endif
                            </div>
                        @endif

                        <div>
                            <div style="font-size:18px; font-weight:800; color:#0f172a; line-height:1.2;">
                                {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
                            </div>
                            @if ($printerSetting->show_shop_info)
                                @if(!empty($shop?->address))
                                    <div style="font-size:12px; color:#475569; margin-top:2px;">{{ $shop->address }}</div>
                                @endif
                                @if(!empty($shop?->phone))
                                    <div style="font-size:12px; color:#475569; margin-top:1px;">মোবাইল : {{ $shop->phone }}</div>
                                @endif
                            @endif
                        </div>
                    </div>

                    {{-- Title with Horizontal Accent Lines --}}
                    <div style="display:flex; align-items:center; justify-content:center; gap:16px; margin:12px 0 14px 0;">
                        <div style="flex:1; height:1px; background:#94a3b8;"></div>
                        <div style="font-size:22px; font-weight:800; color:#0f172a; letter-spacing:1px; padding:0 8px;">
                            ক্রয় ইনভয়েস
                        </div>
                        <div style="flex:1; height:1px; background:#94a3b8;"></div>
                    </div>

                    {{-- Metadata: Supplier & Purchase Information --}}
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:12px; line-height:1.6; margin-bottom:14px;">
                        <div style="width:50%;">
                            <div><b>সরবরাহকারী :</b> <span id="purchase-preview-supplier-name">—</span></div>
                            <div><b>মোবাইল নং :</b> <span id="purchase-preview-supplier-phone">—</span></div>
                            <div><b>ঠিকানা :</b> <span id="purchase-preview-supplier-address">—</span></div>
                        </div>
                        <div style="width:50%; text-align:right;">
                            <div><b>ইনভয়েস নং :</b> <span id="purchase-preview-invoice-no" style="font-weight:700;">#স্বয়ংক্রিয়</span></div>
                            <div><b>তারিখ :</b> <span id="purchase-preview-date">—</span></div>
                            <div><b>গুদাম :</b> <span id="purchase-preview-warehouse">—</span></div>
                        </div>
                    </div>

                    {{-- Items Table --}}
                    <table class="invoice-items-table" style="width:100% !important; border-collapse:separate !important; border-spacing:0 !important; border:1px solid #94a3b8 !important; margin:14px 0 !important; font-size:11.5px !important; table-layout:fixed !important;">
                        <colgroup>
                            <col style="width:6%;">
                            <col style="width:38%;">
                            <col style="width:11%;">
                            <col style="width:11%;">
                            <col style="width:17%;">
                            <col style="width:17%;">
                        </colgroup>
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; padding:6px 2px; text-align:center; font-weight:700;">#</th>
                                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; padding:6px 8px; text-align:center; font-weight:700;">পণ্যের নাম</th>
                                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; padding:6px 2px; text-align:center; font-weight:700;">পরিমান</th>
                                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; padding:6px 2px; text-align:center; font-weight:700;">একক</th>
                                <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; padding:6px 4px; text-align:center; font-weight:700;">ক্রয় মূল্য</th>
                                <th style="border-bottom:1px solid #94a3b8 !important; padding:6px 4px; text-align:center; font-weight:700;">মোট</th>
                            </tr>
                        </thead>
                        <tbody id="purchase-preview-items-body"></tbody>
                        <tfoot>
                            <tr style="font-weight:700; background:#f8fafc;">
                                <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-top:1px solid #94a3b8 !important; padding:6px 8px; text-align:center;">
                                    মোট পরিমান
                                </td>
                                <td style="border-right:1px solid #94a3b8 !important; border-top:1px solid #94a3b8 !important; padding:6px 2px; text-align:center; white-space:nowrap;" id="purchase-preview-total-qty">
                                    ০.০০
                                </td>
                                <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-top:1px solid #94a3b8 !important; padding:6px 4px; text-align:center;">
                                    মোট
                                </td>
                                <td style="border-top:1px solid #94a3b8 !important; padding:6px 4px; text-align:right; white-space:nowrap;" id="purchase-preview-table-subtotal">
                                    ০.০০
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- Lower Summary Section (2 Columns) --}}
                    {{-- Lower Summary Section (2 Columns) --}}
                    <table style="width:100%; border-collapse:collapse; margin-top:12px; font-size:12px; color:#0f172a;">
                        <tr>
                            {{-- Left Column: Dues, Words & Signatures --}}
                            <td style="width:50%; vertical-align:top; padding-right:16px;">
                                <div id="purchase-preview-due-box" style="display:none; margin-bottom:12px;">
                                    <table style="width:100%; max-width:230px; border-collapse:collapse; font-size:12px; line-height:1.5;">
                                        <tr>
                                            <td style="padding:2px 0; text-align:left;"><b>পূর্বের বাকি:</b></td>
                                            <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-prev-due">৳০.০০</td>
                                        </tr>
                                        <tr>
                                            <td style="padding:2px 0; text-align:left;"><b>বর্তমান বাকি:</b></td>
                                            <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-current-due">৳০.০০</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="border-top:1px solid #94a3b8; padding:0; height:1px;"></td>
                                        </tr>
                                        <tr>
                                            <td style="padding:3px 0; text-align:left; font-weight:700;"><b>টোটাল বাকি:</b></td>
                                            <td style="padding:3px 0; text-align:right; font-weight:700; white-space:nowrap;" id="purchase-preview-total-supplier-due">৳০.০০</td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="margin-top:6px;">
                                    <div style="font-weight:700; margin-bottom:2px; font-size:12px;">অ্যামাউন্ট (কথায়):</div>
                                    <div style="color:#1e293b; font-size:11.5px; line-height:1.4;" id="purchase-preview-amount-words">
                                        শূন্য টাকা
                                    </div>
                                </div>

                                <div style="margin-top:40px;">
                                    <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                                        গ্রহীতার স্বাক্ষর
                                    </div>
                                    <div style="font-size:9.5px; color:#64748b; margin-top:3px;">
                                        প্রিন্ট করার সময়: <span id="purchase-preview-print-time">—</span>
                                    </div>
                                </div>
                            </td>

                            {{-- Right Column: Financial Totals --}}
                            <td style="width:50%; vertical-align:top; padding-left:16px;">
                                <table style="width:100%; border-collapse:collapse; font-size:12px; line-height:1.5;">
                                    <tr>
                                        <td style="padding:2px 0; text-align:left; color:#334155;">সাব টোটাল</td>
                                        <td style="padding:2px 0; text-align:right; font-weight:600; white-space:nowrap;" id="purchase-preview-subtotal">৳০.০০</td>
                                    </tr>
                                    <tr id="purchase-preview-discount-row" style="display:none;">
                                        <td style="padding:2px 0; text-align:left; color:#334155;">(-) ছাড়</td>
                                        <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-discount">৳০.০০</td>
                                    </tr>
                                    <tr id="purchase-preview-tax-row" style="display:none;">
                                        <td style="padding:2px 0; text-align:left; color:#334155;">ভ্যাট</td>
                                        <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-tax">৳০.০০</td>
                                    </tr>
                                    <tr id="purchase-preview-transport-row" style="display:none;">
                                        <td style="padding:2px 0; text-align:left; color:#334155;">পরিবহন খরচ</td>
                                        <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-transport">৳০.০০</td>
                                    </tr>
                                    <tr id="purchase-preview-adjustment-row" style="display:none;">
                                        <td style="padding:2px 0; text-align:left; color:#334155;" id="purchase-preview-adjustment-lbl">সমন্বয়</td>
                                        <td style="padding:2px 0; text-align:right; white-space:nowrap;" id="purchase-preview-adjustment">৳০.০০</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2" style="border-top:1px solid #94a3b8; padding:0; height:1px;"></td>
                                    </tr>
                                    <tr>
                                        <td style="padding:3px 0; text-align:left; font-weight:700;">মোট</td>
                                        <td style="padding:3px 0; text-align:right; font-weight:700; white-space:nowrap;" id="purchase-preview-grand-total">৳০.০০</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:2px 0; text-align:left; color:#334155;">পরিশোধিত</td>
                                        <td style="padding:2px 0; text-align:right; font-weight:600; white-space:nowrap;" id="purchase-preview-paid">৳০.০০</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:2px 0; text-align:left; color:#334155;">বাকি আছে</td>
                                        <td style="padding:2px 0; text-align:right; font-weight:600; white-space:nowrap;" id="purchase-preview-due">
                                            ৳০.০০
                                        </td>
                                    </tr>
                                </table>

                                <div style="margin-top:40px; display:flex; justify-content:flex-end;">
                                    <div style="border-top:1px solid #94a3b8; width:135px; text-align:center; padding-top:4px; font-size:11px; font-weight:600;">
                                        অনুমোদিত স্বাক্ষর
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    @if($printerSetting->show_footer_note && !empty($shop?->invoice_footer))
                        <div style="margin-top:16px; border-top:1px dashed #cbd5e1; padding-top:8px; font-size:11px; color:#475569; text-align:center;">
                            {{ $shop->invoice_footer }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Modal Footer: Back / Edit and Confirm / Make Purchase Buttons --}}
        <div class="modal-foot" style="padding:10px 18px; background:var(--card, #ffffff); border-top:1px solid var(--border, #e2e8f0); display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="arrow-left"
                id="btn-back-from-purchase-preview"
                onclick="closeModal('purchaseInvoicePreviewModal')"
                style="font-weight:600;"
            >
                <span class="bn">ফিরে যান / সম্পাদনা</span>
                <span class="en" style="display:none;">Back to Edit</span>
            </x-core::button>

            <x-core::button
                type="button"
                color="primary"
                size="sm"
                icon="check"
                id="btn-confirm-purchase-submit"
                style="font-weight:700; padding:0 24px;"
            >
                <span class="bn">ক্রয় সম্পন্ন করুন</span>
                <span class="en" style="display:none;">Confirm Purchase</span>
            </x-core::button>
        </div>
    </div>
</div>
