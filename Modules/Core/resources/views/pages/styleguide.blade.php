<x-core::layout
    title="UI কম্পোনেন্ট ও ফর্ম স্টাইল গাইড"
    title-en="UI Components & Form Style Guide"
    subtitle="বাটন, ইনপুট, সিলেক্ট, চেকবক্স, টগল, রেডিও, টেবিল এবং সকল UI উপাদানের লাইভ ইন্টারেক্টিভ প্রিভিউ"
    subtitle-en="Interactive showcase and sandbox of buttons, inputs, selects, checkboxes, toggles, radios, tables and CSS theming"
    active="styleguide"
>
    {{-- CSS Styles for Styleguide Layout --}}
    <style>
        .sg-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            gap: 24px;
            align-items: start;
        }
        .sg-sidebar {
            position: sticky;
            top: 16px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            box-shadow: var(--shadow-sm);
            max-height: calc(100vh - 32px);
            overflow-y: auto;
        }
        .sg-nav-heading {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            color: var(--ink-400);
            text-transform: uppercase;
            padding: 10px 10px 4px;
            margin-top: 4px;
        }
        .sg-nav-heading:first-of-type { margin-top: 0; }
        .sg-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-700);
            cursor: pointer;
            transition: all .15s ease;
            text-decoration: none;
            border: 1px solid transparent;
        }
        .sg-nav-link:hover {
            background: var(--paper);
            color: var(--teal-800);
        }
        .sg-nav-link.active {
            background: var(--teal-100);
            color: var(--teal-800);
            border-color: rgba(13,148,136,.2);
            font-weight: 700;
        }
        .sg-nav-link .badge-num {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 99px;
            background: var(--paper);
            color: var(--ink-600);
            border: 1px solid var(--border);
        }
        .sg-nav-link.active .badge-num {
            background: #FFFFFF;
            color: var(--teal-800);
            border-color: transparent;
        }
        .sg-pane {
            display: none;
            flex-direction: column;
            gap: 22px;
        }
        .sg-pane.active {
            display: flex;
        }
        .sg-preview-box {
            background: var(--paper);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 180px;
            gap: 16px;
        }
        .sg-code-wrapper {
            width: 100%;
            margin-top: 6px;
        }
        .sg-code-head {
            font-size: 11px;
            font-weight: 700;
            color: var(--ink-600);
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .sg-code-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 12px 14px;
            font-size: 12px;
            font-family: 'JetBrains Mono', 'Fira Code', 'Inter', monospace;
            color: var(--teal-800);
            overflow-x: auto;
            margin: 0;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.5;
        }
        @media (max-width: 900px) {
            .sg-layout {
                grid-template-columns: 1fr;
            }
            .sg-sidebar {
                position: static;
                max-height: none;
                flex-direction: row;
                overflow-x: auto;
                padding: 8px;
                white-space: nowrap;
            }
            .sg-nav-heading { display: none; }
            .sg-nav-search { display: none; }
        }
    </style>

    <div class="sg-layout">
        {{-- Left Component Navigation Sidebar --}}
        <aside class="sg-sidebar">
            <div class="sg-nav-search" style="margin-bottom:6px;">
                <x-core::input
                    id="sg-search-input"
                    placeholder="কম্পোনেন্ট খুঁজুন..."
                    icon="search"
                    size="sm"
                    clearable
                    no-margin
                />
            </div>

            <div class="sg-nav-heading">বাটন ও আইকন (Actions)</div>
            <a class="sg-nav-link active" data-target="pane-buttons" href="#buttons">
                <x-core::icon name="plus" size="16" />
                <span>বাটন / Buttons</span>
                <span class="badge-num">50+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-icons" href="#icons">
                <x-core::icon name="tag" size="16" />
                <span>আইকন / Icons SVG</span>
                <span class="badge-num">50+</span>
            </a>

            <div class="sg-nav-heading">ফর্ম ইনপুট (Form Controls)</div>
            <a class="sg-nav-link" data-target="pane-inputs" href="#inputs">
                <x-core::icon name="edit" size="16" />
                <span>টেক্সট ইনপুট / Inputs</span>
                <span class="badge-num">12+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-selects" href="#selects">
                <x-core::icon name="chevron-down" size="16" />
                <span>সিলেক্ট / Select Dropdown</span>
                <span class="badge-num">6+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-textareas" href="#textareas">
                <x-core::icon name="mail" size="16" />
                <span>টেক্সট-এরিয়া / Textarea</span>
                <span class="badge-num">4+</span>
            </a>

            <div class="sg-nav-heading">সিলেকশন কন্ট্রোল (Selections)</div>
            <a class="sg-nav-link" data-target="pane-checkboxes" href="#checkboxes">
                <x-core::icon name="check" size="16" />
                <span>চেকবক্স / Checkbox</span>
                <span class="badge-num">8+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-toggles" href="#toggles">
                <x-core::icon name="sun" size="16" />
                <span>সুইচ / Toggle Switch</span>
                <span class="badge-num">6+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-radios" href="#radios">
                <x-core::icon name="credit-card" size="16" />
                <span>রেডিও ও কার্ড / Radios</span>
                <span class="badge-num">8+</span>
            </a>

            <div class="sg-nav-heading">ডেটা ও টেবিল (Data Tables)</div>
            <a class="sg-nav-link" data-target="pane-tables" href="#tables">
                <x-core::icon name="box" size="16" />
                <span>টেবিল / Data Tables</span>
                <span class="badge-num">10+</span>
            </a>

            <div class="sg-nav-heading">ফিডব্যাক ও থিমিং (Layout)</div>
            <a class="sg-nav-link" data-target="pane-validation" href="#validation">
                <x-core::icon name="alert-triangle" size="16" />
                <span>ভ্যালিডেশন / Feedback</span>
                <span class="badge-num">4+</span>
            </a>
            <a class="sg-nav-link" data-target="pane-theming" href="#theming">
                <x-core::icon name="settings" size="16" />
                <span>লেআউট থিমিং / CSS Scopes</span>
                <span class="badge-num">3</span>
            </a>
        </aside>

        {{-- Right Content Area (Separated Panes) --}}
        <main class="sg-content">

            {{-- ========================================================================= --}}
            {{-- PANE 1: BUTTONS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane active" id="pane-buttons">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="settings" size="18" style="color:var(--teal-800);" />
                            <span class="bn">ইন্টারেক্টিভ বাটন প্লে-গ্রাউন্ড</span>
                            <span class="en" style="display:none;">Interactive Button Playground</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="playground-target-wrapper" style="width:100%; display:flex; justify-content:center;">
                                    <x-core::button
                                        id="demo-btn"
                                        variant="solid"
                                        color="gold"
                                        size="md"
                                        icon="plus"
                                    >
                                        Action Button
                                    </x-core::button>
                                </div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-btn-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-btn-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Variant</label>
                                        <select id="btn-ctrl-variant">
                                            <option value="solid" selected>Solid (Default)</option>
                                            <option value="outline">Outline</option>
                                            <option value="soft">Soft / Subtle</option>
                                            <option value="ghost">Ghost</option>
                                            <option value="link">Link</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Theme</label>
                                        <select id="btn-ctrl-color">
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

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Size</label>
                                        <select id="btn-ctrl-size">
                                            <option value="xs">XS (26px)</option>
                                            <option value="sm">SM (32px)</option>
                                            <option value="md" selected>MD (38px)</option>
                                            <option value="lg">LG (46px)</option>
                                            <option value="xl">XL (52px)</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Corner Radius</label>
                                        <select id="btn-ctrl-rounded">
                                            <option value="default" selected>Default (10px)</option>
                                            <option value="pill">Pill / Full (999px)</option>
                                            <option value="none">Square (0px)</option>
                                            <option value="sm">Small (6px)</option>
                                            <option value="lg">Large (14px)</option>
                                            <option value="xl">Extra Large (18px)</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Left Icon</label>
                                        <select id="btn-ctrl-icon">
                                            <option value="plus" selected>plus</option>
                                            <option value="save">save</option>
                                            <option value="edit">edit</option>
                                            <option value="trash">trash</option>
                                            <option value="check">check</option>
                                            <option value="search">search</option>
                                            <option value="download">download</option>
                                            <option value="shopping-cart">shopping-cart</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Right Icon</label>
                                        <select id="btn-ctrl-icon-right">
                                            <option value="none" selected>None</option>
                                            <option value="arrow-right">arrow-right</option>
                                            <option value="chevron-right">chevron-right</option>
                                            <option value="external-link">external-link</option>
                                            <option value="check">check</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Button Text</label>
                                    <input type="text" id="btn-ctrl-text" value="Action Button" placeholder="Button text...">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="btn-ctrl-loading"> Loading State
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="btn-ctrl-disabled"> Disabled
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="btn-ctrl-block"> Block / Full Width
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="btn-ctrl-icon-only"> Icon Only
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 2: ICONS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-icons">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="tag" size="18" style="color:var(--teal-800);" />
                            <span>অন্তর্নির্মিত SVG আইকন ক্যাটালগ (50+ Built-in SVG Icons)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="margin-bottom:14px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                            <p style="margin:0; font-size:13px; color:var(--ink-600);">
                                যেকোনো আইকনে ক্লিক করে সরাসরি Blade কোড কপি করুন অথবা প্লে-গ্রাউন্ডে টেস্ট করুন।
                            </p>
                            <span style="font-size:12px; font-weight:700; color:var(--teal-800); background:var(--teal-100); padding:3px 10px; border-radius:99px;">
                                &lt;x-core::icon name="..." /&gt;
                            </span>
                        </div>

                        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(110px, 1fr)); gap:10px;" id="icons-grid">
                            @php
                                $allIcons = [
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

                            @foreach ($allIcons as $ic)
                                <div
                                    class="icon-grid-cell"
                                    style="background:var(--paper); border:1px solid var(--border); border-radius:10px; padding:12px 8px; display:flex; flex-direction:column; align-items:center; gap:8px; cursor:pointer; transition:all .15s ease;"
                                    onclick="copyIconBlade('{{ $ic }}')"
                                    title="Click to copy icon: {{ $ic }}"
                                >
                                    <x-core::icon :name="$ic" size="20" style="color:var(--teal-800);" />
                                    <span style="font-size:10.5px; font-weight:700; color:var(--ink-700); text-align:center; word-break:break-all;">{{ $ic }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 3: INPUTS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-inputs">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="edit" size="18" style="color:var(--teal-800);" />
                            <span>ইন্টারেক্টিভ ইনপুট প্লে-গ্রাউন্ড (Input Interactive Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="input-preview-target" style="width:100%; max-width:380px;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-inp-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-inp-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Input Type</label>
                                        <select id="inp-ctrl-type">
                                            <option value="text" selected>text</option>
                                            <option value="password">password</option>
                                            <option value="number">number</option>
                                            <option value="email">email</option>
                                            <option value="search">search</option>
                                            <option value="date">date</option>
                                            <option value="tel">tel</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Size</label>
                                        <select id="inp-ctrl-size">
                                            <option value="xs">XS (28px)</option>
                                            <option value="sm">SM (32px)</option>
                                            <option value="md" selected>MD (38px - Default)</option>
                                            <option value="lg">LG (46px)</option>
                                            <option value="xl">XL (52px)</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Variant</label>
                                        <select id="inp-ctrl-variant">
                                            <option value="outline" selected>Outline (Default)</option>
                                            <option value="filled">Filled / Soft</option>
                                            <option value="flushed">Flushed (Underlined)</option>
                                            <option value="unstyled">Unstyled</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Palette</label>
                                        <select id="inp-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="green">Green / Success</option>
                                            <option value="red">Red / Danger</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="dark">Dark / Ink</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Corner Radius</label>
                                        <select id="inp-ctrl-rounded">
                                            <option value="default" selected>Default (10px)</option>
                                            <option value="pill">Pill (999px)</option>
                                            <option value="none">Square (0px)</option>
                                            <option value="sm">Small (6px)</option>
                                            <option value="lg">Large (14px)</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Left Icon</label>
                                        <select id="inp-ctrl-icon">
                                            <option value="user" selected>user</option>
                                            <option value="mail">mail</option>
                                            <option value="phone">phone</option>
                                            <option value="lock">lock</option>
                                            <option value="search">search</option>
                                            <option value="barcode">barcode</option>
                                            <option value="calendar">calendar</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Prefix Addon</label>
                                        <input type="text" id="inp-ctrl-prefix" placeholder="e.g. ৳ or https://">
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Suffix Addon</label>
                                        <input type="text" id="inp-ctrl-suffix" placeholder="e.g. .00 or kg">
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Label Text</label>
                                    <input type="text" id="inp-ctrl-label" value="গ্রাহকের নাম" placeholder="Field label...">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="inp-ctrl-required"> Required (*)
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="inp-ctrl-clearable"> Clearable
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="inp-ctrl-pwd-toggle"> Password Eye Toggle
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="inp-ctrl-loading"> Loading Spinner
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="inp-ctrl-disabled"> Disabled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 4: SELECTS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-selects">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="chevron-down" size="18" style="color:var(--teal-800);" />
                            <span>সিলেক্ট ড্রপডাউন প্লে-গ্রাউন্ড (Select Interactive Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="select-preview-target" style="width:100%; max-width:380px;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-sel-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-sel-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Size</label>
                                        <select id="sel-ctrl-size">
                                            <option value="xs">XS (28px)</option>
                                            <option value="sm">SM (32px)</option>
                                            <option value="md" selected>MD (38px)</option>
                                            <option value="lg">LG (46px)</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Variant</label>
                                        <select id="sel-ctrl-variant">
                                            <option value="outline" selected>Outline</option>
                                            <option value="filled">Filled / Soft</option>
                                            <option value="flushed">Flushed</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Palette</label>
                                        <select id="sel-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="green">Green / Success</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Left Icon</label>
                                        <select id="sel-ctrl-icon">
                                            <option value="credit-card" selected>credit-card</option>
                                            <option value="user">user</option>
                                            <option value="filter">filter</option>
                                            <option value="tag">tag</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Label</label>
                                    <input type="text" id="sel-ctrl-label" value="পেমেন্ট মেথড">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="sel-ctrl-rounded-pill"> Pill Shape (999px)
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="sel-ctrl-disabled"> Disabled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 5: TEXTAREAS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-textareas">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="mail" size="18" style="color:var(--teal-800);" />
                            <span>টেক্সট-এরিয়া প্লে-গ্রাউন্ড (Textarea Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="textarea-preview-target" style="width:100%; max-width:380px;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-txt-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-txt-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Rows</label>
                                        <select id="txt-ctrl-rows">
                                            <option value="2">2 Rows</option>
                                            <option value="3" selected>3 Rows</option>
                                            <option value="5">5 Rows</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Resize</label>
                                        <select id="txt-ctrl-resize">
                                            <option value="vertical" selected>Vertical</option>
                                            <option value="none">None (Fixed)</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Variant</label>
                                        <select id="txt-ctrl-variant">
                                            <option value="outline" selected>Outline</option>
                                            <option value="filled">Filled / Soft</option>
                                            <option value="flushed">Flushed</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Color</label>
                                        <select id="txt-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="blue">Blue / Info</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Label</label>
                                    <input type="text" id="txt-ctrl-label" value="ঠিকানা ও শিপিং নোট">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="txt-ctrl-show-count" checked> Show Character Count
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="txt-ctrl-disabled"> Disabled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 6: CHECKBOXES --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-checkboxes">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="check" size="18" style="color:var(--teal-800);" />
                            <span>কাস্টম চেকবক্স প্লে-গ্রাউন্ড (Checkbox Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="chk-preview-target" style="width:100%; display:flex; justify-content:center;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-chk-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-chk-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Palette</label>
                                        <select id="chk-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="green">Green / Success</option>
                                            <option value="red">Red / Danger</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="dark">Dark / Ink</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Size</label>
                                        <select id="chk-ctrl-size">
                                            <option value="sm">SM (15px)</option>
                                            <option value="md" selected>MD (18px - Default)</option>
                                            <option value="lg">LG (22px)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Label</label>
                                    <input type="text" id="chk-ctrl-label" value="শর্তাবলী মেনে নিচ্ছি">
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Description</label>
                                    <input type="text" id="chk-ctrl-desc" value="ব্যবহারকারীর নিয়মাবলী এবং গোপনীয়তা নীতি">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="chk-ctrl-checked" checked> Checked State
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="chk-ctrl-disabled"> Disabled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 7: TOGGLES --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-toggles">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="sun" size="18" style="color:var(--teal-800);" />
                            <span>সুইচ ও টগল প্লে-গ্রাউন্ড (Toggle Switch Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="tog-preview-target" style="width:100%; display:flex; justify-content:center;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-tog-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-tog-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Color</label>
                                        <select id="tog-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="green">Green / Success</option>
                                            <option value="red">Red / Danger</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Size</label>
                                        <select id="tog-ctrl-size">
                                            <option value="sm">SM (32px)</option>
                                            <option value="md" selected>MD (42px - Default)</option>
                                            <option value="lg">LG (50px)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Label</label>
                                    <input type="text" id="tog-ctrl-label" value="ডার্ক মোড সক্রিয় করুন">
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Description</label>
                                    <input type="text" id="tog-ctrl-desc" value="আইকন সহ স্মুথ থিম টগল">
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tog-ctrl-checked" checked> Checked State
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tog-ctrl-disabled"> Disabled
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 8: RADIOS --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-radios">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="credit-card" size="18" style="color:var(--teal-800);" />
                            <span>রেডিও বাটন ও সিলেকশন কার্ড প্লে-গ্রাউন্ড (Radio Card Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.1fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            <div class="sg-preview-box">
                                <div id="card-preview-target" style="width:100%; max-width:360px;"></div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-rc-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-rc-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Theme</label>
                                        <select id="rc-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="green">Green / Success</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Icon</label>
                                        <select id="rc-ctrl-icon">
                                            <option value="cash" selected>cash</option>
                                            <option value="credit-card">credit-card</option>
                                            <option value="phone">phone</option>
                                            <option value="user">user</option>
                                            <option value="tag">tag</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Card Title</label>
                                    <input type="text" id="rc-ctrl-title" value="ক্যাশ পেমেন্ট">
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Description</label>
                                    <input type="text" id="rc-ctrl-desc" value="সরাসরি ক্যাশ বা ড্রয়ার পেমেন্ট গ্রহণ করুন">
                                </div>

                                <div class="field" style="margin-top:0;">
                                    <label>Badge Label</label>
                                    <input type="text" id="rc-ctrl-badge" value="Instant">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 9: TABLES & DATATABLES --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-tables">
                {{-- Interactive Table Playground --}}
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head" style="background:var(--paper); border-radius:var(--radius) var(--radius) 0 0;">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="box" size="18" style="color:var(--teal-800);" />
                            <span>ইন্টারেক্টিভ টেবিল প্লে-গ্রাউন্ড (Table & DataTables Playground)</span>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div style="display:grid; grid-template-columns:1.2fr 1fr; gap:20px; align-items:start;" class="playground-grid">
                            {{-- Live Preview Box --}}
                            <div class="sg-preview-box" style="padding:16px;">
                                <div id="table-preview-target" style="width:100%;">
                                    {{-- Rendered via JS --}}
                                </div>
                                <div class="sg-code-wrapper">
                                    <div class="sg-code-head">
                                        <span>Blade Code Snippet:</span>
                                        <x-core::button id="copy-tbl-code" variant="ghost" color="dark" size="xs" icon="copy">Copy</x-core::button>
                                    </div>
                                    <pre id="demo-tbl-code" class="sg-code-box"></pre>
                                </div>
                            </div>

                            {{-- Table Controls --}}
                            <div style="display:flex; flex-direction:column; gap:12px;">
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Variant</label>
                                        <select id="tbl-ctrl-variant">
                                            <option value="card" selected>Card / Panel</option>
                                            <option value="striped">Striped Rows</option>
                                            <option value="bordered">Bordered Cells</option>
                                            <option value="borderless">Borderless</option>
                                            <option value="flush">Flush / Clean</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Size / Density</label>
                                        <select id="tbl-ctrl-size">
                                            <option value="xs">XS / Compact (POS 32px)</option>
                                            <option value="sm">SM (38px)</option>
                                            <option value="md" selected>MD (46px - Default)</option>
                                            <option value="lg">LG (54px)</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <div class="field" style="margin-top:0;">
                                        <label>Color Accent</label>
                                        <select id="tbl-ctrl-color">
                                            <option value="teal" selected>Teal / Brand</option>
                                            <option value="gold">Gold / Primary</option>
                                            <option value="dark">Dark / Ink</option>
                                            <option value="blue">Blue / Info</option>
                                            <option value="green">Green / Success</option>
                                        </select>
                                    </div>
                                    <div class="field" style="margin-top:0;">
                                        <label>Table Title</label>
                                        <input type="text" id="tbl-ctrl-title" value="সাম্প্রতিক বিক্রয় তালিকা">
                                    </div>
                                </div>

                                <div style="display:flex; flex-wrap:wrap; gap:12px;">
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tbl-ctrl-searchable" checked> Search Bar
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tbl-ctrl-checkbox" checked> Bulk Checkboxes
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tbl-ctrl-sticky"> Sticky Header
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tbl-ctrl-pagination" checked> Pagination Bar
                                    </label>
                                    <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                        <input type="checkbox" id="tbl-ctrl-empty"> Empty State
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Full Practical Examples --}}
                <div style="display:grid; grid-template-columns:1fr; gap:20px;">
                    {{-- 1. Yajra / Server-side DataTable Ready Component --}}
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <x-core::icon name="settings" size="18" style="color:var(--teal-800);" />
                                <span>১. Yajra DataTable সার্ভার-সাইড ইন্টিগ্রেশন (Yajra & AJAX DataTables)</span>
                            </div>
                        </div>
                        <div class="panel-body">
                            <p style="font-size:13px; color:var(--ink-600); margin-top:0; margin-bottom:14px;">
                                <code>&lt;x-core::table id="orders-table" datatable /&gt;</code> দিয়ে খুব সহজেই Yajra Server-side AJAX ডেটা-টেবিল লোড করুন।
                            </p>

                            <x-core::table
                                id="yajra-demo-table"
                                title="গ্রাহক অর্ডার ডেটা-টেবিল (Yajra Server-side Ready)"
                                subtitle="স্বয়ংক্রিয় পেজিনেশন, ফিল্টারিং ও এক্সপোর্ট বাটন সহ"
                                searchable
                            >
                                <x-slot:actions>
                                    <x-core::button size="sm" variant="outline" color="dark" icon="download">Export Excel</x-core::button>
                                    <x-core::button size="sm" variant="solid" color="teal" icon="plus">নতুন ইনভয়েস</x-core::button>
                                </x-slot:actions>

                                <x-slot:header>
                                    <x-core::table.th checkbox />
                                    <x-core::table.th sortable direction="desc" icon="tag">ইনভয়েস #</x-core::table.th>
                                    <x-core::table.th sortable icon="user">গ্রাহক</x-core::table.th>
                                    <x-core::table.th icon="phone">ফোন</x-core::table.th>
                                    <x-core::table.th align="center">স্ট্যাটাস</x-core::table.th>
                                    <x-core::table.th align="right" sortable icon="cash">মোট মূল্য</x-core::table.th>
                                    <x-core::table.th align="right">অ্যাকশন</x-core::table.th>
                                </x-slot:header>

                                <x-core::table.tr>
                                    <x-core::table.td checkbox value="INV-9021" />
                                    <x-core::table.td bold><a href="#" style="color:var(--teal-800); text-decoration:none; font-weight:700;">#INV-9021</a></x-core::table.td>
                                    <x-core::table.td>তানভীর হাসান</x-core::table.td>
                                    <x-core::table.td muted>01712-345678</x-core::table.td>
                                    <x-core::table.td align="center"><span style="background:var(--green-100); color:var(--green-600); font-weight:700; font-size:11px; padding:3px 8px; border-radius:99px;">পরিশোধিত (Paid)</span></x-core::table.td>
                                    <x-core::table.td align="right" bold>৳ ১২,৫০০.০০</x-core::table.td>
                                    <x-core::table.td actions>
                                        <x-core::button size="xs" variant="ghost" color="dark" icon="printer" icon-only title="Print Invoice" />
                                        <x-core::button size="xs" variant="soft" color="teal" icon="edit" icon-only title="Edit" />
                                        <x-core::button size="xs" variant="soft" color="red" icon="trash" icon-only title="Delete" />
                                    </x-core::table.td>
                                </x-core::table.tr>

                                <x-core::table.tr>
                                    <x-core::table.td checkbox value="INV-9022" />
                                    <x-core::table.td bold><a href="#" style="color:var(--teal-800); text-decoration:none; font-weight:700;">#INV-9022</a></x-core::table.td>
                                    <x-core::table.td>মাহমুদুল করিম</x-core::table.td>
                                    <x-core::table.td muted>01823-998877</x-core::table.td>
                                    <x-core::table.td align="center"><span style="background:var(--gold-100); color:var(--gold-600); font-weight:700; font-size:11px; padding:3px 8px; border-radius:99px;">বাকি (Due)</span></x-core::table.td>
                                    <x-core::table.td align="right" bold>৳ ৩,২০০.০০</x-core::table.td>
                                    <x-core::table.td actions>
                                        <x-core::button size="xs" variant="ghost" color="dark" icon="printer" icon-only title="Print Invoice" />
                                        <x-core::button size="xs" variant="soft" color="teal" icon="edit" icon-only title="Edit" />
                                        <x-core::button size="xs" variant="soft" color="red" icon="trash" icon-only title="Delete" />
                                    </x-core::table.td>
                                </x-core::table.tr>

                                <x-slot:pagination-info>
                                    মোট ৫০ টির মধ্যে ১ থেকে ২ টি দেখানো হচ্ছে
                                </x-slot:pagination-info>

                                <x-slot:pagination>
                                    <a class="table-page-btn disabled">&laquo;</a>
                                    <a class="table-page-btn active">১</a>
                                    <a class="table-page-btn">২</a>
                                    <a class="table-page-btn">৩</a>
                                    <a class="table-page-btn">&raquo;</a>
                                </x-slot:pagination>
                            </x-core::table>
                        </div>
                    </div>

                    {{-- 2. Compact POS Cart Table --}}
                    <div class="panel" style="margin-top:0;">
                        <div class="panel-head">
                            <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                                <x-core::icon name="shopping-cart" size="18" style="color:var(--teal-800);" />
                                <span>২. পিওএস কমপ্যাক্ট টেবিল (POS Compact Cart Table - XS Density)</span>
                            </div>
                        </div>
                        <div class="panel-body">
                            <x-core::table size="xs" variant="striped" color="gold">
                                <x-slot:header>
                                    <x-core::table.th width="45%">পণ্য বিবরণ</x-core::table.th>
                                    <x-core::table.th align="center" width="15%">পরিমাণ</x-core::table.th>
                                    <x-core::table.th align="right" width="20%">একক মূল্য</x-core::table.th>
                                    <x-core::table.th align="right" width="20%">মোট</x-core::table.th>
                                </x-slot:header>

                                <x-core::table.tr>
                                    <x-core::table.td bold>স্যামসাং গ্যালাক্সি A15 (8/128GB)<div style="font-size:10px; color:var(--ink-500);">IMEI: 864201928472910 · Warranty 1 Yr</div></x-core::table.td>
                                    <x-core::table.td align="center"><span style="background:var(--paper); padding:2px 8px; border-radius:6px; font-weight:700; border:1px solid var(--border);">১ টি</span></x-core::table.td>
                                    <x-core::table.td align="right">৳ ১৮,৫০০</x-core::table.td>
                                    <x-core::table.td align="right" bold style="color:var(--teal-800);">৳ ১৮,৫০০</x-core::table.td>
                                </x-core::table.tr>

                                <x-core::table.tr>
                                    <x-core::table.td bold>ফাস্ট চার্জার ৩৩ ওয়াট টাইপ-সি<div style="font-size:10px; color:var(--ink-500);">SKU: ACC-CHG-33W · Original</div></x-core::table.td>
                                    <x-core::table.td align="center"><span style="background:var(--paper); padding:2px 8px; border-radius:6px; font-weight:700; border:1px solid var(--border);">২ টি</span></x-core::table.td>
                                    <x-core::table.td align="right">৳ ৮৫০</x-core::table.td>
                                    <x-core::table.td align="right" bold style="color:var(--teal-800);">৳ ১,৭০০</x-core::table.td>
                                </x-core::table.tr>
                            </x-core::table>
                        </div>
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 10: VALIDATION & FEEDBACK --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-validation">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="alert-triangle" size="18" style="color:var(--red-600);" />
                            <span>ফর্ম ভ্যালিডেশন ও হেল্পার মেসেজ (Validation & Feedback)</span>
                        </div>
                    </div>
                    <div class="panel-body" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:18px;">
                        <x-core::input
                            label="ভ্যালিডেশন এরর স্টেট"
                            value="invalid.email@"
                            error="অনুগ্রহ করে একটি সঠিক ইমেইল অ্যাড্রেস লিখুন"
                            icon="mail"
                            no-margin
                        />
                        <x-core::input
                            label="সফল ভ্যালিডেশন স্টেট"
                            value="john.doe@company.com"
                            class="is-valid"
                            icon="check-circle"
                            helper="ইমেইলটি সঠিক রয়েছে ও অনুমোদিত"
                            helper-variant="success"
                            no-margin
                        />
                        <x-core::input
                            label="তথ্যমূলক হেল্পার টেক্সট"
                            placeholder="Enter password..."
                            type="password"
                            icon="lock"
                            helper="পাসওয়ার্ডে কমপক্ষে ৮টি অক্ষর থাকা আবশ্যক"
                            helper-variant="info"
                            no-margin
                        />
                    </div>
                </div>
            </div>


            {{-- ========================================================================= --}}
            {{-- PANE 11: THEMING & SCOPES --}}
            {{-- ========================================================================= --}}
            <div class="sg-pane" id="pane-theming">
                <div class="panel" style="margin-top:0;">
                    <div class="panel-head">
                        <div class="panel-title" style="display:flex; align-items:center; gap:8px;">
                            <x-core::icon name="settings" size="18" style="color:var(--teal-800);" />
                            <span>সিএসএস ভ্যারিয়েবল স্কোপড থিমিং (Layout Scoped CSS Variables)</span>
                        </div>
                    </div>
                    <div class="panel-body" style="display:flex; flex-direction:column; gap:16px;">
                        <p style="color:var(--ink-600); font-size:13px; margin:0;">
                            বাটন, ইনপুট, চেকবক্স এবং টগল উপাদানগুলো যেকোনো পেরেন্ট কন্টেইনার বা মডালের CSS ভ্যারিয়েবল ওভাররাইড স্বয়ংক্রিয়ভাবে গ্রহণ করে।
                        </p>

                        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;" class="mini-grid">
                            {{-- Purple Scoped --}}
                            <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; --teal-800:#7C3AED; --teal-700:#6D28D9; --teal-100:#EDE9FE; --gold-500:#8B5CF6; --form-focus-border:#7C3AED; --form-focus-ring:0 0 0 3px rgba(124,58,237,.18); --check-active-bg:#7C3AED; --check-active-border:#7C3AED; --switch-active-bg:#7C3AED;">
                                <div style="font-weight:700; font-size:12px; color:#7C3AED; margin-bottom:12px;">🟣 Purple Layout Scope</div>
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <x-core::button color="teal" size="sm" icon="check">Purple Button</x-core::button>
                                    <x-core::input placeholder="Purple input focus..." icon="search" size="sm" no-margin />
                                    <x-core::toggle label="Purple Switch" checked size="sm" />
                                    <x-core::checkbox label="Purple Checkbox" checked size="sm" />
                                </div>
                            </div>

                            {{-- Indigo Scoped --}}
                            <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; --teal-800:#4F46E5; --teal-700:#4338CA; --teal-100:#EEF2FF; --gold-500:#6366F1; --form-focus-border:#4F46E5; --form-focus-ring:0 0 0 3px rgba(79,70,229,.18); --check-active-bg:#4F46E5; --check-active-border:#4F46E5; --switch-active-bg:#4F46E5;">
                                <div style="font-weight:700; font-size:12px; color:#4F46E5; margin-bottom:12px;">🔵 Indigo Layout Scope</div>
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <x-core::button color="teal" size="sm" icon="shopping-cart">Indigo Button</x-core::button>
                                    <x-core::input placeholder="Indigo input focus..." icon="mail" size="sm" no-margin />
                                    <x-core::toggle label="Indigo Switch" checked size="sm" />
                                    <x-core::checkbox label="Indigo Checkbox" checked size="sm" />
                                </div>
                            </div>

                            {{-- Emerald Scoped --}}
                            <div style="background:var(--card); border:1px solid var(--border); border-radius:12px; padding:18px; --teal-800:#059669; --teal-700:#047857; --teal-100:#D1FAE5; --gold-500:#10B981; --form-focus-border:#059669; --form-focus-ring:0 0 0 3px rgba(5,150,105,.18); --check-active-bg:#059669; --check-active-border:#059669; --switch-active-bg:#059669;">
                                <div style="font-weight:700; font-size:12px; color:#059669; margin-bottom:12px;">🟢 Emerald Layout Scope</div>
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <x-core::button color="teal" size="sm" icon="save">Emerald Button</x-core::button>
                                    <x-core::input placeholder="Emerald input focus..." icon="check" size="sm" no-margin />
                                    <x-core::toggle label="Emerald Switch" checked size="sm" />
                                    <x-core::checkbox label="Emerald Checkbox" checked size="sm" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    {{-- Interactive Styleguide JS Logic --}}
    <script>
        (function () {
            // Icon dictionary
            const iconDict = {
                'plus': '<path d="M12 5v14M5 12h14"/>',
                'minus': '<line x1="5" y1="12" x2="19" y2="12"/>',
                'save': '<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>',
                'edit': '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
                'trash': '<path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13"/>',
                'check': '<polyline points="20 6 9 17 4 12"/>',
                'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
                'x': '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
                'x-circle': '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
                'search': '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
                'filter': '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
                'download': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/>',
                'upload': '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
                'refresh': '<path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/>',
                'eye': '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
                'eye-off': '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" y1="2" x2="22" y2="22"/>',
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
                'users': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                'shopping-cart': '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
                'cash': '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
                'credit-card': '<rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>',
                'calendar': '<rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/>',
                'clock': '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                'tag': '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r=".5" fill="currentColor"/>',
                'box': '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" x2="12" y1="22.08" y2="12"/>',
                'barcode': '<path d="M3 5v14M8 5v14M12 5v14M17 5v14M21 5v14"/>',
                'percent': '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
                'phone': '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
                'mail': '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
                'settings': '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
                'lock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
                'unlock': '<rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/>',
                'info': '<circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>',
                'alert-triangle': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>',
                'logout': '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
                'bell': '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>',
                'moon': '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
                'sun': '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>'
            };

            function getSvg(name) {
                if (!iconDict[name]) return '';
                return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="app-icon">' + iconDict[name] + '</svg>';
            }

            window.copyIconBlade = function (name) {
                const code = '<' + 'x-core::icon name="' + name + '" />';
                navigator.clipboard.writeText(code).then(function () {
                    if (typeof window.toast === 'function') window.toast('আইকন কোড কপি হয়েছে: ' + name, 'Copied icon: ' + name);
                });
            };

            function initStyleguide($) {
                // Tab Navigation
                $('.sg-nav-link').on('click', function (e) {
                    const targetId = $(this).data('target');
                    if (!targetId) return;
                    e.preventDefault();

                    $('.sg-nav-link').removeClass('active');
                    $(this).addClass('active');

                    $('.sg-pane').removeClass('active');
                    $('#' + targetId).addClass('active');

                    const hash = $(this).attr('href');
                    if (history.pushState) {
                        history.pushState(null, null, hash);
                    } else {
                        location.hash = hash;
                    }
                });

                // Deep linking on load
                if (window.location.hash) {
                    const hash = window.location.hash;
                    const $matchedLink = $('.sg-nav-link[href="' + hash + '"]');
                    if ($matchedLink.length) {
                        $matchedLink.trigger('click');
                    }
                }

                // Filter Sidebar Items
                $('#sg-search-input').on('input', function () {
                    const query = $(this).val().toLowerCase().trim();
                    $('.sg-nav-link').each(function () {
                        const text = $(this).text().toLowerCase();
                        $(this).toggle(text.indexOf(query) !== -1);
                    });
                });

                // 1. Button Playground Updater
                function updateBtnPlayground() {
                    const variant = $('#btn-ctrl-variant').val() || 'solid';
                    const color = $('#btn-ctrl-color').val() || 'gold';
                    const size = $('#btn-ctrl-size').val() || 'md';
                    const rounded = $('#btn-ctrl-rounded').val() || 'default';
                    const icon = $('#btn-ctrl-icon').val() || 'none';
                    const iconRight = $('#btn-ctrl-icon-right').val() || 'none';
                    const text = $('#btn-ctrl-text').val() || '';
                    const isLoading = $('#btn-ctrl-loading').is(':checked');
                    const isDisabled = $('#btn-ctrl-disabled').is(':checked');
                    const isBlock = $('#btn-ctrl-block').is(':checked');
                    const isIconOnly = $('#btn-ctrl-icon-only').is(':checked');

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

                    let inner = '';
                    if (isLoading) {
                        inner += '<span class="btn-spinner"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" stroke-dasharray="32" stroke-linecap="round" fill="none" opacity="0.25"/><path d="M12 3a9 9 0 0 1 9 9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg></span>';
                    } else if (icon !== 'none' && iconDict[icon]) {
                        inner += '<span class="btn-icon">' + getSvg(icon) + '</span>';
                    }
                    if (!isIconOnly && text) {
                        inner += '<span class="btn-text">' + $('<div>').text(text).html() + '</span>';
                    }
                    if (!isLoading && iconRight !== 'none' && iconDict[iconRight]) {
                        inner += '<span class="btn-icon btn-icon-right">' + getSvg(iconRight) + '</span>';
                    }
                    $btn.html(inner);

                    let snippet = '<' + 'x-core::button';
                    if (variant !== 'solid') snippet += ' variant="' + variant + '"';
                    if (color !== 'gold') snippet += ' color="' + color + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    if (rounded !== 'default') snippet += ' rounded="' + rounded + '"';
                    if (icon !== 'none') snippet += ' icon="' + icon + '"';
                    if (iconRight !== 'none') snippet += ' icon-right="' + iconRight + '"';
                    if (isLoading) snippet += ' loading';
                    if (isDisabled) snippet += ' disabled';
                    if (isBlock) snippet += ' block';
                    if (isIconOnly) {
                        snippet += ' icon-only title="' + (text || 'Action') + '" />';
                    } else {
                        snippet += '>\n    ' + (text || 'Click me') + '\n<' + '/x-core::button>';
                    }
                    $('#demo-btn-code').text(snippet);
                }

                $('#btn-ctrl-variant, #btn-ctrl-color, #btn-ctrl-size, #btn-ctrl-rounded, #btn-ctrl-icon, #btn-ctrl-icon-right, #btn-ctrl-loading, #btn-ctrl-disabled, #btn-ctrl-block, #btn-ctrl-icon-only').on('change', updateBtnPlayground);
                $('#btn-ctrl-text').on('input', updateBtnPlayground);
                $('#copy-btn-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-btn-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('বাটন কোড কপি হয়েছে!', 'Button code copied!');
                    });
                });

                // 2. Input Playground Updater (DOM preview + Snippet)
                function updateInpPlayground() {
                    const type = $('#inp-ctrl-type').val() || 'text';
                    const size = $('#inp-ctrl-size').val() || 'md';
                    const variant = $('#inp-ctrl-variant').val() || 'outline';
                    const color = $('#inp-ctrl-color').val() || 'teal';
                    const rounded = $('#inp-ctrl-rounded').val() || 'default';
                    const icon = $('#inp-ctrl-icon').val() || 'none';
                    const prefix = $('#inp-ctrl-prefix').val() || '';
                    const suffix = $('#inp-ctrl-suffix').val() || '';
                    const label = $('#inp-ctrl-label').val() || '';
                    const isRequired = $('#inp-ctrl-required').is(':checked');
                    const isClearable = $('#inp-ctrl-clearable').is(':checked');
                    const isPwdToggle = $('#inp-ctrl-pwd-toggle').is(':checked');
                    const isLoading = $('#inp-ctrl-loading').is(':checked');
                    const isDisabled = $('#inp-ctrl-disabled').is(':checked');

                    let groupClasses = ['form-input-group'];
                    if (icon !== 'none') groupClasses.push('has-icon-left');
                    if (prefix) groupClasses.push('has-addon-left');
                    if (suffix) groupClasses.push('has-addon-right');
                    if (isClearable || isPwdToggle || isLoading) groupClasses.push('has-icon-right');
                    if (size !== 'md') groupClasses.push('form-input-group-' + size);
                    if (rounded === 'pill') groupClasses.push('form-rounded-pill');

                    let controlClasses = ['form-control'];
                    if (variant === 'filled') controlClasses.push('form-control-filled');
                    else if (variant === 'flushed') controlClasses.push('form-control-flushed');
                    else if (variant === 'unstyled') controlClasses.push('form-control-unstyled');
                    else controlClasses.push('form-control-outline');

                    controlClasses.push('form-control-' + size);
                    if (color) controlClasses.push('form-' + color);
                    if (rounded === 'pill') controlClasses.push('form-rounded-pill');
                    else if (rounded === 'none') controlClasses.push('form-rounded-none');
                    else if (rounded !== 'default') controlClasses.push('form-rounded-' + rounded);

                    let html = '';
                    if (label) {
                        html += '<label class="form-label form-label-' + size + '">';
                        if (icon !== 'none') html += '<span class="form-label-icon">' + getSvg(icon) + '</span>';
                        html += $('<div>').text(label).html();
                        if (isRequired) html += '<span class="form-required">*</span>';
                        html += '</label>';
                    }

                    html += '<div class="' + groupClasses.join(' ') + '">';
                    if (prefix) {
                        html += '<span class="form-input-addon form-input-addon-left">' + $('<div>').text(prefix).html() + '</span>';
                    }
                    if (icon !== 'none' && iconDict[icon]) {
                        html += '<span class="form-input-icon form-input-icon-left">' + getSvg(icon) + '</span>';
                    }

                    html += '<input type="' + type + '" class="' + controlClasses.join(' ') + '" placeholder="এখানে লিখুন..."';
                    if (isDisabled) html += ' disabled';
                    if (isRequired) html += ' required';
                    html += ' />';

                    if (isClearable) {
                        html += '<button type="button" class="form-input-btn form-input-clear" title="Clear input" onclick="$(this).siblings(\'input\').val(\'\').focus();">' + getSvg('x') + '</button>';
                    } else if (isPwdToggle) {
                        html += '<button type="button" class="form-input-btn form-input-pwd-toggle" title="Toggle password visibility" onclick="const inp=$(this).siblings(\'input\'); const isPwd=inp.attr(\'type\')===\'password\'; inp.attr(\'type\', isPwd?\'text\':\'password\');">' + getSvg('eye') + '</button>';
                    } else if (isLoading) {
                        html += '<span class="form-input-icon form-input-icon-right form-input-spinner">' + getSvg('refresh') + '</span>';
                    }

                    if (suffix) {
                        html += '<span class="form-input-addon form-input-addon-right">' + $('<div>').text(suffix).html() + '</span>';
                    }
                    html += '</div>';

                    $('#input-preview-target').html(html);

                    let snippet = '<' + 'x-core::input';
                    if (label) snippet += ' label="' + label + '"';
                    if (type !== 'text') snippet += ' type="' + type + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    if (variant !== 'outline') snippet += ' variant="' + variant + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (rounded !== 'default') snippet += ' rounded="' + rounded + '"';
                    if (icon !== 'none') snippet += ' icon="' + icon + '"';
                    if (prefix) snippet += ' prefix="' + prefix + '"';
                    if (suffix) snippet += ' suffix="' + suffix + '"';
                    if (isRequired) snippet += ' required';
                    if (isClearable) snippet += ' clearable';
                    if (isPwdToggle) snippet += ' password-toggle';
                    if (isLoading) snippet += ' loading';
                    if (isDisabled) snippet += ' disabled';
                    snippet += ' placeholder="এখানে লিখুন..." />';

                    $('#demo-inp-code').text(snippet);
                }

                $('#inp-ctrl-type, #inp-ctrl-size, #inp-ctrl-variant, #inp-ctrl-color, #inp-ctrl-rounded, #inp-ctrl-icon, #inp-ctrl-required, #inp-ctrl-clearable, #inp-ctrl-pwd-toggle, #inp-ctrl-loading, #inp-ctrl-disabled').on('change', updateInpPlayground);
                $('#inp-ctrl-prefix, #inp-ctrl-suffix, #inp-ctrl-label').on('input', updateInpPlayground);
                $('#copy-inp-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-inp-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('ইনপুট কোড কপি হয়েছে!', 'Input code copied!');
                    });
                });

                // 3. Select Playground Updater
                function updateSelPlayground() {
                    const size = $('#sel-ctrl-size').val() || 'md';
                    const variant = $('#sel-ctrl-variant').val() || 'outline';
                    const color = $('#sel-ctrl-color').val() || 'teal';
                    const icon = $('#sel-ctrl-icon').val() || 'none';
                    const label = $('#sel-ctrl-label').val() || 'পেমেন্ট মেথড';
                    const isPill = $('#sel-ctrl-rounded-pill').is(':checked');
                    const isDisabled = $('#sel-ctrl-disabled').is(':checked');

                    let groupClasses = ['form-input-group'];
                    if (icon !== 'none') groupClasses.push('has-icon-left');
                    if (size !== 'md') groupClasses.push('form-input-group-' + size);
                    if (isPill) groupClasses.push('form-rounded-pill');

                    let controlClasses = ['form-control', 'form-select'];
                    if (variant === 'filled') controlClasses.push('form-control-filled');
                    else if (variant === 'flushed') controlClasses.push('form-control-flushed');
                    else controlClasses.push('form-control-outline');
                    controlClasses.push('form-control-' + size);
                    controlClasses.push('form-' + color);
                    if (isPill) controlClasses.push('form-rounded-pill');

                    let html = '';
                    if (label) {
                        html += '<label class="form-label form-label-' + size + '">';
                        if (icon !== 'none') html += '<span class="form-label-icon">' + getSvg(icon) + '</span>';
                        html += $('<div>').text(label).html() + '</label>';
                    }

                    html += '<div class="' + groupClasses.join(' ') + '">';
                    if (icon !== 'none' && iconDict[icon]) {
                        html += '<span class="form-input-icon form-input-icon-left">' + getSvg(icon) + '</span>';
                    }
                    html += '<select class="' + controlClasses.join(' ') + '"' + (isDisabled ? ' disabled' : '') + '>';
                    html += '<option value="" disabled selected>মেথড নির্বাচন করুন...</option>';
                    html += '<option value="cash">Cash in Hand</option>';
                    html += '<option value="bkash">bKash Merchant</option>';
                    html += '<option value="card">POS Card Terminal</option>';
                    html += '</select></div>';

                    $('#select-preview-target').html(html);

                    let snippet = '<' + 'x-core::select';
                    if (label) snippet += ' label="' + label + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    if (variant !== 'outline') snippet += ' variant="' + variant + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (isPill) snippet += ' rounded="pill"';
                    if (icon !== 'none') snippet += ' icon="' + icon + '"';
                    if (isDisabled) snippet += ' disabled';
                    snippet += ' placeholder="মেথড নির্বাচন করুন..."\n';
                    snippet += '    :options="[\n';
                    snippet += "        'cash' => 'Cash in Hand',\n";
                    snippet += "        'bkash' => 'bKash Merchant',\n";
                    snippet += "        'card' => 'POS Card Terminal'\n";
                    snippet += '    ]" />';
                    $('#demo-sel-code').text(snippet);
                }
                $('#sel-ctrl-size, #sel-ctrl-variant, #sel-ctrl-color, #sel-ctrl-icon, #sel-ctrl-rounded-pill, #sel-ctrl-disabled').on('change', updateSelPlayground);
                $('#sel-ctrl-label').on('input', updateSelPlayground);
                $('#copy-sel-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-sel-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('সিলেক্ট কোড কপি হয়েছে!', 'Select code copied!');
                    });
                });

                // 4. Textarea Playground Updater
                function updateTxtPlayground() {
                    const rows = $('#txt-ctrl-rows').val() || '3';
                    const resize = $('#txt-ctrl-resize').val() || 'vertical';
                    const variant = $('#txt-ctrl-variant').val() || 'outline';
                    const color = $('#txt-ctrl-color').val() || 'teal';
                    const label = $('#txt-ctrl-label').val() || 'ঠিকানা ও শিপিং নোট';
                    const showCount = $('#txt-ctrl-show-count').is(':checked');
                    const isDisabled = $('#txt-ctrl-disabled').is(':checked');

                    let controlClasses = ['form-control', 'form-textarea'];
                    if (variant === 'filled') controlClasses.push('form-control-filled');
                    else if (variant === 'flushed') controlClasses.push('form-control-flushed');
                    else controlClasses.push('form-control-outline');
                    if (resize === 'none') controlClasses.push('form-textarea-no-resize');
                    controlClasses.push('form-' + color);

                    let html = '';
                    if (label) {
                        html += '<label class="form-label form-label-md">' + $('<div>').text(label).html() + '</label>';
                    }
                    html += '<div class="form-input-group">';
                    html += '<textarea rows="' + rows + '" class="' + controlClasses.join(' ') + '" placeholder="বিস্তারিত ঠিকানা লিখুন..." maxlength="200"';
                    if (isDisabled) html += ' disabled';
                    if (showCount) html += ' oninput="$(this).closest(\'.sg-preview-box\').find(\'.count-val\').text(this.value.length);"';
                    html += '></textarea></div>';
                    if (showCount) {
                        html += '<div class="form-textarea-count" style="margin-top:4px;"><span class="count-val">0</span>/200 characters</div>';
                    }

                    $('#textarea-preview-target').html(html);

                    let snippet = '<' + 'x-core::textarea';
                    if (label) snippet += ' label="' + label + '"';
                    if (rows !== '3') snippet += ' rows="' + rows + '"';
                    if (resize !== 'vertical') snippet += ' resize="' + resize + '"';
                    if (variant !== 'outline') snippet += ' variant="' + variant + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (showCount) snippet += ' show-count max-length="200"';
                    if (isDisabled) snippet += ' disabled';
                    snippet += ' placeholder="বিস্তারিত ঠিকানা লিখুন..." />';
                    $('#demo-txt-code').text(snippet);
                }
                $('#txt-ctrl-rows, #txt-ctrl-resize, #txt-ctrl-variant, #txt-ctrl-color, #txt-ctrl-show-count, #txt-ctrl-disabled').on('change', updateTxtPlayground);
                $('#txt-ctrl-label').on('input', updateTxtPlayground);
                $('#copy-txt-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-txt-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('টেক্সট-এরিয়া কোড কপি হয়েছে!', 'Textarea code copied!');
                    });
                });

                // 5. Checkbox Playground Updater
                function updateChkPlayground() {
                    const color = $('#chk-ctrl-color').val() || 'teal';
                    const size = $('#chk-ctrl-size').val() || 'md';
                    const label = $('#chk-ctrl-label').val() || 'শর্তাবলী মেনে নিচ্ছি';
                    const desc = $('#chk-ctrl-desc').val() || '';
                    const isChecked = $('#chk-ctrl-checked').is(':checked');
                    const isDisabled = $('#chk-ctrl-disabled').is(':checked');

                    let wrapClasses = ['form-check', 'form-check-' + size, 'form-' + color];
                    let html = '<label class="' + wrapClasses.join(' ') + '">';
                    html += '<input type="checkbox"' + (isChecked ? ' checked' : '') + (isDisabled ? ' disabled' : '') + ' />';
                    html += '<span class="form-check-box">' + getSvg('check') + '</span>';
                    if (label || desc) {
                        html += '<span class="form-check-label">';
                        if (label) html += $('<div>').text(label).html();
                        if (desc) html += '<span class="form-check-desc">' + $('<div>').text(desc).html() + '</span>';
                        html += '</span>';
                    }
                    html += '</label>';

                    $('#chk-preview-target').html(html);

                    let snippet = '<' + 'x-core::checkbox';
                    snippet += ' label="' + label + '"';
                    if (desc) snippet += ' description="' + desc + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    if (isChecked) snippet += ' checked';
                    if (isDisabled) snippet += ' disabled';
                    snippet += ' />';
                    $('#demo-chk-code').text(snippet);
                }
                $('#chk-ctrl-color, #chk-ctrl-size, #chk-ctrl-checked, #chk-ctrl-disabled').on('change', updateChkPlayground);
                $('#chk-ctrl-label, #chk-ctrl-desc').on('input', updateChkPlayground);
                $('#copy-chk-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-chk-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('চেকবক্স কোড কপি হয়েছে!', 'Checkbox code copied!');
                    });
                });

                // 6. Toggle Playground Updater
                function updateTogPlayground() {
                    const color = $('#tog-ctrl-color').val() || 'teal';
                    const size = $('#tog-ctrl-size').val() || 'md';
                    const label = $('#tog-ctrl-label').val() || 'ডার্ক মোড সক্রিয় করুন';
                    const desc = $('#tog-ctrl-desc').val() || '';
                    const isChecked = $('#tog-ctrl-checked').is(':checked');
                    const isDisabled = $('#tog-ctrl-disabled').is(':checked');

                    let wrapClasses = ['form-toggle-wrap', 'form-toggle-' + size, 'form-' + color];
                    let html = '<label class="' + wrapClasses.join(' ') + '">';
                    html += '<input type="checkbox"' + (isChecked ? ' checked' : '') + (isDisabled ? ' disabled' : '') + ' />';
                    html += '<span class="form-toggle-track"><span class="form-toggle-thumb">';
                    html += getSvg(isChecked ? 'moon' : 'sun');
                    html += '</span></span>';
                    if (label || desc) {
                        html += '<span class="form-toggle-label">';
                        if (label) html += $('<div>').text(label).html();
                        if (desc) html += '<span class="form-toggle-desc">' + $('<div>').text(desc).html() + '</span>';
                        html += '</span>';
                    }
                    html += '</label>';

                    $('#tog-preview-target').html(html);

                    let snippet = '<' + 'x-core::toggle';
                    snippet += ' label="' + label + '"';
                    if (desc) snippet += ' description="' + desc + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    snippet += ' icon-on="moon" icon-off="sun"';
                    if (isChecked) snippet += ' checked';
                    if (isDisabled) snippet += ' disabled';
                    snippet += ' />';
                    $('#demo-tog-code').text(snippet);
                }
                $('#tog-ctrl-color, #tog-ctrl-size, #tog-ctrl-checked, #tog-ctrl-disabled').on('change', updateTogPlayground);
                $('#tog-ctrl-label, #tog-ctrl-desc').on('input', updateTogPlayground);
                $('#copy-tog-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-tog-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('টগল কোড কপি হয়েছে!', 'Toggle code copied!');
                    });
                });

                // 7. Radio Card Playground Updater
                function updateRcPlayground() {
                    const color = $('#rc-ctrl-color').val() || 'teal';
                    const icon = $('#rc-ctrl-icon').val() || 'cash';
                    const title = $('#rc-ctrl-title').val() || 'ক্যাশ পেমেন্ট';
                    const desc = $('#rc-ctrl-desc').val() || 'সরাসরি ক্যাশ বা ড্রয়ার পেমেন্ট গ্রহণ করুন';
                    const badge = $('#rc-ctrl-badge').val() || 'Instant';

                    let wrapClasses = ['form-radio-card', 'form-' + color, 'active'];
                    let html = '<label class="' + wrapClasses.join(' ') + '">';
                    html += '<input type="radio" name="demo_radio_play" checked />';
                    if (icon !== 'none' && iconDict[icon]) {
                        html += '<span class="card-icon">' + getSvg(icon) + '</span>';
                    }
                    html += '<span class="card-content">';
                    if (title) html += '<span class="card-title">' + $('<div>').text(title).html() + '</span>';
                    if (desc) html += '<span class="card-desc">' + $('<div>').text(desc).html() + '</span>';
                    html += '</span>';
                    if (badge) {
                        html += '<span class="card-badge badge-' + color + '">' + $('<div>').text(badge).html() + '</span>';
                    }
                    html += '</label>';

                    $('#card-preview-target').html(html);

                    let snippet = '<' + 'x-core::radio-card\n';
                    snippet += '    name="payment_choice"\n';
                    snippet += '    value="cash"\n';
                    snippet += '    title="' + title + '"\n';
                    snippet += '    description="' + desc + '"\n';
                    snippet += '    icon="' + icon + '"\n';
                    if (badge) snippet += '    badge="' + badge + '"\n';
                    if (color !== 'teal') snippet += '    color="' + color + '"\n';
                    snippet += '    checked\n/>';
                    $('#demo-rc-code').text(snippet);
                }
                $('#rc-ctrl-color, #rc-ctrl-icon').on('change', updateRcPlayground);
                $('#rc-ctrl-title, #rc-ctrl-desc, #rc-ctrl-badge').on('input', updateRcPlayground);
                $('#copy-rc-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-rc-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('রেডিও কার্ড কোড কপি হয়েছে!', 'Radio card code copied!');
                    });
                });

                // 8. Table Playground Updater
                function updateTblPlayground() {
                    const variant = $('#tbl-ctrl-variant').val() || 'card';
                    const size = $('#tbl-ctrl-size').val() || 'md';
                    const color = $('#tbl-ctrl-color').val() || 'teal';
                    const title = $('#tbl-ctrl-title').val() || '';
                    const hasSearch = $('#tbl-ctrl-searchable').is(':checked');
                    const hasCheckbox = $('#tbl-ctrl-checkbox').is(':checked');
                    const isSticky = $('#tbl-ctrl-sticky').is(':checked');
                    const hasPagination = $('#tbl-ctrl-pagination').is(':checked');
                    const isEmpty = $('#tbl-ctrl-empty').is(':checked');

                    let containerClasses = ['table-container'];
                    if (variant === 'flush') containerClasses.push('table-flush');
                    if (size !== 'md') containerClasses.push('table-container-' + size);
                    if (color) containerClasses.push('table-' + color);

                    let tableClasses = ['app-table'];
                    if (variant === 'striped') tableClasses.push('app-table-striped');
                    else if (variant === 'bordered') tableClasses.push('app-table-bordered');
                    else if (variant === 'borderless') tableClasses.push('app-table-borderless');
                    tableClasses.push('app-table-hover');

                    let html = '<div class="' + containerClasses.join(' ') + '">';

                    if (title || hasSearch) {
                        html += '<div class="table-toolbar">';
                        html += '<div class="table-toolbar-start">';
                        if (title) html += '<div class="table-title-wrap"><div class="table-title">' + $('<div>').text(title).html() + '</div></div>';
                        if (hasSearch) {
                            html += '<div class="table-search-input"><div class="form-input-group has-icon-left form-input-group-sm"><span class="form-input-icon form-input-icon-left">' + getSvg('search') + '</span><input type="search" class="form-control form-control-sm table-quick-search" placeholder="খুঁজুন..." /></div></div>';
                        }
                        html += '</div>';
                        html += '<div class="table-toolbar-end"><button type="button" class="btn btn-solid-teal btn-teal btn-xs"><span class="btn-icon">' + getSvg('plus') + '</span><span class="btn-text">Add Item</span></button></div>';
                        html += '</div>';
                    }

                    if (hasCheckbox) {
                        html += '<div class="table-bulk-bar" style="display:none;" data-table-bulk-bar><div class="table-bulk-info">' + getSvg('check-circle') + '<span><strong class="selected-count">0</strong> টি আইটেম নির্বাচিত</span></div><div><button type="button" class="btn btn-soft-red btn-xs"><span class="btn-icon">' + getSvg('trash') + '</span><span class="btn-text">মুছুন</span></button></div></div>';
                    }

                    html += '<div class="table-responsive ' + (isSticky ? 'table-sticky-header' : '') + '" ' + (isSticky ? 'style="max-height:220px;"' : '') + '>';
                    html += '<table class="' + tableClasses.join(' ') + '">';
                    html += '<thead><tr>';
                    if (hasCheckbox) html += '<th class="table-check-col"><label class="form-check form-check-sm" style="margin:0; justify-content:center;"><input type="checkbox" data-table-select-all /><span class="form-check-box">' + getSvg('check') + '</span></label></th>';
                    html += '<th class="sortable"><div class="th-wrap"><span>আইটেম বিবরণ</span><span class="sort-icon">' + getSvg('chevron-down') + '</span></div></th>';
                    html += '<th class="table-cell-center">ক্যাটেগরি</th>';
                    html += '<th class="table-cell-right sortable"><div class="th-wrap justify-end"><span>মূল্য</span><span class="sort-icon">' + getSvg('chevron-down') + '</span></div></th>';
                    html += '<th class="table-cell-right">অ্যাকশন</th>';
                    html += '</tr></thead>';

                    html += '<tbody>';
                    if (isEmpty) {
                        html += '<tr><td colspan="10"><div class="table-empty"><div class="table-empty-icon">' + getSvg('box') + '</div><div class="table-empty-title">কোনো তথ্য পাওয়া যায়নি</div><div class="table-empty-desc">আপনার ফিল্টারের সাথে মিল রেখে কোনো রেকর্ড নেই</div></div></td></tr>';
                    } else {
                        const demoData = [
                            { id: '1', name: 'Wireless Bluetooth Mouse', cat: 'Accessories', price: '৳ ৮৫০.০০', badge: 'teal' },
                            { id: '2', name: 'Mechanical Keyboard RGB', cat: 'Peripherals', price: '৳ ৩,৫০০.০০', badge: 'gold' },
                            { id: '3', name: 'Ultra-fast SSD 512GB NVMe', cat: 'Storage', price: '৳ ৫,২০০.০০', badge: 'green' }
                        ];
                        demoData.forEach(function (d) {
                            html += '<tr>';
                            if (hasCheckbox) html += '<td class="table-check-col"><label class="form-check form-check-sm" style="margin:0; justify-content:center;"><input type="checkbox" data-table-select-row value="' + d.id + '" /><span class="form-check-box">' + getSvg('check') + '</span></label></td>';
                            html += '<td class="table-cell-bold">' + d.name + '</td>';
                            html += '<td class="table-cell-center"><span style="background:var(--' + d.badge + '-100); color:var(--' + d.badge + '-800); font-weight:700; font-size:11px; padding:2px 8px; border-radius:99px;">' + d.cat + '</span></td>';
                            html += '<td class="table-cell-right table-cell-bold">' + d.price + '</td>';
                            html += '<td class="table-cell-right"><div class="table-cell-actions"><button type="button" class="btn btn-soft-teal btn-xs btn-icon-only" title="Edit">' + getSvg('edit') + '</button><button type="button" class="btn btn-soft-red btn-xs btn-icon-only" title="Delete">' + getSvg('trash') + '</button></div></td>';
                            html += '</tr>';
                        });
                    }
                    html += '</tbody></table></div>';

                    if (hasPagination && !isEmpty) {
                        html += '<div class="table-footer"><div class="table-pagination-info">Showing 1 to 3 of 48 records</div><div class="table-pagination"><a class="table-page-btn disabled">&laquo;</a><a class="table-page-btn active">1</a><a class="table-page-btn">2</a><a class="table-page-btn">&raquo;</a></div></div>';
                    }
                    html += '</div>';

                    $('#table-preview-target').html(html);

                    // Build Blade Code
                    let snippet = '<' + 'x-core::table';
                    if (title) snippet += ' title="' + title + '"';
                    if (variant !== 'card') snippet += ' variant="' + variant + '"';
                    if (size !== 'md') snippet += ' size="' + size + '"';
                    if (color !== 'teal') snippet += ' color="' + color + '"';
                    if (hasSearch) snippet += ' searchable';
                    if (isSticky) snippet += ' sticky-header max-height="350px"';
                    if (isEmpty) snippet += ' empty empty-title="কোনো তথ্য পাওয়া যায়নি"';
                    snippet += '>\n';

                    snippet += '    <' + 'x-slot:header>\n';
                    if (hasCheckbox) snippet += '        <' + 'x-core::table.th checkbox />\n';
                    snippet += '        <' + 'x-core::table.th sortable>আইটেম বিবরণ<' + '/x-core::table.th>\n';
                    snippet += '        <' + 'x-core::table.th align="center">ক্যাটেগরি<' + '/x-core::table.th>\n';
                    snippet += '        <' + 'x-core::table.th align="right" sortable>মূল্য<' + '/x-core::table.th>\n';
                    snippet += '        <' + 'x-core::table.th align="right">অ্যাকশন<' + '/x-core::table.th>\n';
                    snippet += '    <' + '/x-slot:header>\n\n';

                    snippet += '    @@foreach ($items as $item)\n';
                    snippet += '        <' + 'x-core::table.tr>\n';
                    if (hasCheckbox) snippet += '            <' + 'x-core::table.td checkbox :value="$item->id" />\n';
                    snippet += '            <' + 'x-core::table.td bold>@{{ $item->name }}<' + '/x-core::table.td>\n';
                    snippet += '            <' + 'x-core::table.td align="center">@{{ $item->category }}<' + '/x-core::table.td>\n';
                    snippet += '            <' + 'x-core::table.td align="right" bold>৳ @{{ number_format($item->price, 2) }}<' + '/x-core::table.td>\n';
                    snippet += '            <' + 'x-core::table.td actions>\n';
                    snippet += '                <' + 'x-core::button size="xs" variant="soft" color="teal" icon="edit" icon-only title="Edit" />\n';
                    snippet += '                <' + 'x-core::button size="xs" variant="soft" color="red" icon="trash" icon-only title="Delete" />\n';
                    snippet += '            <' + '/x-core::table.td>\n';
                    snippet += '        <' + '/x-core::table.tr>\n';
                    snippet += '    @@endforeach\n';

                    if (hasPagination) {
                        snippet += '\n    <' + 'x-slot:pagination>\n';
                        snippet += '        @{{ $items->links() }}\n';
                        snippet += '    <' + '/x-slot:pagination>\n';
                    }

                    snippet += '<' + '/x-core::table>';
                    $('#demo-tbl-code').text(snippet);
                }

                $('#tbl-ctrl-variant, #tbl-ctrl-size, #tbl-ctrl-color, #tbl-ctrl-searchable, #tbl-ctrl-checkbox, #tbl-ctrl-sticky, #tbl-ctrl-pagination, #tbl-ctrl-empty').on('change', updateTblPlayground);
                $('#tbl-ctrl-title').on('input', updateTblPlayground);
                $('#copy-tbl-code').on('click', function () {
                    navigator.clipboard.writeText($('#demo-tbl-code').text()).then(function () {
                        if (typeof window.toast === 'function') window.toast('টেবিল কোড কপি হয়েছে!', 'Table code copied!');
                    });
                });

                // Initial runs
                updateBtnPlayground();
                updateInpPlayground();
                updateSelPlayground();
                updateTxtPlayground();
                updateChkPlayground();
                updateTogPlayground();
                updateRcPlayground();
                updateTblPlayground();
            }

            function bootstrap() {
                if (typeof window.jQuery !== 'undefined') {
                    window.jQuery(function () {
                        initStyleguide(window.jQuery);
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