<x-core::layout
    title="বাটন ও আইকন স্টাইল গাইড"
    title-en="Button & Icon Style Guide"
    subtitle="বাটন কম্পোনেন্টের সকল ভ্যারিয়েশন, সাইজ, আইকন এবং সিএসএস ভেরিয়েবল থিমিং"
    subtitle-en="Interactive showcase of all button variations, sizes, icons and CSS variable theming"
    active="styleguide"
>
    <div style="display:flex; flex-direction:column; gap:24px;">

        {{-- Interactive Playground / Sandbox --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                    <x-core::icon name="settings" size="18" style="color:var(--teal-800);" />
                    <span class="bn">ইন্টারেক্টিভ বাটন প্লে-গ্রাউন্ড</span>
                    <span class="en" style="display:none;">Interactive Button Playground</span>
                </div>
            </div>
            <div class="panel-body">
                <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:24px; align-items:start;" class="playground-grid">
                    {{-- Live Preview Box --}}
                    <div style="background:var(--paper); border:1px solid var(--border); border-radius:14px; padding:32px; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:220px; gap:18px;">
                        <div id="playground-target-wrapper" style="width:100%; display:flex; justify-content:center;">
                            <x-core::button
                                id="demo-btn"
                                variant="solid"
                                color="gold"
                                size="md"
                                icon="plus"
                            >
                                <span id="demo-btn-label">Action Button</span>
                            </x-core::button>
                        </div>
                        <div style="width:100%; margin-top:10px;">
                            <div style="font-size:11px; font-weight:700; color:var(--ink-600); margin-bottom:6px; display:flex; justify-content:space-between; align-items:center;">
                                <span>Blade Code Snippet:</span>
                                <x-core::button id="copy-code-btn" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                            </div>
                            <pre id="demo-code-box" style="background:var(--card); border:1px solid var(--border); border-radius:10px; padding:12px; font-size:12px; font-family:'Inter',monospace; color:var(--teal-800); overflow-x:auto; margin:0;"></pre>
                        </div>
                    </div>

                    {{-- Controls --}}
                    <div style="display:flex; flex-direction:column; gap:14px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field" style="margin-top:0;">
                                <label>Variant</label>
                                <select id="ctrl-variant">
                                    <option value="solid" selected>Solid (Default)</option>
                                    <option value="outline">Outline</option>
                                    <option value="soft">Soft / Subtle</option>
                                    <option value="ghost">Ghost</option>
                                    <option value="link">Link</option>
                                </select>
                            </div>
                            <div class="field" style="margin-top:0;">
                                <label>Color Theme</label>
                                <select id="ctrl-color">
                                    <option value="gold" selected>Gold / Primary</option>
                                    <option value="teal">Teal / Brand</option>
                                    <option value="green">Green / Success</option>
                                    <option value="red">Red / Danger</option>
                                    <option value="blue">Blue / Info</option>
                                    <option value="dark">Dark / Ink</option>
                                    <option value="grey">Grey / Secondary</option>
                                    <option value="white">White</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field" style="margin-top:0;">
                                <label>Size</label>
                                <select id="ctrl-size">
                                    <option value="xs">XS (26px)</option>
                                    <option value="sm">SM (32px)</option>
                                    <option value="md" selected>MD (38px)</option>
                                    <option value="lg">LG (46px)</option>
                                    <option value="xl">XL (52px)</option>
                                </select>
                            </div>
                            <div class="field" style="margin-top:0;">
                                <label>Corner Radius</label>
                                <select id="ctrl-rounded">
                                    <option value="default" selected>Default (10px)</option>
                                    <option value="pill">Pill / Full (999px)</option>
                                    <option value="none">Square (0px)</option>
                                    <option value="sm">Small (6px)</option>
                                    <option value="lg">Large (14px)</option>
                                    <option value="xl">Extra Large (18px)</option>
                                </select>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <div class="field" style="margin-top:0;">
                                <label>Left Icon</label>
                                <select id="ctrl-icon">
                                    <option value="none">None</option>
                                    <option value="plus" selected>plus</option>
                                    <option value="save">save</option>
                                    <option value="edit">edit</option>
                                    <option value="trash">trash</option>
                                    <option value="check">check</option>
                                    <option value="check-circle">check-circle</option>
                                    <option value="search">search</option>
                                    <option value="filter">filter</option>
                                    <option value="download">download</option>
                                    <option value="upload">upload</option>
                                    <option value="refresh">refresh</option>
                                    <option value="eye">eye</option>
                                    <option value="copy">copy</option>
                                    <option value="printer">printer</option>
                                    <option value="shopping-cart">shopping-cart</option>
                                    <option value="cash">cash</option>
                                    <option value="credit-card">credit-card</option>
                                    <option value="calendar">calendar</option>
                                    <option value="clock">clock</option>
                                    <option value="tag">tag</option>
                                    <option value="box">box</option>
                                    <option value="barcode">barcode</option>
                                    <option value="percent">percent</option>
                                    <option value="phone">phone</option>
                                    <option value="mail">mail</option>
                                    <option value="user">user</option>
                                    <option value="users">users</option>
                                    <option value="settings">settings</option>
                                    <option value="lock">lock</option>
                                    <option value="unlock">unlock</option>
                                    <option value="info">info</option>
                                    <option value="alert-triangle">alert-triangle</option>
                                    <option value="bell">bell</option>
                                    <option value="logout">logout</option>
                                </select>
                            </div>
                            <div class="field" style="margin-top:0;">
                                <label>Right Icon</label>
                                <select id="ctrl-icon-right">
                                    <option value="none" selected>None</option>
                                    <option value="arrow-right">arrow-right</option>
                                    <option value="arrow-left">arrow-left</option>
                                    <option value="arrow-up">arrow-up</option>
                                    <option value="arrow-down">arrow-down</option>
                                    <option value="chevron-right">chevron-right</option>
                                    <option value="chevron-left">chevron-left</option>
                                    <option value="chevron-down">chevron-down</option>
                                    <option value="chevron-up">chevron-up</option>
                                    <option value="external-link">external-link</option>
                                    <option value="check">check</option>
                                    <option value="x">x</option>
                                </select>
                            </div>
                        </div>

                        <div class="field" style="margin-top:0;">
                            <label>Button Text</label>
                            <input type="text" id="ctrl-text" value="Action Button" placeholder="Button text...">
                        </div>

                        <div style="display:flex; flex-wrap:wrap; gap:14px; margin-top:4px;">
                            <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                <input type="checkbox" id="ctrl-loading"> Loading State
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                <input type="checkbox" id="ctrl-disabled"> Disabled
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                <input type="checkbox" id="ctrl-block"> Block / Full Width
                            </label>
                            <label style="display:flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; cursor:pointer;">
                                <input type="checkbox" id="ctrl-icon-only"> Icon Only
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 1. All Variants by Color --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">১. সকল ভ্যারিয়েন্ট ও কালার (All Variants & Color Palettes)</div>
            </div>
            <div class="panel-body" style="display:flex; flex-direction:column; gap:20px;">
                {{-- Solid --}}
                <div>
                    <div style="font-weight:700; font-size:13px; margin-bottom:10px; color:var(--ink-700);">Solid (Default)</div>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <x-core::button color="gold" icon="plus">Gold / Primary</x-core::button>
                        <x-core::button color="teal" icon="check">Teal / Brand</x-core::button>
                        <x-core::button color="green" icon="check-circle">Green / Success</x-core::button>
                        <x-core::button color="red" icon="trash">Red / Danger</x-core::button>
                        <x-core::button color="blue" icon="info">Blue / Info</x-core::button>
                        <x-core::button color="dark" icon="lock">Dark / Ink</x-core::button>
                        <x-core::button color="grey" icon="settings">Grey / Secondary</x-core::button>
                        <x-core::button color="white" icon="sun">White</x-core::button>
                    </div>
                </div>

                {{-- Outline --}}
                <div>
                    <div style="font-weight:700; font-size:13px; margin-bottom:10px; color:var(--ink-700);">Outline</div>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <x-core::button variant="outline" color="gold" icon="plus">Outline Gold</x-core::button>
                        <x-core::button variant="outline" color="teal" icon="check">Outline Teal</x-core::button>
                        <x-core::button variant="outline" color="green" icon="check-circle">Outline Green</x-core::button>
                        <x-core::button variant="outline" color="red" icon="trash">Outline Red</x-core::button>
                        <x-core::button variant="outline" color="blue" icon="info">Outline Blue</x-core::button>
                        <x-core::button variant="outline" color="dark" icon="lock">Outline Dark</x-core::button>
                        <x-core::button variant="outline" color="grey" icon="settings">Outline Grey</x-core::button>
                    </div>
                </div>

                {{-- Soft / Subtle --}}
                <div>
                    <div style="font-weight:700; font-size:13px; margin-bottom:10px; color:var(--ink-700);">Soft / Subtle (Tinted)</div>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <x-core::button variant="soft" color="gold" icon="plus">Soft Gold</x-core::button>
                        <x-core::button variant="soft" color="teal" icon="check">Soft Teal</x-core::button>
                        <x-core::button variant="soft" color="green" icon="check-circle">Soft Green</x-core::button>
                        <x-core::button variant="soft" color="red" icon="trash">Soft Red</x-core::button>
                        <x-core::button variant="soft" color="blue" icon="info">Soft Blue</x-core::button>
                        <x-core::button variant="soft" color="dark" icon="lock">Soft Dark</x-core::button>
                        <x-core::button variant="soft" color="grey" icon="settings">Soft Grey</x-core::button>
                    </div>
                </div>

                {{-- Ghost --}}
                <div>
                    <div style="font-weight:700; font-size:13px; margin-bottom:10px; color:var(--ink-700);">Ghost (Flat Transparent)</div>
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <x-core::button variant="ghost" color="gold" icon="plus">Ghost Gold</x-core::button>
                        <x-core::button variant="ghost" color="teal" icon="check">Ghost Teal</x-core::button>
                        <x-core::button variant="ghost" color="green" icon="check-circle">Ghost Green</x-core::button>
                        <x-core::button variant="ghost" color="red" icon="trash">Ghost Red</x-core::button>
                        <x-core::button variant="ghost" color="blue" icon="info">Ghost Blue</x-core::button>
                        <x-core::button variant="ghost" color="dark" icon="lock">Ghost Dark</x-core::button>
                    </div>
                </div>

                {{-- Link --}}
                <div>
                    <div style="font-weight:700; font-size:13px; margin-bottom:10px; color:var(--ink-700);">Link</div>
                    <div style="display:flex; flex-wrap:wrap; gap:18px; align-items:center;">
                        <x-core::button variant="link" color="teal" icon-right="external-link">View Documentation</x-core::button>
                        <x-core::button variant="link" color="gold" icon-right="arrow-right">Learn More</x-core::button>
                        <x-core::button variant="link" color="red" icon="trash">Remove permanently</x-core::button>
                        <x-core::button variant="link" color="blue" icon="download">Download Invoice</x-core::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Sizes & Corner Radiuses --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;" class="dash-2col">
            {{-- Sizes --}}
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">২. সাইজ ভ্যারিয়েশন (Size Variations)</div>
                </div>
                <div class="panel-body" style="display:flex; flex-direction:column; gap:12px; align-items:flex-start;">
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:70px; font-size:12px; font-weight:700; color:var(--ink-600);">XS (26px):</span>
                        <x-core::button size="xs" color="teal" icon="plus">Extra Small</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:70px; font-size:12px; font-weight:700; color:var(--ink-600);">SM (32px):</span>
                        <x-core::button size="sm" color="teal" icon="plus">Small</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:70px; font-size:12px; font-weight:700; color:var(--ink-600);">MD (38px):</span>
                        <x-core::button size="md" color="teal" icon="plus">Medium (Default)</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:70px; font-size:12px; font-weight:700; color:var(--ink-600);">LG (46px):</span>
                        <x-core::button size="lg" color="teal" icon="plus">Large</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:70px; font-size:12px; font-weight:700; color:var(--ink-600);">XL (52px):</span>
                        <x-core::button size="xl" color="teal" icon="plus">Extra Large</x-core::button>
                    </div>
                </div>
            </div>

            {{-- Shapes --}}
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">৩. কর্নার শেপ (Corner Radius Options)</div>
                </div>
                <div class="panel-body" style="display:flex; flex-direction:column; gap:12px; align-items:flex-start;">
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:100px; font-size:12px; font-weight:700; color:var(--ink-600);">Default (10px):</span>
                        <x-core::button color="gold">Rounded Default</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:100px; font-size:12px; font-weight:700; color:var(--ink-600);">Pill (999px):</span>
                        <x-core::button rounded="pill" color="gold" icon="check">Pill Rounded</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:100px; font-size:12px; font-weight:700; color:var(--ink-600);">Large (14px):</span>
                        <x-core::button rounded="lg" color="gold">Rounded LG</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:100px; font-size:12px; font-weight:700; color:var(--ink-600);">Small (6px):</span>
                        <x-core::button rounded="sm" color="gold">Rounded SM</x-core::button>
                    </div>
                    <div style="display:flex; align-items:center; gap:14px; width:100%;">
                        <span style="width:100px; font-size:12px; font-weight:700; color:var(--ink-600);">Square (0px):</span>
                        <x-core::button rounded="none" color="gold">Square 0px</x-core::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Icon-Only Buttons & States --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;" class="dash-2col">
            {{-- Icon Only Buttons --}}
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">৪. আইকন বাটন (Icon-Only Buttons)</div>
                </div>
                <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:var(--ink-600); margin-bottom:8px;">Square Action Icons (Table Rows & Toolbars):</div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            <x-core::button icon="edit" variant="outline" size="sm" icon-only title="Edit" />
                            <x-core::button icon="trash" variant="soft" color="red" size="sm" icon-only title="Delete" />
                            <x-core::button icon="eye" variant="soft" color="teal" size="sm" icon-only title="View" />
                            <x-core::button icon="printer" variant="outline" color="dark" size="sm" icon-only title="Print" />
                            <x-core::button icon="download" variant="solid" color="gold" size="sm" icon-only title="Download" />
                        </div>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:var(--ink-600); margin-bottom:8px;">Pill / Circular Icon Buttons:</div>
                        <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
                            <x-core::button icon="plus" color="teal" rounded="pill" size="md" icon-only title="Add" />
                            <x-core::button icon="shopping-cart" color="gold" rounded="pill" size="md" icon-only title="Cart" />
                            <x-core::button icon="bell" variant="soft" color="blue" rounded="pill" size="md" icon-only title="Notifications" />
                            <x-core::button icon="refresh" variant="outline" rounded="pill" size="md" icon-only title="Sync" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- States (Loading, Disabled, Badges) --}}
            <div class="panel" style="margin-top:0;">
                <div class="panel-head">
                    <div class="panel-title">৫. স্পেশাল স্টেট (Loading, Disabled, Badges)</div>
                </div>
                <div class="panel-body" style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <x-core::button color="teal" loading>Saving...</x-core::button>
                        <x-core::button variant="soft" color="red" loading>Deleting</x-core::button>
                        <x-core::button color="gold" disabled>Disabled State</x-core::button>
                        <x-core::button color="teal" badge="12">Inbox</x-core::button>
                        <x-core::button variant="soft" color="blue" badge="New" badge-color="blue">Updates</x-core::button>
                    </div>
                    <div>
                        <div style="font-size:11.5px; font-weight:700; color:var(--ink-600); margin-bottom:8px;">Full Width / Block Buttons:</div>
                        <x-core::button color="gold" block icon="shopping-cart">Checkout Now (৳ ১,৫০০.০০)</x-core::button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Layout Theming & CSS Variable Scoping Demo --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">৬. সিএসএস ভেরিয়েবল থিমিং ও লেআউট অ্যাডাপ্টেশন (CSS Variable Theming)</div>
            </div>
            <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
                <p style="color:var(--ink-600); font-size:13px; margin:0;">
                    বাটন কম্পোনেন্টটি পুরোপুরি সিএসএস কাস্টম প্রপার্টি (CSS Variables) এর মাধ্যমে ডিজাইন করা। যেকোনো পেরেন্ট কন্টেইনার বা লেআউট ব্লকে কালার ভ্যারিয়েবল পরিবর্তন করলে বাটন স্বয়ংক্রিয়ভাবে সেই লেআউটের থিম গ্রহণ করবে।
                </p>

                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;" class="mini-grid">
                    {{-- Purple theme box --}}
                    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; --teal-800:#7C3AED; --teal-700:#6D28D9; --teal-100:#EDE9FE; --gold-500:#8B5CF6; --gold-600:#7C3AED;">
                        <div style="font-weight:700; font-size:12px; color:#7C3AED; margin-bottom:10px;">🟣 Purple Layout Scope</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <x-core::button color="teal" size="sm" icon="check">Custom Scoped Teal</x-core::button>
                            <x-core::button variant="soft" color="teal" size="sm">Soft Purple Theme</x-core::button>
                        </div>
                    </div>

                    {{-- Indigo theme box --}}
                    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; --teal-800:#4F46E5; --teal-700:#4338CA; --teal-100:#EEF2FF;">
                        <div style="font-weight:700; font-size:12px; color:#4F46E5; margin-bottom:10px;">🔵 Indigo Layout Scope</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <x-core::button color="teal" size="sm" icon="shopping-cart">Indigo Scoped Button</x-core::button>
                            <x-core::button variant="outline" color="teal" size="sm">Indigo Outline</x-core::button>
                        </div>
                    </div>

                    {{-- Emerald theme box --}}
                    <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:16px; --teal-800:#059669; --teal-700:#047857; --teal-100:#D1FAE5;">
                        <div style="font-weight:700; font-size:12px; color:#059669; margin-bottom:10px;">🟢 Emerald Layout Scope</div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <x-core::button color="teal" size="sm" icon="save">Emerald Scoped Button</x-core::button>
                            <x-core::button variant="soft" color="teal" size="sm">Emerald Soft</x-core::button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 5. Built-in Icons Grid --}}
        <div class="panel" style="margin-top:0;">
            <div class="panel-head">
                <div class="panel-title">৭. অন্তর্নির্মিত আইকন ক্যাটালগ (50+ Built-in SVG Icons)</div>
            </div>
            <div class="panel-body">
                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(110px, 1fr)); gap:10px;">
                    @php
                        $iconList = [
                            'plus', 'minus', 'edit', 'trash', 'save', 'check', 'check-circle',
                            'x', 'x-circle', 'search', 'filter', 'download', 'upload', 'refresh',
                            'spinner', 'eye', 'eye-off', 'copy', 'printer', 'arrow-left', 'arrow-right',
                            'arrow-up', 'arrow-down', 'chevron-left', 'chevron-right', 'chevron-down',
                            'chevron-up', 'external-link', 'user', 'users', 'shopping-cart', 'cash',
                            'credit-card', 'calendar', 'clock', 'tag', 'box', 'barcode', 'percent',
                            'phone', 'mail', 'settings', 'lock', 'unlock', 'info', 'alert-triangle',
                            'logout', 'bell', 'moon', 'sun'
                        ];
                    @endphp

                    @foreach ($iconList as $ic)
                        <div
                            class="icon-grid-cell"
                            style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px 8px; display:flex; flex-direction:column; align-items:center; gap:8px; cursor:pointer; transition:all .15s ease;"
                            onclick="selectIcon('{{ $ic }}')"
                            title="Click to use icon='{{ $ic }}'"
                        >
                            <x-core::icon :name="$ic" size="20" style="color:var(--teal-800);" />
                            <span style="font-size:10.5px; font-weight:700; color:var(--ink-700); text-align:center; word-break:break-all;">{{ $ic }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Interactive Playground JavaScript --}}
    <script>
        (function () {
            // Icon dictionary of all 50+ SVG paths
            const icons = {
                'plus': '<path d="M12 5v14M5 12h14"/>',
                'minus': '<path d="M5 12h14"/>',
                'edit': '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
                'trash': '<path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/>',
                'save': '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
                'check': '<polyline points="20 6 9 17 4 12"/>',
                'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
                'x': '<path d="M18 6 6 18M6 6l12 12"/>',
                'x-circle': '<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>',
                'search': '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
                'filter': '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
                'download': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
                'upload': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
                'refresh': '<path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>',
                'spinner': '<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-dasharray="32" opacity="0.3"/><path d="M12 3a9 9 0 0 1 9 9"/>',
                'eye': '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
                'eye-off': '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61M2 2l20 20"/>',
                'copy': '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
                'printer': '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>',
                'arrow-left': '<path d="m12 19-7-7 7-7M5 12h14"/>',
                'arrow-right': '<path d="m12 5 7 7-7 7M19 12H5"/>',
                'arrow-up': '<path d="m5 12 7-7 7 7M12 5v14"/>',
                'arrow-down': '<path d="m19 12-7 7-7-7M12 19V5"/>',
                'chevron-left': '<path d="m15 18-6-6 6-6"/>',
                'chevron-right': '<path d="m9 18 6-6-6-6"/>',
                'chevron-down': '<path d="m6 9 6 6 6-6"/>',
                'chevron-up': '<path d="m18 15-6-6-6 6"/>',
                'external-link': '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3"/>',
                'user': '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
                'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
                'shopping-cart': '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                'cash': '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
                'credit-card': '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
                'calendar': '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
                'clock': '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                'tag': '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/>',
                'box': '<path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/>',
                'barcode': '<path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14M5 5v14M15 5v14M19 5v14"/>',
                'percent': '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
                'phone': '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
                'mail': '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                'settings': '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                'lock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                'unlock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
                'info': '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
                'alert-triangle': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
                'logout': '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
                'bell': '<path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/>',
                'moon': '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
                'sun': '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>'
            };

            function makeSvg(inner) {
                if (!inner) return '';
                return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">' + inner + '</svg>';
            }

            function setupPlayground($) {
                function updatePlayground() {
                    const variant = $('#ctrl-variant').val() || 'solid';
                    const color = $('#ctrl-color').val() || 'gold';
                    const size = $('#ctrl-size').val() || 'md';
                    const rounded = $('#ctrl-rounded').val() || 'default';
                    const icon = $('#ctrl-icon').val() || 'none';
                    const iconRight = $('#ctrl-icon-right').val() || 'none';
                    const text = $('#ctrl-text').val() || '';
                    const isLoading = $('#ctrl-loading').is(':checked');
                    const isDisabled = $('#ctrl-disabled').is(':checked');
                    const isBlock = $('#ctrl-block').is(':checked');
                    const isIconOnly = $('#ctrl-icon-only').is(':checked');

                    // Build CSS classes
                    let classes = ['btn'];

                    if (variant === 'outline') {
                        classes.push(color === 'grey' ? 'btn-outline' : 'btn-outline btn-outline-' + color);
                    } else if (variant === 'soft') {
                        classes.push('btn-soft btn-soft-' + color);
                    } else if (variant === 'ghost') {
                        classes.push(color === 'grey' ? 'btn-ghost' : 'btn-ghost btn-ghost-' + color);
                    } else if (variant === 'link') {
                        classes.push(color === 'grey' ? 'btn-link' : 'btn-link btn-link-' + color);
                    } else {
                        classes.push('btn-solid-' + color + ' btn-' + color);
                    }

                    classes.push('btn-' + size);

                    if (rounded === 'pill') classes.push('btn-pill');
                    else if (rounded === 'none') classes.push('btn-rounded-none');
                    else if (rounded !== 'default') classes.push('btn-rounded-' + rounded);

                    if (isIconOnly) classes.push('btn-icon-only');
                    if (isBlock) classes.push('btn-block');
                    if (isLoading) classes.push('is-loading');
                    if (isDisabled) classes.push('disabled');

                    const $btn = $('#demo-btn');
                    $btn.attr('class', classes.join(' '));
                    $btn.prop('disabled', isDisabled || isLoading);

                    // Build HTML inside demo button
                    let innerHtml = '';
                    if (isLoading) {
                        innerHtml += '<span class="btn-spinner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" stroke-dasharray="32" stroke-linecap="round" fill="none" opacity="0.25"/><path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></span>';
                    } else if (icon !== 'none' && icons[icon]) {
                        innerHtml += '<span class="btn-icon">' + makeSvg(icons[icon]) + '</span>';
                    }

                    if (!isIconOnly && text) {
                        innerHtml += '<span class="btn-text">' + $('<div>').text(text).html() + '</span>';
                    }

                    if (!isLoading && iconRight !== 'none' && icons[iconRight]) {
                        innerHtml += '<span class="btn-icon btn-icon-right">' + makeSvg(icons[iconRight]) + '</span>';
                    }

                    $btn.html(innerHtml);

                    // Build Blade Code snippet
                    let bladeCode = '<' + 'x-button';
                    if (variant !== 'solid') bladeCode += ' variant="' + variant + '"';
                    if (color !== 'gold') bladeCode += ' color="' + color + '"';
                    if (size !== 'md') bladeCode += ' size="' + size + '"';
                    if (rounded !== 'default') bladeCode += ' rounded="' + rounded + '"';
                    if (icon !== 'none') bladeCode += ' icon="' + icon + '"';
                    if (iconRight !== 'none') bladeCode += ' icon-right="' + iconRight + '"';
                    if (isLoading) bladeCode += ' loading';
                    if (isDisabled) bladeCode += ' disabled';
                    if (isBlock) bladeCode += ' block';
                    if (isIconOnly) bladeCode += ' icon-only';

                    if (isIconOnly) {
                        bladeCode += ' title="' + (text || 'Action') + '" />';
                    } else {
                        bladeCode += '>\n    ' + (text || 'Click me') + '\n<' + '/x-button>';
                    }

                    $('#demo-code-box').text(bladeCode);
                }

                window.selectIcon = function (name) {
                    const $select = $('#ctrl-icon');
                    if ($select.find('option[value="' + name + '"]').length === 0) {
                        $select.append($('<option>', { value: name, text: name }));
                    }
                    $select.val(name);
                    updatePlayground();
                    if (typeof window.toast === 'function') {
                        window.toast('আইকন নির্বাচন করা হয়েছে: ' + name, 'Selected icon: ' + name);
                    }
                };

                $('#ctrl-variant, #ctrl-color, #ctrl-size, #ctrl-rounded, #ctrl-icon, #ctrl-icon-right').on('change', updatePlayground);
                $('#ctrl-text').on('input', updatePlayground);
                $('#ctrl-loading, #ctrl-disabled, #ctrl-block, #ctrl-icon-only').on('change', updatePlayground);

                $('#copy-code-btn').on('click', function () {
                    const code = $('#demo-code-box').text();
                    navigator.clipboard.writeText(code).then(function () {
                        if (typeof window.toast === 'function') {
                            window.toast('কোড কপি হয়েছে!', 'Code copied to clipboard!');
                        }
                    });
                });

                updatePlayground();
            }

            function bootstrap() {
                if (typeof window.jQuery !== 'undefined') {
                    window.jQuery(function () {
                        setupPlayground(window.jQuery);
                    });
                } else {
                    setTimeout(bootstrap, 20);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootstrap);
            } else {
                bootstrap();
            }
        })();
    </script>
</x-core::layout>
