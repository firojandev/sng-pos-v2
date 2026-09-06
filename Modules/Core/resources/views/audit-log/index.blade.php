<x-core::layout
    title="অ্যাক্টিভিটি ও অডিট লগ"
    title-en="Audit & Activity Log"
    subtitle="সিস্টেমের গুরুত্বপূর্ণ লেনদেন ও রেকর্ডের পরিবর্তনের সুরক্ষিত ইতিহাস"
    subtitle-en="Secure audit trail and historical logs of critical records"
    active="audit-log"
>
    <style>
        .stat-grid-5 {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        @media (max-width: 1280px) {
            .stat-grid-5 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .stat-grid-5 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 480px) {
            .stat-grid-5 {
                grid-template-columns: 1fr;
            }
        }
    </style>

    {{-- Executive Summary Stat Grid (1 Row 5 Cards) --}}
    @if (isset($metrics))
        <div class="stat-grid-5">
            <x-core::stat-card
                icon="activity"
                color="teal"
                :value="number_format($metrics['totalLogs'])"
                label="সর্বমোট অ্যাক্টিভিটি"
                label-en="Total Activities"
                subtext="সকল সুরক্ষিত সিস্টেম রেকর্ড"
                subtext-en="All recorded system events"
            />

            <x-core::stat-card
                icon="clock"
                color="blue"
                :value="number_format($metrics['todayLogs'])"
                label="আজকের অ্যাক্টিভিটি"
                label-en="Today's Events"
                subtext="আজকে সংঘটিত কার্যক্রম"
                subtext-en="Activities logged today"
            />

            <x-core::stat-card
                icon="plus-circle"
                color="green"
                value-color="green"
                :value="number_format($metrics['createdLogs'])"
                label="নতুন এন্ট্রি তৈরি"
                label-en="Created Records"
                subtext="নতুন ডাটা সংযোজন"
                subtext-en="New records inserted"
            />

            <x-core::stat-card
                icon="edit"
                color="gold"
                :value="number_format($metrics['updatedLogs'])"
                label="হালনাগাদ / পরিবর্তন"
                label-en="Updated Records"
                subtext="পরিবর্তিত তথ্যের ইতিহাস"
                subtext-en="Modifications tracked"
            />

            <x-core::stat-card
                icon="trash-2"
                color="red"
                value-color="red"
                :value="number_format($metrics['deletedLogs'])"
                label="মুছে ফেলা রেকর্ড"
                label-en="Deleted Records"
                subtext="বাতিল বা মুছে ফেলা ডাটা"
                subtext-en="Soft & hard deleted entries"
            />
        </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:200px; flex-shrink:0;">
                <x-core::select
                    id="filter-model"
                    name="filter_model"
                    size="sm"
                    :no-margin="true"
                    :options="$modelOptions"
                />
            </div>

            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-action"
                    name="filter_action"
                    size="sm"
                    :no-margin="true"
                    :options="$actionOptions"
                />
            </div>

            <div style="width:180px; flex-shrink:0;">
                <x-core::select
                    id="filter-user"
                    name="filter_user_id"
                    size="sm"
                    :no-margin="true"
                    :options="$userOptions"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-from"
                    name="filter_date_from"
                    size="sm"
                    :no-margin="true"
                    placeholder="হতে / From"
                    title="তারিখ হতে / Date From"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-to"
                    name="filter_date_to"
                    size="sm"
                    :no-margin="true"
                    placeholder="পর্যন্ত / To"
                    title="তারিখ পর্যন্ত / Date To"
                />
            </div>

            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reset-filters"
                title="রিসেট / Reset Filters"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'audit-log-table']) !!}
        </div>
    </div>

    {{-- Advanced Audit Log Detail Drawer --}}
    <div class="drawer-backdrop" id="auditDetailDrawer" style="display:none;">
        <div class="drawer" style="width:640px; max-width:96vw; display:flex; flex-direction:column; padding:0; box-sizing:border-box;">
            <div class="drawer-head" style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div id="drawer-action-icon" style="width:36px; height:36px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="file-text" size="18" />
                    </div>
                    <div>
                        <div style="font-size:15px; font-weight:700; color:var(--ink-900);" id="drawer-title">
                            <span class="bn">অ্যাক্টিভিটি বিবরণ</span>
                            <span class="en" style="display:none;">Audit Activity Details</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);" id="drawer-subtitle">
                            —
                        </div>
                    </div>
                </div>
                <button type="button" class="drawer-x btn-close-audit-drawer" style="background:none; border:none; font-size:22px; cursor:pointer; color:var(--ink-400); line-height:1;">&times;</button>
            </div>

            <div style="padding:14px 20px; background:var(--paper); border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:8px;" id="drawer-meta-bar">
                <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                    <span id="drawer-badge-action"></span>
                    <span id="drawer-badge-model"></span>
                    <span id="drawer-badge-ip" style="font-size:11px; font-family:var(--font-mono, monospace); background:var(--card); border:1px solid var(--border); padding:2px 6px; border-radius:4px; color:var(--ink-700);"></span>
                </div>
                <div id="drawer-live-link"></div>
            </div>

            {{-- Drawer Tabs --}}
            <div style="display:flex; align-items:center; gap:4px; padding:8px 20px; border-bottom:1px solid var(--border); background:var(--card); flex-shrink:0;">
                <button type="button" class="audit-tab-btn active" data-tab="tab-diff" style="padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:1px solid transparent; background:var(--paper-line); color:var(--ink-900);">
                    <span class="bn">পরিবর্তনের তালিকা (Diff)</span>
                    <span class="en" style="display:none;">Change Diff</span>
                </button>
                <button type="button" class="audit-tab-btn" data-tab="tab-security" style="padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:1px solid transparent; background:transparent; color:var(--ink-600);">
                    <span class="bn">ফরেনসিক তথ্য</span>
                    <span class="en" style="display:none;">Forensics &amp; Device</span>
                </button>
                <button type="button" class="audit-tab-btn" data-tab="tab-json" style="padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; border:1px solid transparent; background:transparent; color:var(--ink-600);">
                    <span class="bn">কাঁচা ডেটা (JSON)</span>
                    <span class="en" style="display:none;">Raw JSON</span>
                </button>
            </div>

            {{-- Tab Contents --}}
            <div style="flex:1; overflow-y:auto; padding:16px 20px; box-sizing:border-box;">
                {{-- Tab 1: Diff --}}
                <div class="audit-tab-pane" id="tab-diff">
                    <div id="drawer-diff-empty" style="display:none; text-align:center; padding:30px 10px; color:var(--ink-500);">
                        <x-core::icon name="info" size="24" style="margin-bottom:6px; opacity:0.6;" />
                        <div class="bn">কোনো সুনির্দিষ্ট ফিল্ড পরিবর্তন রেকর্ড করা হয়নি।</div>
                        <div class="en" style="display:none;">No specific field changes recorded.</div>
                    </div>

                    <div id="drawer-diff-container" class="table-wrap" style="border:1px solid var(--border); border-radius:8px; overflow-x:auto; width:100%; max-width:100%; box-sizing:border-box;">
                        <table style="width:100% !important; min-width:0 !important; max-width:100% !important; table-layout:fixed !important; border-collapse:collapse; font-size:12.5px;">
                            <thead>
                                <tr style="background:var(--paper); border-bottom:1px solid var(--border);">
                                    <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700); width:30%; white-space:normal !important; box-sizing:border-box;">
                                        <span class="bn">ফিল্ড / বিষয়</span>
                                        <span class="en" style="display:none;">Field</span>
                                    </th>
                                    <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700); width:35%; white-space:normal !important; word-break:break-word !important; box-sizing:border-box;">
                                        <span class="bn">পূর্বের মান (Old)</span>
                                        <span class="en" style="display:none;">Old Value</span>
                                    </th>
                                    <th style="padding:10px 12px; text-align:left; font-weight:700; color:var(--ink-700); width:35%; white-space:normal !important; word-break:break-word !important; box-sizing:border-box;">
                                        <span class="bn">বর্তমান মান (New)</span>
                                        <span class="en" style="display:none;">New Value</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="drawer-diff-tbody">
                                {{-- Populated dynamically via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 2: Security & Forensics --}}
                <div class="audit-tab-pane" id="tab-security" style="display:none;">
                    <div style="display:flex; flex-direction:column; gap:12px;">
                        <div class="tx-section" style="border:1px solid var(--border); border-radius:8px; padding:12px 14px; background:var(--card);">
                            <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:6px;">
                                <span class="bn">ব্যবহারকারী ও অধিবেশন</span>
                                <span class="en" style="display:none;">User &amp; Session Info</span>
                            </div>
                            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; font-size:12px; line-height:1.5;">
                                <div style="color:var(--ink-500);">ব্যবহারকারী / User:</div>
                                <div id="sec-user-name" style="font-weight:600; color:var(--ink-900);">—</div>

                                <div style="color:var(--ink-500);">ইমেইল / Email:</div>
                                <div id="sec-user-email" style="font-family:var(--font-mono, monospace); color:var(--ink-800);">—</div>

                                <div style="color:var(--ink-500);">শাখা / Shop:</div>
                                <div id="sec-shop-name" style="color:var(--ink-800);">—</div>

                                <div style="color:var(--ink-500);">তারিখ ও সময় / Time:</div>
                                <div id="sec-timestamp" style="color:var(--ink-800);">—</div>
                            </div>
                        </div>

                        <div class="tx-section" style="border:1px solid var(--border); border-radius:8px; padding:12px 14px; background:var(--card);">
                            <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:6px;">
                                <span class="bn">নেটওয়ার্ক ও ডিভাইস পরিচিতি</span>
                                <span class="en" style="display:none;">Network &amp; Device Details</span>
                            </div>
                            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; font-size:12px; line-height:1.5;">
                                <div style="color:var(--ink-500);">আইপি অ্যাড্রেস / IP:</div>
                                <div id="sec-ip" style="font-family:var(--font-mono, monospace); font-weight:600; color:var(--teal-800);">—</div>

                                <div style="color:var(--ink-500);">অপারেটিং সিস্টেম / OS:</div>
                                <div id="sec-platform" style="color:var(--ink-800);">—</div>

                                <div style="color:var(--ink-500);">ব্রাউজার / Browser:</div>
                                <div id="sec-browser" style="color:var(--ink-800);">—</div>

                                <div style="color:var(--ink-500); align-self:start;">ইউজার এজেন্ট / Agent:</div>
                                <div id="sec-user-agent" style="font-size:11px; font-family:var(--font-mono, monospace); word-break:break-all; color:var(--ink-600); background:var(--paper); padding:6px 8px; border-radius:6px; border:1px solid var(--border);">—</div>
                            </div>
                        </div>

                        <div class="tx-section" style="border:1px solid var(--border); border-radius:8px; padding:12px 14px; background:var(--card);">
                            <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-bottom:8px; border-bottom:1px solid var(--border); padding-bottom:6px;">
                                <span class="bn">টার্গেট রেকর্ড পরিচিতি</span>
                                <span class="en" style="display:none;">Target Entity Info</span>
                            </div>
                            <div style="display:grid; grid-template-columns:140px 1fr; gap:8px; font-size:12px; line-height:1.5;">
                                <div style="color:var(--ink-500);">মডেল ক্লাস / Class:</div>
                                <div id="sec-model-class" style="font-family:var(--font-mono, monospace); font-size:11.5px; word-break:break-all; color:var(--ink-800);">—</div>

                                <div style="color:var(--ink-500);">রেকর্ড আইডি / ID:</div>
                                <div id="sec-model-id" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tab 3: Raw JSON --}}
                <div class="audit-tab-pane" id="tab-json" style="display:none;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:12px; font-weight:600; color:var(--ink-700);">
                            <span class="bn">সরাসরি ডেটাবেস পেলোড</span>
                            <span class="en" style="display:none;">Raw JSON Payload</span>
                        </span>
                        <x-core::button
                            type="button"
                            variant="secondary"
                            size="sm"
                            icon="copy"
                            id="btn-copy-json"
                            title="কপি করুন / Copy JSON"
                        >
                            <span class="bn">কপি করুন</span>
                            <span class="en" style="display:none;">Copy JSON</span>
                        </x-core::button>
                    </div>

                    <div style="margin-bottom:12px;">
                        <div style="font-size:11.5px; font-weight:600; color:var(--ink-600); margin-bottom:4px;">
                            <span class="bn">বর্তমান মান (New Values JSON)</span>
                            <span class="en" style="display:none;">New Values</span>
                        </div>
                        <pre id="json-new-view" style="margin:0; background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:12px; font-size:11.5px; font-family:var(--font-mono, monospace); color:var(--ink-900); overflow-x:auto; max-height:220px;"></pre>
                    </div>

                    <div>
                        <div style="font-size:11.5px; font-weight:600; color:var(--ink-600); margin-bottom:4px;">
                            <span class="bn">পূর্বের মান (Old Values JSON)</span>
                            <span class="en" style="display:none;">Old Values</span>
                        </div>
                        <pre id="json-old-view" style="margin:0; background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:12px; font-size:11.5px; font-family:var(--font-mono, monospace); color:var(--ink-900); overflow-x:auto; max-height:220px;"></pre>
                    </div>
                </div>
            </div>

            <div style="padding:12px 20px; border-top:1px solid var(--border); background:var(--paper); display:flex; align-items:center; justify-content:flex-end; flex-shrink:0;">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="btn-close-audit-drawer"
                >
                    <span class="bn">বন্ধ করুন</span>
                    <span class="en" style="display:none;">Close</span>
                </x-core::button>
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            var tableId = 'audit-log-table';

            function getTable() {
                return window.LaravelDataTables ? window.LaravelDataTables[tableId] : null;
            }

            // Real-time table redraw on filter changes
            $('#filter-model, #filter-action, #filter-user, #filter-date-from, #filter-date-to').on('change', function () {
                var table = getTable();
                if (table) {
                    table.draw();
                }
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function () {
                $('#filter-model').val('all');
                $('#filter-action').val('all');
                $('#filter-user').val('all');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');

                var table = getTable();
                if (table) {
                    table.draw();
                }
            });

            // Drawer opening & closing
            function showDrawer() {
                $('#auditDetailDrawer').css('display', 'flex');
                $('body').css('overflow', 'hidden');
            }

            function closeDrawer() {
                $('#auditDetailDrawer').css('display', 'none');
                $('body').css('overflow', '');
            }

            $(document).on('click', '.btn-close-audit-drawer', function () {
                closeDrawer();
            });

            $(document).on('click', '#auditDetailDrawer', function (e) {
                if ($(e.target).is('#auditDetailDrawer')) {
                    closeDrawer();
                }
            });

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape' && $('#auditDetailDrawer:visible').length) {
                    closeDrawer();
                }
            });

            // Tab switching
            $(document).on('click', '.audit-tab-btn', function () {
                var tab = $(this).data('tab');
                $('.audit-tab-btn').removeClass('active').css({
                    'background': 'transparent',
                    'color': 'var(--ink-600)'
                });
                $(this).addClass('active').css({
                    'background': 'var(--paper-line)',
                    'color': 'var(--ink-900)'
                });

                $('.audit-tab-pane').hide();
                $('#' + tab).show();
            });

            var activeAuditJson = '';

            // Load audit details via AJAX
            function loadAuditDetails(url) {
                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (res) {
                        if (!res.success) return;

                        var log = res.log;
                        var diff = res.diff || [];
                        var target = res.target_record;

                        // Header info
                        $('#drawer-title .bn').text(log.model.name_bn + ' (#' + log.model.id + ') ' + log.action_label.bn);
                        $('#drawer-title .en').text(log.model.name_en + ' (#' + log.model.id + ') ' + log.action_label.en);
                        $('#drawer-subtitle').text(log.created_at + ' · ' + log.created_at_human);

                        // Meta bar
                        $('#drawer-badge-action').html('<span class="badge b-' + (log.action === 'created' ? 'green' : (log.action === 'deleted' ? 'red' : (log.action === 'restored' ? 'teal' : 'gold'))) + '">' + log.action_label.bn + ' / ' + log.action_label.en + '</span>');
                        $('#drawer-badge-model').html('<span class="badge b-teal">' + log.model.name_bn + '</span>');
                        $('#drawer-badge-ip').text('IP: ' + log.ip_address);

                        // Target link
                        if (target && target.view_url) {
                            $('#drawer-live-link').html('<a href="' + target.view_url + '" target="_blank" class="btn btn-soft-teal btn-xs" style="text-decoration:none;"><span class="bn">রেকর্ড দেখুন</span><span class="en" style="display:none;">View Record</span> &rarr;</a>');
                        } else {
                            $('#drawer-live-link').empty();
                        }

                        // Diff Table
                        var $tbody = $('#drawer-diff-tbody');
                        $tbody.empty();

                        if (diff.length === 0) {
                            $('#drawer-diff-container').hide();
                            $('#drawer-diff-empty').show();
                        } else {
                            $('#drawer-diff-empty').hide();
                            $('#drawer-diff-container').show();

                            $.each(diff, function (idx, row) {
                                var rowBg = (idx % 2 === 0) ? 'var(--card)' : 'var(--paper)';
                                var oldStyle = 'color:var(--red-600); text-decoration:line-through; font-family:var(--font-mono, monospace); font-size:12px;';
                                var newStyle = 'color:var(--teal-700); font-weight:600; font-family:var(--font-mono, monospace); font-size:12px;';

                                if (row.status === 'unchanged') {
                                    oldStyle = 'color:var(--ink-600); font-family:var(--font-mono, monospace); font-size:12px;';
                                    newStyle = 'color:var(--ink-600); font-family:var(--font-mono, monospace); font-size:12px;';
                                }

                                var tr = '<tr style="background:' + rowBg + '; border-bottom:1px solid var(--border);">'
                                    + '<td style="padding:10px 12px; vertical-align:top; width:30%; white-space:normal !important; word-break:break-word !important; box-sizing:border-box;">'
                                    + '<div style="font-weight:600; color:var(--ink-900); font-size:12px; white-space:normal !important; word-break:break-word !important;">' + $('<div>').text(row.field_label).html() + '</div>'
                                    + '<div style="font-size:10.5px; font-family:var(--font-mono, monospace); color:var(--ink-400); white-space:normal !important; word-break:break-word !important;">' + $('<div>').text(row.field).html() + '</div>'
                                    + '</td>'
                                    + '<td style="padding:10px 12px; vertical-align:top; width:35%; white-space:normal !important; word-break:break-word !important; overflow-wrap:anywhere !important; box-sizing:border-box;">'
                                    + '<span style="display:inline-block; max-width:100%; ' + oldStyle + '">' + $('<div>').text(row.old_value).html() + '</span>'
                                    + '</td>'
                                    + '<td style="padding:10px 12px; vertical-align:top; width:35%; white-space:normal !important; word-break:break-word !important; overflow-wrap:anywhere !important; box-sizing:border-box;">'
                                    + '<span style="display:inline-block; max-width:100%; ' + newStyle + '">' + $('<div>').text(row.new_value).html() + '</span>'
                                    + '</td>'
                                    + '</tr>';
                                $tbody.append(tr);
                            });
                        }

                        // Security & Forensics
                        $('#sec-user-name').text(log.user ? log.user.name : 'সিস্টেম / System');
                        $('#sec-user-email').text(log.user ? log.user.email : 'system@internal');
                        $('#sec-shop-name').text(log.shop_name || 'N/A');
                        $('#sec-timestamp').text(log.created_at + ' (' + log.created_at_human + ')');
                        $('#sec-ip').text(log.ip_address);
                        $('#sec-platform').text(log.platform);
                        $('#sec-browser').text(log.browser);
                        $('#sec-user-agent').text(log.user_agent);
                        $('#sec-model-class').text(log.model.class);
                        $('#sec-model-id').text('#' + log.model.id);

                        // JSON
                        $('#json-new-view').text(res.raw_new_json || 'null');
                        $('#json-old-view').text(res.raw_old_json || 'null');
                        activeAuditJson = JSON.stringify(res, null, 2);

                        // Reset to first tab
                        $('.audit-tab-btn[data-tab="tab-diff"]').trigger('click');

                        showDrawer();
                    },
                    error: function () {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ত্রুটি!',
                                text: 'লগ বিবরণ লোড করা যায়নি।'
                            });
                        }
                    }
                });
            }

            // Click on details button or row
            $(document).on('click', '.btn-view-audit-detail', function (e) {
                e.stopPropagation();
                var url = $(this).data('url');
                loadAuditDetails(url);
            });

            $(document).on('click', '.clickable-audit-row', function (e) {
                if ($(e.target).closest('a, button').length) return;
                var url = $(this).data('url');
                if (url) {
                    loadAuditDetails(url);
                }
            });

            // Copy JSON to clipboard
            $('#btn-copy-json').on('click', function () {
                if (!activeAuditJson) return;
                navigator.clipboard.writeText(activeAuditJson).then(function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'কপি সম্পন্ন!',
                            text: 'JSON ডেটা ক্লিপবোর্ডে কপি করা হয়েছে।',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });
        </script>
    @endpush
</x-core::layout>

