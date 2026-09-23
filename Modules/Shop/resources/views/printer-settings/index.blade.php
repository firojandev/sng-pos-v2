<x-core::layout
    title="প্রিন্টার ও পেপার সেটিংস"
    title-en="Printer & Paper Settings"
    subtitle="রসিদ ও ইনভয়েস প্রিন্ট করার জন্য প্রিন্টারের ধরন, পেপার সাইজ এবং লেআউট কনফিগার করুন"
    subtitle-en="Configure printer types, paper sizes, and layout behavior for receipts and invoices"
    active="printer-settings"
>
    @if (session('status'))
        <div style="background:var(--green-100); border:1px solid var(--green-ic-bg); color:var(--green-ink); border-radius:12px; padding:12px 18px; margin-bottom:20px; font-size:13.5px; font-weight:600; display:flex; align-items:center; gap:10px;">
            <x-core::icon name="check-circle" size="18" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <style>
        @media (max-width: 1080px) {
            .printer-settings-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    <div style="width:100%; max-width:1400px;">
        <div style="display:grid; grid-template-columns:460px 1fr; gap:24px; align-items:start;" class="printer-settings-grid">
            {{-- Left Column: Form Configuration --}}
            <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:24px; box-shadow:var(--shadow-card);">
                <form method="POST" action="{{ route('printer-settings.update') }}" id="printerSettingsForm">
                    @csrf
                    @method('PUT')

                    {{-- Section 1: Printer Type Selection --}}
                    <div style="margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                        <div style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="printer" size="18" style="color:var(--teal-800);" />
                            <span style="font-size:14.5px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">প্রিন্টারের ধরন</span>
                                <span class="en" style="display:none;">Select Printer Type</span>
                            </span>
                        </div>
                        <x-core::badge color="teal" size="xs" variant="soft">
                            <span class="bn">৩ ধরনের প্রিন্টার সাপোর্টেড</span>
                            <span class="en" style="display:none;">3 Types Supported</span>
                        </x-core::badge>
                    </div>

                    {{-- Visual Type Selector Cards --}}
                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin-bottom:22px;">
                        {{-- Thermal Card --}}
                        <div class="printer-card-opt {{ old('printer_type', $printerSetting->printer_type) === 'thermal' ? 'selected' : '' }}" data-type="thermal" style="border:1.5px solid var(--border); border-radius:12px; padding:12px; cursor:pointer; background:var(--paper); transition:all 0.15s ease; position:relative;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="width:30px; height:30px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                                    <x-core::icon name="receipt" size="16" />
                                </div>
                                <span class="badge-active-dot" style="width:8px; height:8px; border-radius:50%; background:var(--teal-800); display:{{ old('printer_type', $printerSetting->printer_type) === 'thermal' ? 'block' : 'none' }};"></span>
                            </div>
                            <div style="font-weight:700; font-size:13px; color:var(--ink-900);">
                                <span class="bn">থার্মাল প্রিন্টার</span>
                                <span class="en" style="display:none;">Thermal Printer</span>
                            </div>
                            <div style="font-size:10.5px; color:var(--ink-500); margin-top:2px;">
                                58 / 80 / 100mm
                            </div>
                        </div>

                        {{-- A4 Card --}}
                        <div class="printer-card-opt {{ old('printer_type', $printerSetting->printer_type) === 'a4' ? 'selected' : '' }}" data-type="a4" style="border:1.5px solid var(--border); border-radius:12px; padding:12px; cursor:pointer; background:var(--paper); transition:all 0.15s ease; position:relative;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="width:30px; height:30px; border-radius:8px; background:var(--blue-100); color:var(--blue-ink); display:flex; align-items:center; justify-content:center;">
                                    <x-core::icon name="file-text" size="16" />
                                </div>
                                <span class="badge-active-dot" style="width:8px; height:8px; border-radius:50%; background:var(--teal-800); display:{{ old('printer_type', $printerSetting->printer_type) === 'a4' ? 'block' : 'none' }};"></span>
                            </div>
                            <div style="font-weight:700; font-size:13px; color:var(--ink-900);">
                                <span class="bn">A4 প্রিন্টার</span>
                                <span class="en" style="display:none;">A4 Printer</span>
                            </div>
                            <div style="font-size:10.5px; color:var(--ink-500); margin-top:2px;">
                                210 × 297 mm
                            </div>
                        </div>

                        {{-- A5 Card --}}
                        <div class="printer-card-opt {{ old('printer_type', $printerSetting->printer_type) === 'a5' ? 'selected' : '' }}" data-type="a5" style="border:1.5px solid var(--border); border-radius:12px; padding:12px; cursor:pointer; background:var(--paper); transition:all 0.15s ease; position:relative;">
                            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                                <div style="width:30px; height:30px; border-radius:8px; background:var(--purple-100, #f3e8ff); color:var(--purple-ink, #7e22ce); display:flex; align-items:center; justify-content:center;">
                                    <x-core::icon name="file" size="16" />
                                </div>
                                <span class="badge-active-dot" style="width:8px; height:8px; border-radius:50%; background:var(--teal-800); display:{{ old('printer_type', $printerSetting->printer_type) === 'a5' ? 'block' : 'none' }};"></span>
                            </div>
                            <div style="font-weight:700; font-size:13px; color:var(--ink-900);">
                                <span class="bn">A5 প্রিন্টার</span>
                                <span class="en" style="display:none;">A5 Printer</span>
                            </div>
                            <div style="font-size:10.5px; color:var(--ink-500); margin-top:2px;">
                                148 × 210 mm
                            </div>
                        </div>
                    </div>

                    {{-- Hidden Select synced with cards --}}
                    <div style="display:none;">
                        <x-core::select
                            name="printer_type"
                            id="printerTypeSelect"
                            :value="old('printer_type', $printerSetting->printer_type)"
                            :options="[
                                'thermal' => 'Thermal Printer',
                                'a4' => 'A4 Printer',
                                'a5' => 'A5 Printer',
                            ]"
                            size="sm"
                        />
                    </div>

                    {{-- Section 2: Paper Size & Dimension Settings --}}
                    <div style="margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="maximize-2" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">কাগজের সাইজ ও পরিমাপ</span>
                            <span class="en" style="display:none;">Paper Size Configuration</span>
                        </span>
                    </div>

                    {{-- A4 Configuration Details --}}
                    <div id="a4ConfigBox" style="display:none; margin-bottom:22px; background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:16px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                            <div style="width:28px; height:28px; border-radius:6px; background:var(--blue-100); color:var(--blue-ink); display:flex; align-items:center; justify-content:center;">
                                <x-core::icon name="info" size="16" />
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:var(--ink-900);">
                                    <span class="bn">প্রিডিফাইন্ড A4 সাইজ কনফিগারেশন</span>
                                    <span class="en" style="display:none;">Predefined A4 Size</span>
                                </div>
                                <div style="font-size:11.5px; color:var(--ink-500);">
                                    <span class="bn">A4 পেপারের মাপ পূর্বনির্ধারিত (210 × 297 মিমি)। ম্যানুয়াল মাপ ইনপুট দেওয়ার প্রয়োজন নেই।</span>
                                    <span class="en" style="display:none;">Standard A4 dimensions are pre-configured automatically.</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <x-core::input
                                name="a4_width_display"
                                label="প্রস্থ"
                                label-en="Width"
                                value="210 mm"
                                size="sm"
                                icon="move-horizontal"
                                :readonly="true"
                            />

                            <x-core::input
                                name="a4_height_display"
                                label="উচ্চতা"
                                label-en="Height"
                                value="297 mm"
                                size="sm"
                                icon="move-vertical"
                                :readonly="true"
                            />

                            <x-core::input
                                name="a4_unit_display"
                                label="একক"
                                label-en="Unit"
                                value="mm (Millimeter)"
                                size="sm"
                                icon="ruler"
                                :readonly="true"
                            />
                        </div>
                    </div>

                    {{-- A5 Configuration Details --}}
                    <div id="a5ConfigBox" style="display:none; margin-bottom:22px; background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:16px;">
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                            <div style="width:28px; height:28px; border-radius:6px; background:var(--purple-100, #f3e8ff); color:var(--purple-ink, #7e22ce); display:flex; align-items:center; justify-content:center;">
                                <x-core::icon name="info" size="16" />
                            </div>
                            <div>
                                <div style="font-size:13px; font-weight:700; color:var(--ink-900);">
                                    <span class="bn">প্রিডিফাইন্ড A5 সাইজ কনফিগারেশন</span>
                                    <span class="en" style="display:none;">Predefined A5 Size</span>
                                </div>
                                <div style="font-size:11.5px; color:var(--ink-500);">
                                    <span class="bn">A5 পেপারের মাপ পূর্বনির্ধারিত (148 × 210 মিমি)। ম্যানুয়াল মাপ ইনপুটের প্রয়োজন নেই।</span>
                                    <span class="en" style="display:none;">Standard A5 dimensions are pre-configured automatically.</span>
                                </div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <x-core::input
                                name="a5_width_display"
                                label="প্রস্থ"
                                label-en="Width"
                                value="148 mm"
                                size="sm"
                                icon="move-horizontal"
                                :readonly="true"
                            />

                            <x-core::input
                                name="a5_height_display"
                                label="উচ্চতা"
                                label-en="Height"
                                value="210 mm"
                                size="sm"
                                icon="move-vertical"
                                :readonly="true"
                            />

                            <x-core::input
                                name="a5_unit_display"
                                label="একক"
                                label-en="Unit"
                                value="mm (Millimeter)"
                                size="sm"
                                icon="ruler"
                                :readonly="true"
                            />
                        </div>
                    </div>

                    {{-- Thermal Configuration Details (Custom Dimensions) --}}
                    <div id="thermalConfigBox" style="display:none; margin-bottom:22px; background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:16px;">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
                            <div>
                                <div style="font-size:13px; font-weight:700; color:var(--ink-900);">
                                    <span class="bn">থার্মাল পেপার মাপ ও প্রিসেট</span>
                                    <span class="en" style="display:none;">Thermal Paper Size</span>
                                </div>
                                <div style="font-size:11.5px; color:var(--ink-500);">
                                    <span class="bn">রোল পেপারের প্রস্থ ও উচ্চতা কাস্টমাইজ করুন।</span>
                                    <span class="en" style="display:none;">Define thermal paper roll width and height.</span>
                                </div>
                            </div>

                            {{-- Quick Preset Width Buttons --}}
                            <div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
                                <x-core::button type="button" variant="secondary" size="sm" class="btn-thermal-preset" data-w="58" data-unit="mm">58 mm</x-core::button>
                                <x-core::button type="button" variant="secondary" size="sm" class="btn-thermal-preset" data-w="80" data-unit="mm">80 mm</x-core::button>
                                <x-core::button type="button" variant="secondary" size="sm" class="btn-thermal-preset" data-w="100" data-unit="mm">100 mm</x-core::button>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:10px;">
                            <x-core::input
                                name="paper_width"
                                id="thermalPaperWidth"
                                type="number"
                                step="any"
                                label="কাগজের প্রস্থ"
                                label-en="Paper Width"
                                :value="old('paper_width', $printerSetting->paper_width ?: 80)"
                                placeholder="যেমন: 80"
                                placeholder-en="e.g. 80"
                                size="sm"
                                icon="move-horizontal"
                                helper="58mm / 80mm / 100mm"
                                helper-en="Usually 58mm or 80mm"
                                :required="true"
                            />

                            <x-core::input
                                name="paper_height"
                                id="thermalPaperHeight"
                                type="number"
                                step="any"
                                label="উচ্চতা"
                                label-en="Paper Height"
                                :value="old('paper_height', $printerSetting->paper_height)"
                                placeholder="ফাঁকা = অটো রোল"
                                placeholder-en="Auto Continuous Roll"
                                size="sm"
                                icon="move-vertical"
                                helper="ফাঁকা রাখলে অটো রোল"
                                helper-en="Leave empty for continuous roll"
                            />

                            <x-core::select
                                name="unit"
                                id="thermalUnit"
                                label="একক"
                                label-en="Unit"
                                :value="old('unit', $printerSetting->unit ?: 'mm')"
                                :options="[
                                    'mm' => 'মিলিমিটার (mm)',
                                    'inch' => 'ইঞ্চি (inch)',
                                ]"
                                size="sm"
                                icon="ruler"
                            />
                        </div>
                    </div>

                    {{-- Orientation Selection (Visible for A4 & A5) --}}
                    <div id="orientationSection" style="margin-bottom:22px;">
                        <x-core::select
                            name="orientation"
                            id="orientationSelect"
                            label="কাগজের ওরিয়েন্টেশন"
                            label-en="Paper Orientation"
                            :value="old('orientation', $printerSetting->orientation ?: 'portrait')"
                            :options="[
                                'portrait' => 'পোর্ট্রেট (Portrait)',
                                'landscape' => 'ল্যান্ডস্কেপ (Landscape)',
                            ]"
                            size="sm"
                            icon="compass"
                            helper="A4 ও A5 এর জন্য প্রযোজ্য (থার্মাল প্রিন্টারে সবসময় পোর্ট্রেট)"
                            helper-en="Applies to A4 & A5 (Thermal is always portrait)"
                        />
                    </div>

                    {{-- Section 3: Printing Behavior & Options --}}
                    <div style="margin-bottom:18px; padding-bottom:10px; border-bottom:1px solid var(--border); display:flex; align-items:center; gap:8px;">
                        <x-core::icon name="sliders" size="18" style="color:var(--teal-800);" />
                        <span style="font-size:14px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">প্রিন্টিং আচরণ ও উপাদান সেটিংস</span>
                            <span class="en" style="display:none;">Printing Behavior & Layout</span>
                        </span>
                    </div>

                    <div style="display:flex; flex-direction:column; gap:14px; margin-bottom:24px;">
                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px;">
                            <x-core::toggle
                                name="auto_print"
                                id="toggleAutoPrint"
                                :checked="old('auto_print', $printerSetting->auto_print)"
                                label="স্বয়ংক্রিয় প্রিন্ট ডায়ালগ চালু করুন"
                                label-en="Auto Open Print Dialog"
                                description="বিক্রয় সম্পন্ন হলে বা ইনভয়েস পেজ খুললে সরাসরি প্রিন্ট উইন্ডো ওপেন হবে।"
                                description-en="Directly opens system print dialog upon sale completion."
                                color="teal"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px;">
                            <x-core::toggle
                                name="show_header_logo"
                                id="toggleHeaderLogo"
                                :checked="old('show_header_logo', $printerSetting->show_header_logo)"
                                label="রসিদে দোকানের লোগো প্রদর্শন"
                                label-en="Print Shop Logo on Receipt"
                                description="দোকানের লোগো স্লিপের শীর্ষে প্রিন্ট হবে।"
                                description-en="Prints shop logo on the top of receipt."
                                color="teal"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px;">
                            <x-core::toggle
                                name="show_shop_info"
                                id="toggleShopInfo"
                                :checked="old('show_shop_info', $printerSetting->show_shop_info)"
                                label="দোকানের বিবরণ প্রদর্শন"
                                label-en="Print Shop Details"
                                description="দোকানের পূর্ণ ঠিকানা এবং অফিসিয়াল মোবাইল নম্বর প্রিন্ট হবে।"
                                description-en="Prints full shop address and official contact number."
                                color="teal"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px;">
                            <x-core::toggle
                                name="show_customer_due"
                                id="toggleCustomerDue"
                                :checked="old('show_customer_due', $printerSetting->show_customer_due)"
                                label="কাস্টমারের পূর্বের বাকি ও মোট বাকি প্রদর্শন"
                                label-en="Print Customer Due Summary"
                                description="রসিদে কাস্টমারের অতীত বকেয়া এবং সর্বমোট বাকি প্রিন্ট হবে।"
                                description-en="Prints customer previous balance and total remaining due on receipt."
                                color="teal"
                            />
                        </div>

                        <div style="background:var(--paper); border:1px solid var(--border); border-radius:12px; padding:14px;">
                            <x-core::toggle
                                name="show_footer_note"
                                id="toggleFooterNote"
                                :checked="old('show_footer_note', $printerSetting->show_footer_note)"
                                label="ইনভয়েস শর্তাবলী / ফুটার নোট প্রদর্শন"
                                label-en="Print Invoice Footer Note"
                                description="দোকান সেটিংসে নির্ধারিত বিক্রয় শর্তাবলী বা কৃতজ্ঞতা বার্তা স্লিপের নিচে মুদ্রিত হবে।"
                                description-en="Prints terms & conditions or note at the bottom of the receipt."
                                color="teal"
                            />
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <x-core::input
                                name="page_margin"
                                id="inputPageMargin"
                                type="number"
                                step="0.5"
                                min="0"
                                max="50"
                                label="মার্জিন (মিমি)"
                                label-en="Page Margin (mm)"
                                :value="old('page_margin', $printerSetting->page_margin ?: 2)"
                                placeholder="যেমন: 2"
                                placeholder-en="e.g. 2"
                                size="sm"
                                icon="layout"
                            />

                            <x-core::input
                                name="print_copies"
                                id="inputPrintCopies"
                                type="number"
                                min="1"
                                max="10"
                                label="প্রিন্ট কপি সংখ্যা"
                                label-en="Print Copies"
                                :value="old('print_copies', $printerSetting->print_copies ?: 1)"
                                placeholder="১"
                                placeholder-en="1"
                                size="sm"
                                icon="copy"
                            />
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div style="display:flex; align-items:center; justify-content:flex-end; gap:10px; padding-top:16px; border-top:1px solid var(--border);">
                        <x-core::button
                            type="submit"
                            color="primary"
                            size="sm"
                            icon="save"
                        >
                            <span class="bn">প্রিন্টার সেটিংস সংরক্ষণ করুন</span>
                            <span class="en" style="display:none;">Save Printer Settings</span>
                        </x-core::button>
                    </div>
                </form>
            </div>

            {{-- Right Column: Expanded Real-time Live Paper Preview --}}
            <div style="position:sticky; top:20px; display:flex; flex-direction:column; gap:16px;">
                <div style="background:var(--card); border:1px solid var(--border); border-radius:16px; padding:22px; box-shadow:var(--shadow-card);">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; flex-wrap:wrap; gap:10px;">
                        <div style="font-size:14px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="eye" size="18" style="color:var(--teal-800);" />
                            <span class="bn">আউটপুট লাইভ প্রিভিউ</span>
                            <span class="en" style="display:none;">Live Print Output Preview</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span id="previewDimensionBadge" style="font-size:11.5px; font-weight:700; color:var(--teal-800); background:var(--teal-100); border:1px solid var(--teal-200); padding:3px 10px; border-radius:6px;">
                                A4 Portrait (210 × 297 mm)
                            </span>
                        </div>
                    </div>

                    {{-- Workspace Canvas --}}
                    <div style="background:var(--paper-line); border:1px dashed var(--border); border-radius:14px; padding:24px 16px; display:flex; justify-content:center; align-items:flex-start; min-height:640px; max-height:820px; overflow-y:auto; overflow-x:auto;">

                        {{-- PREVIEW TEMPLATE 1: A4 & A5 Exact Formal Invoice Sheet --}}
                        <div id="previewA4A5Box" style="background:#ffffff; color:#0f172a; box-shadow:0 8px 24px rgba(0,0,0,0.12); border-radius:4px; padding:24px 28px; font-family:'Noto Sans Bengali',sans-serif; font-size:12px; line-height:1.4; width:100%; max-width:680px; transition:all 0.2s ease; box-sizing:border-box; overflow:hidden;">
                            {{-- Top Header with Shop Info (Logo on Left Side) --}}
                            <div style="display:flex; align-items:center; justify-content:center; gap:14px; margin-bottom:12px;">
                                <div id="previewA4Logo" style="flex-shrink:0; width:48px; height:48px; border-radius:6px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                                    @if(!empty($shop?->logo))
                                        <img src="{{ $shop->logo_url ?? asset($shop->logo) }}" alt="Shop Logo" style="max-width:48px; max-height:48px; object-fit:contain;">
                                    @else
                                        <div style="width:46px; height:46px; border-radius:6px; background:#0d9488; color:#ffffff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:18px;">
                                            {{ mb_substr($shop->name ?? 'S', 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                                <div>
                                    <div style="font-size:18px; font-weight:800; color:#0f172a; line-height:1.25;">
                                        {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
                                    </div>
                                    <div id="previewA4ShopInfo">
                                        @if(!empty($shop?->address))
                                            <div style="font-size:11.5px; color:#475569; margin-top:2px;">
                                                {{ $shop->address }}
                                            </div>
                                        @endif
                                        @if(!empty($shop?->phone))
                                            <div style="font-size:11.5px; color:#475569; margin-top:1px;">
                                                মোবাইল : {{ $shop->phone }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Centered Title with Horizontal Accent Lines --}}
                            <div style="display:flex; align-items:center; justify-content:center; gap:16px; margin:10px 0 14px 0;">
                                <div style="flex:1; height:1px; background:#94a3b8;"></div>
                                <div style="font-size:14px; font-weight:600; color:#334155; letter-spacing:0.5px; padding:0 8px;">
                                    ইনভয়েস
                                </div>
                                <div style="flex:1; height:1px; background:#94a3b8;"></div>
                            </div>

                            {{-- Metadata: Customer & Sale Information --}}
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:11.5px; line-height:1.6; margin-bottom:12px;">
                                <div style="width:50%;">
                                    <div><b>ক্রেতার নাম :</b> জনাব আরিফুল ইসলাম</div>
                                    <div><b>মোবাইল নং :</b> 01712345678</div>
                                    <div><b>ঠিকানা :</b> ধানমন্ডি, ঢাকা</div>
                                </div>
                                <div style="width:50%; text-align:right;">
                                    <div><b>বিক্রয় প্রতিনিধি :</b> অ্যাডমিন</div>
                                    <div><b>ইনভয়েস নং :</b> <span style="font-weight:700;">#INV-2026-089</span></div>
                                    <div><b>তারিখ :</b> আজ, ১১:৩০ AM</div>
                                </div>
                            </div>

                            {{-- Items Table (Exact match to real invoice) --}}
                            <table style="width:100% !important; min-width:0 !important; max-width:100% !important; table-layout:fixed !important; border-collapse:separate !important; border-spacing:0 !important; border:1px solid #94a3b8 !important; margin:12px 0 !important; font-size:11px; box-sizing:border-box;">
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
                                        <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; font-weight:700; box-sizing:border-box;">#</th>
                                        <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:center; font-weight:700; box-sizing:border-box;">পণ্যের নাম</th>
                                        <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; font-weight:700; box-sizing:border-box;">পরিমান</th>
                                        <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; font-weight:700; box-sizing:border-box;">ইউনিট</th>
                                        <th style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700; box-sizing:border-box;">ইউনিট মূল্য</th>
                                        <th style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; font-weight:700; box-sizing:border-box;">মোট</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; box-sizing:border-box;">১.</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:left; vertical-align:middle; white-space:normal !important; word-break:break-word; overflow-wrap:anywhere; box-sizing:border-box;">
                                            <div style="font-weight:600; color:#0f172a; line-height:1.3;">প্রিমিয়াম কটন শার্ট (L - ব্লু)</div>
                                            <div style="font-size:9.5px; color:#64748b; margin-top:2px;">বারকোড : 890123456789 &middot; SKU : SHT-BL-01</div>
                                        </td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">২</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">পিছ</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:right; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">৳১,২০০.০০</td>
                                        <td style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 4px; text-align:right; vertical-align:middle; font-weight:600; white-space:nowrap; box-sizing:border-box;">৳২,৪০০.০০</td>
                                    </tr>
                                    <tr>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; box-sizing:border-box;">২.</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 8px; text-align:left; vertical-align:middle; white-space:normal !important; word-break:break-word; overflow-wrap:anywhere; box-sizing:border-box;">
                                            <div style="font-weight:600; color:#0f172a; line-height:1.3;">স্ট্র্যাচ জিন্স প্যান্ট (ব্ল্যাক 32)</div>
                                            <div style="font-size:9.5px; color:#64748b; margin-top:2px;">SKU : JNS-BK-32</div>
                                        </td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">১</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 2px; text-align:center; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">পিছ</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:1px solid #94a3b8 !important; border-top:none; border-left:none; padding:6px 4px; text-align:right; vertical-align:middle; white-space:nowrap; box-sizing:border-box;">৳১,৮০০.০০</td>
                                        <td style="border-bottom:1px solid #94a3b8 !important; border-right:none; border-top:none; border-left:none; padding:6px 4px; text-align:right; vertical-align:middle; font-weight:600; white-space:nowrap; box-sizing:border-box;">৳১,৮০০.০০</td>
                                    </tr>

                                    {{-- Subtotal Row --}}
                                    <tr style="font-weight:700; background:#f8fafc;">
                                        <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 8px; text-align:center; box-sizing:border-box;">সর্বমোট পরিমান</td>
                                        <td style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 2px; text-align:center; white-space:nowrap; box-sizing:border-box;">৩</td>
                                        <td colspan="2" style="border-right:1px solid #94a3b8 !important; border-bottom:none; border-top:none; border-left:none; padding:6px 4px; text-align:center; box-sizing:border-box;">মোট</td>
                                        <td style="border:none; padding:6px 4px; text-align:right; white-space:nowrap; box-sizing:border-box;">৳৪,২০০.০০</td>
                                    </tr>
                                </tbody>
                            </table>

                            {{-- Lower Summary Section (2 Columns) --}}
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; font-size:11px; line-height:1.5; color:#0f172a;">
                                {{-- Left Column --}}
                                <div style="width:48%;">
                                    <div id="previewA4CustomerDue">
                                        <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                                            <span>পূর্বের বাকি :</span>
                                            <span>৳৫০০.০০</span>
                                        </div>
                                        <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:2px;">
                                            <span>বর্তমান বাকি :</span>
                                            <span>৳৪০০.০০</span>
                                        </div>
                                        <div style="border-top:1px solid #94a3b8; max-width:210px; margin:4px 0;"></div>
                                        <div style="display:flex; justify-content:space-between; max-width:210px; margin-bottom:6px; font-weight:700;">
                                            <span>টোটাল বাকি :</span>
                                            <span>৳৯০০.০০</span>
                                        </div>
                                    </div>

                                    <div style="margin-top:12px;">
                                        <div style="font-weight:700; margin-bottom:2px;">অ্যামাউন্ট (কথায়):</div>
                                        <div style="color:#334155; font-size:10.5px;">চার হাজার দুইশত টাকা মাত্র</div>
                                    </div>

                                    <div style="margin-top:30px;">
                                        <div style="border-top:1px solid #94a3b8; width:125px; text-align:center; padding-top:3px; font-weight:600;">
                                            ক্রেতার স্বাক্ষর
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Column --}}
                                <div style="width:44%;">
                                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                        <span style="color:#475569;">সাব টোটাল</span>
                                        <span style="font-weight:600;">৳৪,২০০.০০</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                        <span style="color:#475569;">(-) ছাড়</span>
                                        <span>৳২০০.০০</span>
                                    </div>
                                    <div style="border-top:1px solid #94a3b8; margin:5px 0;"></div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:3px; font-weight:800; font-size:12px;">
                                        <span>মোট</span>
                                        <span>৳৪,০০০.০০</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:3px;">
                                        <span style="color:#475569;">পরিশোধিত</span>
                                        <span style="font-weight:600;">৳৩,৬০০.০০</span>
                                    </div>
                                    <div style="border-top:1px solid #94a3b8; margin:5px 0;"></div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#dc2626; font-weight:700;">
                                        <span>বাকি আছে</span>
                                        <span>৳৪০০.০০</span>
                                    </div>

                                    <div style="margin-top:30px; display:flex; justify-content:flex-end;">
                                        <div style="border-top:1px solid #94a3b8; width:125px; text-align:center; padding-top:3px; font-weight:600;">
                                            বিক্রেতার স্বাক্ষর
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Note --}}
                            <div id="previewA4FooterNote" style="margin-top:14px; border-top:1px dashed #cbd5e1; padding-top:6px; font-size:10.5px; color:#64748b; text-align:center;">
                                {{ $shop->invoice_footer ?: 'বিক্রীত পণ্য ৭ দিনের মধ্যে পরিবর্তনের সুযোগ রয়েছে। ক্যাশ মেমো সাথে রাখবেন। ধন্যবাদ!' }}
                            </div>
                        </div>

                        {{-- PREVIEW TEMPLATE 2: Thermal Roll POS Receipt (Exact Receipt Ribbon) --}}
                        <div id="previewThermalBox" style="display:none; background:#ffffff; color:#000000; box-shadow:0 8px 24px rgba(0,0,0,0.18); border-radius:2px; padding:14px 12px; font-family:'Noto Sans Bengali', monospace, sans-serif; font-size:11px; line-height:1.35; width:300px; max-width:100%; transition:all 0.2s ease; box-sizing:border-box; overflow:hidden;">
                            {{-- Thermal Header --}}
                            <div style="text-align:center; margin-bottom:6px;">
                                <div id="previewThermalLogo" style="margin-bottom:4px; display:flex; justify-content:center;">
                                    @if ($shop->logo_url)
                                        <img src="{{ $shop->logo_url }}" alt="Logo" style="max-height:36px; max-width:100px; object-fit:contain; filter:grayscale(100%);">
                                    @else
                                        <div style="width:30px; height:30px; border-radius:4px; background:#000; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; margin:0 auto;">
                                            {{ mb_substr($shop->name ?? 'S', 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                                <div style="font-size:15px; font-weight:800; color:#000000; line-height:1.2;">
                                    {{ $shop->name ?? 'ব্যবসা প্রতিষ্ঠান' }}
                                </div>

                                <div id="previewThermalShopInfo">
                                    @if (!empty($shop?->address))
                                        <div style="font-size:10px; margin-top:2px;">{{ $shop->address }}</div>
                                    @endif
                                    @if (!empty($shop?->phone))
                                        <div style="font-size:10px; margin-top:1px;">মোবাইল: {{ $shop->phone }}</div>
                                    @endif
                                </div>
                            </div>

                            <div style="border-top:1px dashed #000; margin:6px 0;"></div>

                            {{-- Invoice Meta --}}
                            <div style="font-size:10px;">
                                <div style="display:flex; justify-content:space-between;">
                                    <span>ইনভয়েস: <b>#INV-2026-089</b></span>
                                    <span>আজ, ১১:৩০ AM</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-top:1px;">
                                    <span>ক্রেতা: <b>আরিফুল ইসলাম</b></span>
                                    <span>01712345678</span>
                                </div>
                                <div style="margin-top:1px;">বিক্রেতা: অ্যাডমিন</div>
                            </div>

                            <div style="border-top:1px dashed #000; margin:6px 0;"></div>

                            {{-- Items Table --}}
                            <table style="width:100% !important; min-width:0 !important; max-width:100% !important; table-layout:fixed !important; border-collapse:collapse !important; margin:4px 0 !important; font-size:10px !important; box-sizing:border-box;">
                                <colgroup>
                                    <col style="width:48%;">
                                    <col style="width:12%;">
                                    <col style="width:20%;">
                                    <col style="width:20%;">
                                </colgroup>
                                <thead>
                                    <tr style="border-bottom:1px dashed #000000;">
                                        <th style="text-align:left; padding:4px 6px 4px 0; font-weight:700; box-sizing:border-box; overflow:hidden;">আইটেম</th>
                                        <th style="text-align:center; padding:4px 2px; font-weight:700; box-sizing:border-box; overflow:hidden;">পরি.</th>
                                        <th style="text-align:right; padding:4px 2px; font-weight:700; box-sizing:border-box; overflow:hidden;">দর</th>
                                        <th style="text-align:right; padding:4px 0 4px 2px; font-weight:700; box-sizing:border-box; overflow:hidden;">মোট</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="border-bottom:1px dotted #cccccc;">
                                        <td style="text-align:left; vertical-align:top; padding:5px 6px 5px 0; box-sizing:border-box; overflow:hidden;">
                                            <div style="font-weight:700; color:#000000; font-size:10px; line-height:1.3; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important; display:block;">
                                                প্রিমিয়াম কটন শার্ট (L - ব্লু)
                                            </div>
                                            <div style="font-size:8.5px; color:#555555; margin-top:2px; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important;">
                                                SKU : SHT-BL-01
                                            </div>
                                        </td>
                                        <td style="text-align:center; vertical-align:top; padding:5px 2px; box-sizing:border-box; white-space:nowrap; overflow:hidden; font-size:10.5px;">
                                            ২
                                        </td>
                                        <td style="text-align:right; vertical-align:top; padding:5px 2px; box-sizing:border-box; white-space:nowrap; overflow:hidden; font-size:10px;">
                                            ৳১,২০০
                                        </td>
                                        <td style="text-align:right; vertical-align:top; padding:5px 0 5px 2px; box-sizing:border-box; font-weight:700; white-space:nowrap; overflow:hidden; font-size:10px;">
                                            ৳২,৪০০
                                        </td>
                                    </tr>
                                    <tr style="border-bottom:1px dotted #cccccc;">
                                        <td style="text-align:left; vertical-align:top; padding:5px 6px 5px 0; box-sizing:border-box; overflow:hidden;">
                                            <div style="font-weight:700; color:#000000; font-size:10px; line-height:1.3; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important; display:block;">
                                                স্ট্র্যাচ জিন্স প্যান্ট (ব্ল্যাক 32)
                                            </div>
                                            <div style="font-size:8.5px; color:#555555; margin-top:2px; white-space:normal !important; word-break:break-word !important; overflow-wrap:break-word !important;">
                                                SKU : JNS-BK-32
                                            </div>
                                        </td>
                                        <td style="text-align:center; vertical-align:top; padding:5px 2px; box-sizing:border-box; white-space:nowrap; overflow:hidden; font-size:10.5px;">
                                            ১
                                        </td>
                                        <td style="text-align:right; vertical-align:top; padding:5px 2px; box-sizing:border-box; white-space:nowrap; overflow:hidden; font-size:10px;">
                                            ৳১,৮০০
                                        </td>
                                        <td style="text-align:right; vertical-align:top; padding:5px 0 5px 2px; box-sizing:border-box; font-weight:700; white-space:nowrap; overflow:hidden; font-size:10px;">
                                            ৳১,৮০০
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div style="border-top:1px dashed #000; margin:6px 0;"></div>

                            {{-- Totals --}}
                            <div style="font-size:10.5px;">
                                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                    <span>সাব টোটাল:</span>
                                    <span>৳৪,২০০.০০</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                    <span>(-) ছাড়:</span>
                                    <span>৳২০০.০০</span>
                                </div>
                                <div style="border-top:2px solid #000; margin:4px 0;"></div>
                                <div style="display:flex; justify-content:space-between; font-weight:800; font-size:12px; margin:2px 0;">
                                    <span>সর্বমোট:</span>
                                    <span>৳৪,০০০.০০</span>
                                </div>
                                <div style="border-top:1px dashed #000; margin:4px 0;"></div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:2px;">
                                    <span>পরিশোধ:</span>
                                    <span>৳৩,৬০০.০০</span>
                                </div>
                                <div style="display:flex; justify-content:space-between; font-weight:700; color:#b91c1c;">
                                    <span>বর্তমান বাকি:</span>
                                    <span>৳৪০০.০০</span>
                                </div>

                                {{-- Customer Due --}}
                                <div id="previewThermalCustomerDue" style="margin-top:6px; padding:4px 6px; background:#f8fafc; border:1px solid #000; border-radius:3px; box-sizing:border-box; width:100%;">
                                    <div style="display:flex; justify-content:space-between; font-size:9.5px;">
                                        <span>পূর্বের বাকি:</span>
                                        <span>৳৫০০.০০</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:10.5px; margin-top:2px;">
                                        <span>সর্বমোট বকেয়া:</span>
                                        <span>৳৯০০.০০</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer Note --}}
                            <div id="previewThermalFooterNote" style="text-align:center; font-size:9px; color:#333; margin-top:8px; padding-top:6px; border-top:1px dashed #000; word-break:break-word;">
                                {{ $shop->invoice_footer ?: 'বিক্রীত পণ্য ৭ দিনের মধ্যে পরিবর্তনের সুযোগ রয়েছে। ধন্যবাদ!' }}
                            </div>

                            <div style="text-align:center; margin-top:8px; font-size:9px;">
                                <div>*** ধন্যবাদ, আবার আসবেন ***</div>
                            </div>
                        </div>
                    </div>

                    <div style="font-size:11px; color:var(--ink-500); text-align:center; margin-top:12px;">
                        <span class="bn">নির্বাচিত প্রিন্টার ফরম্যাট অনুযায়ী লাইভ আউটপুট প্রদর্শিত হচ্ছে</span>
                        <span class="en" style="display:none;">Showing live output based on selected printer layout</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                const $printerTypeSelect = $('#printerTypeSelect');
                const $a4ConfigBox = $('#a4ConfigBox');
                const $a5ConfigBox = $('#a5ConfigBox');
                const $thermalConfigBox = $('#thermalConfigBox');
                const $orientationSection = $('#orientationSection');
                const $orientationSelect = $('#orientationSelect');
                const $thermalPaperWidth = $('#thermalPaperWidth');
                const $thermalPaperHeight = $('#thermalPaperHeight');
                const $thermalUnit = $('#thermalUnit');
                const $previewDimensionBadge = $('#previewDimensionBadge');

                // Preview Containers
                const $previewA4A5Box = $('#previewA4A5Box');
                const $previewThermalBox = $('#previewThermalBox');

                // Toggles
                const $toggleHeaderLogo = $('#toggleHeaderLogo');
                const $toggleShopInfo = $('#toggleShopInfo');
                const $toggleCustomerDue = $('#toggleCustomerDue');
                const $toggleFooterNote = $('#toggleFooterNote');

                // Toggle Preview Elements
                const $previewA4Logo = $('#previewA4Logo');
                const $previewA4ShopInfo = $('#previewA4ShopInfo');
                const $previewA4CustomerDue = $('#previewA4CustomerDue');
                const $previewA4FooterNote = $('#previewA4FooterNote');

                const $previewThermalLogo = $('#previewThermalLogo');
                const $previewThermalShopInfo = $('#previewThermalShopInfo');
                const $previewThermalCustomerDue = $('#previewThermalCustomerDue');
                const $previewThermalFooterNote = $('#previewThermalFooterNote');

                function updateUi() {
                    const currentType = $printerTypeSelect.val();
                    const orientation = $orientationSelect.val();

                    // Update Card selection style
                    $('.printer-card-opt').each(function () {
                        const type = $(this).data('type');
                        if (type === currentType) {
                            $(this).css({
                                'border-color': 'var(--teal-800)',
                                'background': 'var(--card)'
                            });
                            $(this).find('.badge-active-dot').show();
                        } else {
                            $(this).css({
                                'border-color': 'var(--border)',
                                'background': 'var(--paper)'
                            });
                            $(this).find('.badge-active-dot').hide();
                        }
                    });

                    if (currentType === 'a4') {
                        $a4ConfigBox.slideDown(150);
                        $a5ConfigBox.hide();
                        $thermalConfigBox.hide();
                        $orientationSection.slideDown(150);

                        // Show A4/A5 box, hide Thermal box
                        $previewA4A5Box.show();
                        $previewThermalBox.hide();

                        const isLandscape = orientation === 'landscape';
                        $previewDimensionBadge.text(isLandscape ? 'A4 Landscape (297 × 210 mm)' : 'A4 Portrait (210 × 297 mm)');

                        $previewA4A5Box.css({
                            'max-width': isLandscape ? '800px' : '680px',
                            'padding': isLandscape ? '20px 24px' : '24px 28px',
                            'font-size': '12px'
                        });
                        $previewA4A5Box.find('table').css('font-size', '11px');
                    } else if (currentType === 'a5') {
                        $a4ConfigBox.hide();
                        $a5ConfigBox.slideDown(150);
                        $thermalConfigBox.hide();
                        $orientationSection.slideDown(150);

                        // Show A4/A5 box, hide Thermal box
                        $previewA4A5Box.show();
                        $previewThermalBox.hide();

                        const isLandscape = orientation === 'landscape';
                        $previewDimensionBadge.text(isLandscape ? 'A5 Landscape (210 × 148 mm)' : 'A5 Portrait (148 × 210 mm)');

                        $previewA4A5Box.css({
                            'max-width': isLandscape ? '660px' : '560px',
                            'padding': isLandscape ? '16px 20px' : '20px 22px',
                            'font-size': '11px'
                        });
                        $previewA4A5Box.find('table').css('font-size', '10px');
                    } else {
                        // Thermal
                        $a4ConfigBox.hide();
                        $a5ConfigBox.hide();
                        $thermalConfigBox.slideDown(150);
                        $orientationSection.slideUp(150);

                        // Show Thermal box, hide A4/A5 box
                        $previewA4A5Box.hide();
                        $previewThermalBox.show();

                        const widthVal = parseFloat($thermalPaperWidth.val()) || 80;
                        const unitVal = $thermalUnit.val() || 'mm';
                        const heightVal = parseFloat($thermalPaperHeight.val());
                        const heightBadge = heightVal ? (heightVal + ' ' + unitVal) : 'অটো রোল';

                        $previewDimensionBadge.text('Thermal ' + widthVal + ' ' + unitVal + ' × ' + heightBadge);

                        // Visual scaling for thermal receipt ribbon
                        let effectiveMm = widthVal;
                        if (unitVal === 'inch') {
                            effectiveMm = widthVal * 25.4;
                        }

                        let ribbonWidth = 300;
                        let fontSize = '11px';

                        if (effectiveMm <= 60) {
                            ribbonWidth = 240;
                            fontSize = '10px';
                        } else if (effectiveMm >= 95) {
                            ribbonWidth = 380;
                            fontSize = '11.5px';
                        } else {
                            ribbonWidth = 300;
                            fontSize = '11px';
                        }

                        $previewThermalBox.css({
                            'width': ribbonWidth + 'px',
                            'max-width': '100%',
                            'font-size': fontSize
                        });
                    }

                    // Toggles live update for A4/A5 preview
                    const showLogo = $toggleHeaderLogo.is(':checked');
                    const showInfo = $toggleShopInfo.is(':checked');
                    const showDue = $toggleCustomerDue.is(':checked');
                    const showFooter = $toggleFooterNote.is(':checked');

                    $previewA4Logo.toggle(showLogo);
                    $previewA4ShopInfo.toggle(showInfo);
                    $previewA4CustomerDue.toggle(showDue);
                    $previewA4FooterNote.toggle(showFooter);

                    // Toggles live update for Thermal preview
                    $previewThermalLogo.toggle(showLogo);
                    $previewThermalShopInfo.toggle(showInfo);
                    $previewThermalCustomerDue.toggle(showDue);
                    $previewThermalFooterNote.toggle(showFooter);
                }

                // Card Click
                $('.printer-card-opt').on('click', function () {
                    const selectedType = $(this).data('type');
                    $printerTypeSelect.val(selectedType);

                    if (selectedType === 'a4' || selectedType === 'a5') {
                        // keep current or default
                    } else if (selectedType === 'thermal') {
                        $orientationSelect.val('portrait');
                    }

                    updateUi();
                });

                // Input events
                $printerTypeSelect.on('change', updateUi);
                $orientationSelect.on('change', updateUi);
                $thermalPaperWidth.on('input', updateUi);
                $thermalPaperHeight.on('input', updateUi);
                $thermalUnit.on('change', updateUi);

                // Quick presets
                $('.btn-thermal-preset').on('click', function () {
                    const presetW = $(this).data('w');
                    const presetUnit = $(this).data('unit');
                    $thermalPaperWidth.val(presetW);
                    $thermalUnit.val(presetUnit);
                    updateUi();
                });

                // Toggles
                $toggleHeaderLogo.on('change', updateUi);
                $toggleShopInfo.on('change', updateUi);
                $toggleCustomerDue.on('change', updateUi);
                $toggleFooterNote.on('change', updateUi);

                // Initial render
                updateUi();
            });
        </script>
    @endpush
</x-core::layout>
