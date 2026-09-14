<x-core::layout
    title="ডাটাবেজ ব্যাকআপ ও পুনরুদ্ধার"
    title-en="Database Backup & Restore"
    subtitle="সম্পূর্ণ ডাটাবেজ বা নির্দিষ্ট দোকানের ডাটা ব্যাকআপ গ্রহণ, তালিকা পর্যালোচনা ও সুরক্ষিত পুনরুদ্ধার"
    subtitle-en="Create full or shop-scoped database backups, review history, and safely restore data"
    active="backup"
>
    <style>
        .backup-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }
        @media (max-width: 1024px) {
            .backup-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 640px) {
            .backup-stat-grid {
                grid-template-columns: 1fr;
            }
        }
        .restore-mode-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--paper);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .restore-mode-card:hover {
            border-color: var(--teal-600);
        }
        .restore-mode-card.active {
            border-color: var(--teal-600);
            background: var(--card);
            box-shadow: var(--shadow-sm);
        }
        .restore-mode-card input[type="radio"] {
            margin-top: 3px;
            accent-color: var(--teal-600);
        }
        #restoreModal {
            position: fixed;
            inset: 0;
            z-index: 1050;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #restoreModal.open {
            display: flex !important;
        }
        #restoreModal .modal-box {
            margin: auto;
        }
        .multiselect-trigger:hover,
        .multiselect-trigger.active {
            border-color: var(--teal-600) !important;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }
        .shop-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            background: var(--card);
            border: 1px solid var(--border);
            font-size: 12.5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .shop-dropdown-item:hover {
            background: var(--paper) !important;
            border-color: var(--teal-600);
        }
        .shop-dropdown-item.selected {
            background: var(--teal-100) !important;
            border-color: var(--teal-600);
        }
        .multiselect-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11.5px;
            background: var(--teal-100);
            color: var(--teal-800);
            border: 1px solid var(--teal-600);
            border-radius: 5px;
            padding: 2px 7px;
            font-weight: 600;
        }
        .remove-shop-pill {
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            line-height: 1;
            padding: 0 2px;
            border-radius: 3px;
            color: var(--teal-800);
        }
        .remove-shop-pill:hover {
            background: var(--red-100);
            color: var(--red-600) !important;
        }
    </style>

    {{-- Executive Summary Stat Grid (4 Cards) --}}
    @if (isset($metrics))
        <div class="backup-stat-grid">
            <x-core::stat-card
                icon="database"
                color="teal"
                :value="number_format($metrics['totalBackups'])"
                label="সর্বমোট ব্যাকআপ"
                label-en="Total Backups"
                subtext="সংরক্ষিত ব্যাকআপ ফাইল"
                subtext-en="Stored backup dumps"
            />

            <x-core::stat-card
                icon="archive"
                color="blue"
                :value="$metrics['totalSize']"
                label="মোট ব্যাকআপ সাইজ"
                label-en="Total Storage Used"
                subtext="লোকাল স্টোরেজে সংরক্ষিত"
                subtext-en="Occupied on private disk"
            />

            <x-core::stat-card
                icon="clock"
                color="green"
                value-color="green"
                :value="$metrics['latestBackupAge']"
                label="সর্বশেষ ব্যাকআপ"
                label-en="Latest Backup"
                :subtext="$metrics['latestBackupDate']"
                :subtext-en="$metrics['latestBackupDate']"
            />

            <x-core::stat-card
                icon="server"
                color="purple"
                :value="$metrics['dbEngine']"
                label="ডাটাবেজ ইঞ্জিন"
                label-en="Database Engine"
                :subtext="$metrics['dbName']"
                :subtext-en="$metrics['dbName']"
            />
        </div>
    @endif

    {{-- Action Toolbar --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:8px;">
            <div style="font-size:13px; font-weight:600; color:var(--ink-700); display:flex; align-items:center; gap:6px;">
                <x-core::icon name="shield-check" size="16" style="color:var(--teal-600);" />
                <span class="bn">সম্পূর্ণ স্কিমা ও দোকান ভিত্তিক আইসোলেটেড ব্যাকআপ</span>
                <span class="en" style="display:none;">Full Schema &amp; Shop-Scoped Backup</span>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:8px;">
            <x-core::button
                type="button"
                variant="secondary"
                size="sm"
                icon="rotate-ccw"
                id="btn-reload-backup-table"
                title="রিলোড / Reload Table"
            >
                <span class="bn">রিলোড</span>
                <span class="en" style="display:none;">Reload</span>
            </x-core::button>

            @if (auth()->user()->isSuperAdmin())
                <x-core::button
                    type="button"
                    color="primary"
                    size="sm"
                    icon="database"
                    id="btn-create-backup"
                    title="নতুন ব্যাকআপ নিন / Create Backup Now"
                >
                    <span class="bn">নতুন ব্যাকআপ নিন</span>
                    <span class="en" style="display:none;">Create Backup Now</span>
                </x-core::button>
            @endif
        </div>
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'backup-data-table']) !!}
        </div>
    </div>

    {{-- Restore Modal --}}
    <div class="modal-backdrop" id="restoreModal" style="z-index:1050;">
        <div class="modal-box" style="width:580px; max-width:95vw; max-height:90vh; margin:auto; overflow-y:auto; padding:24px; border-radius:16px; background:var(--card); color:var(--ink-900); border:1px solid var(--border); box-shadow:var(--shadow-card);">
            {{-- Modal Header --}}
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="rotate-ccw" size="18" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16.5px; font-weight:700;">
                            <span class="bn">ডাটাবেজ পুনরুদ্ধার (Restore Database)</span>
                            <span class="en" style="display:none;">Restore Database</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);">
                            <span class="bn">ব্যাকআপ ফাইল থেকে ডাটা পূর্বাবস্থায় ফিরিয়ে আনুন</span>
                            <span class="en" style="display:none;">Revert database to the state in this backup</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-500);">&times;</button>
            </div>

            {{-- Backup File Info Card --}}
            <div style="background:var(--paper); border:1px solid var(--border); border-radius:8px; padding:10px 14px; margin-bottom:16px; font-size:12.5px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px;">
                    <span style="color:var(--ink-500);">ফাইলের নাম:</span>
                    <span id="restore_file_name" style="font-family:var(--font-mono, monospace); font-weight:700; color:var(--ink-900);">—</span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span style="color:var(--ink-500);">সাইজ ও সময়:</span>
                    <span id="restore_file_meta" style="color:var(--ink-700);">—</span>
                </div>
            </div>

            {{-- Mode Selection --}}
            <div style="margin-bottom:16px;">
                <div style="font-size:13px; font-weight:700; color:var(--ink-900); margin-bottom:8px;">
                    <span class="bn">পুনরুদ্ধার পদ্ধতি নির্বাচন করুন:</span>
                    <span class="en" style="display:none;">Select Restore Scope:</span>
                </div>

                <div style="display:flex; flex-direction:column; gap:8px;">
                    {{-- Full Restore Radio --}}
                    <label class="restore-mode-card active" id="label_mode_full">
                        <input type="radio" name="restore_mode" id="mode_full" value="full" checked>
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">সম্পূর্ণ ডাটাবেজ পুনরুদ্ধার (Full Database Restore)</span>
                                <span class="en" style="display:none;">Full Database Restore</span>
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                                <span class="bn">সমস্ত দোকান, ইউজার এবং সিস্টেম সেটিংস একসাথে রিস্টোর হবে।</span>
                                <span class="en" style="display:none;">Restores all shops, users, settings and tables simultaneously.</span>
                            </div>
                        </div>
                    </label>

                    {{-- Shop Scoped Restore Radio --}}
                    <label class="restore-mode-card" id="label_mode_shops">
                        <input type="radio" name="restore_mode" id="mode_shops" value="shops">
                        <div style="flex:1;">
                            <div style="font-size:13px; font-weight:700; color:var(--ink-900); display:flex; align-items:center; gap:6px;">
                                <span class="bn">নির্দিষ্ট দোকান পুনরুদ্ধার (Specific Shop Restore)</span>
                                <span class="en" style="display:none;">Specific Shop Restore</span>
                                <span class="badge badge-teal badge-xs" style="font-size:10px;">আইসোলেটেড / Scoped</span>
                            </div>
                            <div style="font-size:11.5px; color:var(--ink-500); margin-top:2px;">
                                <span class="bn">শুধু নির্বাচিত দোকানের ডাটা রিস্টোর হবে, অন্যান্য দোকান অক্ষত থাকবে।</span>
                                <span class="en" style="display:none;">Only selected shop data will be restored. Other shops remain untouched.</span>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Shop Selector Container (Multiple Dropdown) --}}
            <div id="shop_selection_container" style="display:none; margin-bottom:16px;">
                <label class="form-label" style="font-size:12.5px; font-weight:700; color:var(--ink-900); margin-bottom:6px; display:flex; align-items:center; justify-content:space-between;">
                    <div>
                        <span class="bn">দোকান(সমূহ) নির্বাচন করুন:</span>
                        <span class="en" style="display:none;">Select Shop(s) to Restore:</span>
                    </div>
                    <span id="shop_selection_count" style="font-size:11.5px; color:var(--ink-500); font-weight:500;">০টি নির্বাচিত (0 selected)</span>
                </label>

                <div class="multiselect-dropdown" id="shop_multiselect_dropdown" style="position:relative; width:100%;">
                    {{-- Dropdown Trigger --}}
                    <div class="multiselect-trigger" id="shop_dropdown_trigger" style="width:100%; min-height:40px; border:1px solid var(--border); border-radius:8px; background:var(--card); padding:6px 12px; display:flex; align-items:center; justify-content:space-between; cursor:pointer; transition:all 0.15s ease; user-select:none;">
                        <div class="multiselect-selected-items" id="shop_dropdown_selected_labels" style="display:flex; align-items:center; flex-wrap:wrap; gap:6px; flex:1; min-width:0;">
                            <span class="placeholder-text" style="color:var(--ink-400); font-size:12.5px;">দোকান ড্রপডাউন থেকে নির্বাচন করুন...</span>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px; margin-left:8px; flex-shrink:0;">
                            <span id="shop_selected_badge" class="badge badge-teal badge-xs" style="display:none; font-size:10px; font-weight:700;"></span>
                            <x-core::icon name="chevron-down" size="16" style="color:var(--ink-500); transition:transform 0.2s;" id="shop_dropdown_chevron" />
                        </div>
                    </div>

                    {{-- Dropdown Menu --}}
                    <div class="multiselect-menu" id="shop_dropdown_menu" style="display:none; margin-top:6px; background:var(--card); border:1px solid var(--border); border-radius:8px; box-shadow:var(--shadow-card); padding:10px; display:flex; flex-direction:column; gap:8px;">
                        {{-- Search & Bulk Actions Bar --}}
                        <div style="display:flex; align-items:center; gap:8px;">
                            <div style="position:relative; flex:1;">
                                <input type="text" id="shop_search_input" class="form-control form-control-sm" placeholder="দোকান খুঁজুন / Search shops..." style="width:100%; font-size:12px; padding:6px 10px 6px 28px; border:1px solid var(--border); border-radius:6px; background:var(--paper); color:var(--ink-900);">
                                <div style="position:absolute; left:8px; top:50%; transform:translateY(-50%); pointer-events:none; color:var(--ink-400); display:flex; align-items:center;">
                                    <x-core::icon name="search" size="13" />
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:4px; flex-shrink:0;">
                                <button type="button" id="btn_multiselect_select_all" style="font-size:11.5px; padding:4px 8px; border:1px solid var(--border); border-radius:6px; background:var(--paper); color:var(--teal-700); cursor:pointer; font-weight:600; transition:all 0.15s;">
                                    <span class="bn">সব</span>
                                    <span class="en" style="display:none;">All</span>
                                </button>
                                <button type="button" id="btn_multiselect_clear_all" style="font-size:11.5px; padding:4px 8px; border:1px solid var(--border); border-radius:6px; background:var(--paper); color:var(--ink-500); cursor:pointer; font-weight:600; transition:all 0.15s;">
                                    <span class="bn">মুছুন</span>
                                    <span class="en" style="display:none;">Clear</span>
                                </button>
                            </div>
                        </div>

                        {{-- Shop Items Scrollable List --}}
                        <div id="restore_shops_list" style="overflow-y:auto; max-height:160px; display:flex; flex-direction:column; gap:4px; padding-top:2px;">
                            <div style="font-size:12px; color:var(--ink-500); text-align:center; padding:12px;">দোকান তালিকা লোড হচ্ছে...</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Warning Banner --}}
            <div style="background:var(--red-100); border:1px solid var(--red-600); border-radius:8px; padding:10px 12px; margin-bottom:16px; display:flex; align-items:flex-start; gap:8px;">
                <div style="color:var(--red-600); flex-shrink:0; margin-top:2px;">
                    <x-core::icon name="alert-triangle" size="16" />
                </div>
                <div style="font-size:11.5px; color:var(--red-ink); line-height:1.4;">
                    <strong>সতর্কতা:</strong> ডাটাবেজ পুনরুদ্ধার একটি সংবেদনশীল ও অপরিবর্তনীয় প্রক্রিয়া। নির্বাচিত স্কোপের বর্তমান ডাটা ওভাররাইট হয়ে যাবে।
                </div>
            </div>

            {{-- Security Confirmation Input --}}
            <div style="margin-bottom:20px;">
                <div style="font-size:12.5px; font-weight:600; color:var(--ink-800); margin-bottom:4px;">
                    <span class="bn">নিরাপত্তা নিশ্চিতকরণ:</span>
                    <span class="en" style="display:none;">Security Confirmation:</span>
                </div>
                <div style="font-size:11.5px; color:var(--ink-500); margin-bottom:8px;">
                    <span class="bn">নিশ্চিত করতে <code>RESTORE</code> লিখুন অথবা আপনার সুপার এডমিন পাসওয়ার্ড দিন:</span>
                    <span class="en" style="display:none;">Type <code>RESTORE</code> or enter your Super Admin password to confirm:</span>
                </div>
                <x-core::input
                    type="text"
                    id="restore_confirm_input"
                    name="confirm_text"
                    size="sm"
                    :no-margin="true"
                    placeholder="RESTORE"
                />
            </div>

            {{-- Modal Footer --}}
            <div style="display:flex; align-items:center; justify-content:flex-end; gap:8px; padding-top:12px; border-top:1px solid var(--border);">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    class="modal-close-btn"
                >
                    <span class="bn">বাতিল</span>
                    <span class="en" style="display:none;">Cancel</span>
                </x-core::button>

                <x-core::button
                    type="button"
                    color="danger"
                    size="sm"
                    icon="rotate-ccw"
                    id="btn-submit-restore"
                >
                    <span class="bn">পুনরুদ্ধার নিশ্চিত করুন</span>
                    <span class="en" style="display:none;">Confirm Restore</span>
                </x-core::button>
            </div>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}
        <script>
            $(function() {
                var isBangla = function() {
                    return !$('html').hasClass('lang-en');
                };

                // State variables for restore modal
                var currentRestoreUrl = '';
                var currentInspectUrl = '';
                var currentBackupFilename = '';

                // Create Backup Button Click
                $('#btn-create-backup').on('click', function() {
                    var isBn = isBangla();

                    Swal.fire({
                        title: isBn ? 'নতুন ডাটাবেজ ব্যাকআপ তৈরি করবেন?' : 'Create Database Backup?',
                        text: isBn 
                            ? 'এটি সম্পূর্ণ ডাটাবেজ এবং প্রতিটি দোকানের আইসোলেটেড ডাটা প্যাকেজ তৈরি করবে।' 
                            : 'This will generate a complete database and shop-scoped backup package.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#0d9488',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: isBn ? '<i class="app-icon"></i> হ্যাঁ, ব্যাকআপ নিন' : 'Yes, Backup Now',
                        cancelButtonText: isBn ? 'বাতিল' : 'Cancel',
                        reverseButtons: true,
                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: isBn ? 'ডাটাবেজ ব্যাকআপ তৈরি হচ্ছে...' : 'Generating Database Backup...',
                                text: isBn 
                                    ? 'অনুগ্রহ করে অপেক্ষা করুন, এটি কয়েক সেকেন্ড সময় নিতে পারে।' 
                                    : 'Please wait, this may take a few seconds.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a',
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: "{{ route('backup.store') }}",
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                dataType: 'json',
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: isBn ? 'সফল!' : 'Success!',
                                        text: response.message || (isBn ? 'ডাটাবেজ ব্যাকআপ সফলভাবে তৈরি হয়েছে।' : 'Database backup created successfully.'),
                                        timer: 3000,
                                        showConfirmButton: true,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    });

                                    if (window.LaravelDataTables && window.LaravelDataTables['backup-data-table']) {
                                        window.LaravelDataTables['backup-data-table'].ajax.reload(null, false);
                                    } else {
                                        $('#backup-data-table').DataTable().ajax.reload(null, false);
                                    }
                                },
                                error: function(xhr) {
                                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) 
                                        ? xhr.responseJSON.message 
                                        : (isBn ? 'ডাটাবেজ ব্যাকআপ তৈরিতে সমস্যা হয়েছে।' : 'Failed to generate database backup.');

                                    Swal.fire({
                                        icon: 'error',
                                        title: isBn ? 'ত্রুটি' : 'Error',
                                        text: errorMsg,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    });
                                }
                            });
                        }
                    });
                });

                // Reload Table Button Click
                $('#btn-reload-backup-table').on('click', function() {
                    if (window.LaravelDataTables && window.LaravelDataTables['backup-data-table']) {
                        window.LaravelDataTables['backup-data-table'].ajax.reload(null, false);
                    } else {
                        $('#backup-data-table').DataTable().ajax.reload(null, false);
                    }
                });

                // Open Restore Modal
                $(document).on('click', '.btn-open-restore-modal', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    currentRestoreUrl = $btn.data('restore-url');
                    currentInspectUrl = $btn.data('inspect-url');
                    currentBackupFilename = $btn.data('filename');

                    $('#restore_file_name').text(currentBackupFilename);
                    $('#restore_file_meta').text($btn.data('size') + ' · ' + $btn.data('date'));
                    $('#restore_confirm_input').val('');

                    // Reset to full mode by default
                    $('#mode_full').prop('checked', true);
                    $('.restore-mode-card').removeClass('active');
                    $('#label_mode_full').addClass('active');
                    $('#shop_selection_container').hide();

                    $('#restore_shops_list').html('<div style="font-size:12px; color:var(--ink-500); text-align:center; padding:10px;">দোকান তালিকা লোড হচ্ছে...</div>');
                    $('#restoreModal').addClass('open').css('display', 'flex');

                    // Reset multiselect dropdown state
                    $('#shop_dropdown_menu').hide();
                    $('#shop_dropdown_chevron').css('transform', 'rotate(0deg)');
                    $('#shop_dropdown_trigger').removeClass('active');
                    $('#shop_search_input').val('');
                    $('#shop_selection_count').text(isBangla() ? '০টি নির্বাচিত (0 selected)' : '0 selected');

                    // Fetch manifest/inspect info
                    $.ajax({
                        url: currentInspectUrl,
                        type: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            if (res.success && res.data) {
                                var data = res.data;
                                if (data.can_restore_shops && data.shops && data.shops.length > 0) {
                                    $('#label_mode_shops').removeClass('disabled').css('opacity', '1');
                                    var shopsHtml = '';
                                    $.each(data.shops, function(index, shop) {
                                        var phoneStr = shop.phone ? ' · ' + shop.phone : '';
                                        var searchKey = (shop.name + ' ' + shop.id + ' ' + (shop.phone || '')).toLowerCase();
                                        shopsHtml += '<label class="shop-dropdown-item" data-search="' + searchKey + '">'
                                            + '<input type="checkbox" class="shop-item-check" value="' + shop.id + '" data-name="' + shop.name + '" style="accent-color:var(--teal-600);">'
                                            + '<div style="flex:1; display:flex; align-items:center; justify-content:space-between; gap:6px;">'
                                            + '<span style="font-weight:600; color:var(--ink-900);">' + shop.name + '</span>'
                                            + '<span style="color:var(--ink-400); font-size:11px;">(ID: #' + shop.id + phoneStr + ')</span>'
                                            + '</div>'
                                            + '</label>';
                                    });
                                    $('#restore_shops_list').html(shopsHtml);
                                    updateShopDropdownUI();
                                } else {
                                    $('#label_mode_shops').addClass('disabled').css('opacity', '0.5');
                                    $('#restore_shops_list').html('<div style="font-size:11.5px; color:var(--ink-400); text-align:center; padding:8px;">এই ব্যাকআপ ফাইলে নির্দিষ্ট দোকান তথ্য পাওয়া যায়নি (শুধু সম্পূর্ণ ডাটাবেজ সম্ভব)।</div>');
                                    updateShopDropdownUI();
                                }
                            }
                        },
                        error: function() {
                            $('#restore_shops_list').html('<div style="font-size:12px; color:var(--red-600); text-align:center; padding:10px;">দোকান তালিকা লোড করা যায়নি।</div>');
                        }
                    });
                });

                // Update Multiple Dropdown UI Helper
                var updateShopDropdownUI = function() {
                    var isBn = isBangla();
                    var $checked = $('.shop-item-check:checked');
                    var count = $checked.length;
                    var $labels = $('#shop_dropdown_selected_labels');
                    var $badge = $('#shop_selected_badge');
                    var $countText = $('#shop_selection_count');

                    var countLabel = isBn ? count + 'টি নির্বাচিত' : count + ' selected';
                    $countText.text(countLabel);

                    $('.shop-dropdown-item').removeClass('selected');
                    $checked.each(function() {
                        $(this).closest('.shop-dropdown-item').addClass('selected');
                    });

                    if (count === 0) {
                        $labels.html('<span class="placeholder-text" style="color:var(--ink-400); font-size:12.5px;">' + (isBn ? 'দোকান ড্রপডাউন থেকে নির্বাচন করুন...' : 'Select shop(s) from dropdown...') + '</span>');
                        $badge.hide();
                    } else if (count === 1) {
                        var singleName = $checked.first().data('name') || $checked.first().val();
                        var singleId = $checked.first().val();
                        $labels.html('<span class="multiselect-pill">' + singleName + ' <span class="remove-shop-pill" data-shop-id="' + singleId + '">&times;</span></span>');
                        $badge.text(countLabel).show();
                    } else if (count === 2) {
                        var pillsHtml = '';
                        $checked.each(function() {
                            var sName = $(this).data('name') || $(this).val();
                            var sId = $(this).val();
                            pillsHtml += '<span class="multiselect-pill">' + sName + ' <span class="remove-shop-pill" data-shop-id="' + sId + '">&times;</span></span>';
                        });
                        $labels.html(pillsHtml);
                        $badge.text(countLabel).show();
                    } else {
                        var firstTwo = '';
                        $checked.slice(0, 2).each(function() {
                            var sName = $(this).data('name') || $(this).val();
                            var sId = $(this).val();
                            firstTwo += '<span class="multiselect-pill">' + sName + ' <span class="remove-shop-pill" data-shop-id="' + sId + '">&times;</span></span>';
                        });
                        var remaining = count - 2;
                        firstTwo += '<span class="multiselect-pill" style="background:var(--paper-line); color:var(--ink-700); border-color:var(--border);">+' + remaining + ' ' + (isBn ? 'আরও' : 'more') + '</span>';
                        $labels.html(firstTwo);
                        $badge.text(countLabel).show();
                    }
                };

                // Dropdown Trigger Click
                $('#shop_dropdown_trigger').on('click', function(e) {
                    if ($(e.target).closest('.remove-shop-pill').length) {
                        return;
                    }
                    var $menu = $('#shop_dropdown_menu');
                    var isOpen = $menu.is(':visible');
                    if (isOpen) {
                        $menu.slideUp(120);
                        $('#shop_dropdown_chevron').css('transform', 'rotate(0deg)');
                        $('#shop_dropdown_trigger').removeClass('active');
                    } else {
                        $menu.slideDown(120, function() {
                            $('#shop_search_input').trigger('focus');
                        });
                        $('#shop_dropdown_chevron').css('transform', 'rotate(180deg)');
                        $('#shop_dropdown_trigger').addClass('active');
                    }
                });

                // Remove Shop Pill Click
                $(document).on('click', '.remove-shop-pill', function(e) {
                    e.stopPropagation();
                    var shopId = $(this).data('shop-id');
                    $('.shop-item-check[value="' + shopId + '"]').prop('checked', false);
                    updateShopDropdownUI();
                });

                // Checkbox state change inside dropdown
                $(document).on('change', '.shop-item-check', function() {
                    updateShopDropdownUI();
                });

                // Instant Search in Dropdown
                $('#shop_search_input').on('keyup input', function() {
                    var q = $.trim($(this).val()).toLowerCase();
                    var matched = 0;
                    $('.shop-dropdown-item').each(function() {
                        var searchStr = $(this).data('search') || '';
                        if (!q || searchStr.indexOf(q) > -1) {
                            $(this).show();
                            matched++;
                        } else {
                            $(this).hide();
                        }
                    });

                    if (matched === 0) {
                        if ($('#shop_dropdown_no_results').length === 0) {
                            $('#restore_shops_list').append('<div id="shop_dropdown_no_results" style="font-size:12px; color:var(--ink-400); text-align:center; padding:12px;">কোনো দোকান পাওয়া যায়নি (No shops found)</div>');
                        }
                    } else {
                        $('#shop_dropdown_no_results').remove();
                    }
                });

                // Select All Button in Dropdown
                $('#btn_multiselect_select_all').on('click', function(e) {
                    e.preventDefault();
                    $('.shop-dropdown-item:visible .shop-item-check').prop('checked', true);
                    updateShopDropdownUI();
                });

                // Clear All Button in Dropdown
                $('#btn_multiselect_clear_all').on('click', function(e) {
                    e.preventDefault();
                    $('.shop-item-check').prop('checked', false);
                    updateShopDropdownUI();
                });

                // Close Dropdown when clicking outside
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('#shop_multiselect_dropdown').length) {
                        $('#shop_dropdown_menu').slideUp(100);
                        $('#shop_dropdown_chevron').css('transform', 'rotate(0deg)');
                        $('#shop_dropdown_trigger').removeClass('active');
                    }
                });

                // Mode radio card clicks
                $('input[name="restore_mode"]').on('change', function() {
                    $('.restore-mode-card').removeClass('active');
                    $(this).closest('.restore-mode-card').addClass('active');

                    if ($(this).val() === 'shops') {
                        $('#shop_selection_container').slideDown(150, function() {
                            $('#shop_dropdown_menu').slideDown(120);
                            $('#shop_dropdown_chevron').css('transform', 'rotate(180deg)');
                            $('#shop_dropdown_trigger').addClass('active');
                        });
                    } else {
                        $('#shop_selection_container').slideUp(150);
                    }
                });

                // Close Restore Modal
                $('.modal-close-btn').on('click', function() {
                    $('#restoreModal').removeClass('open').css('display', 'none');
                });

                // Close Restore Modal on backdrop click
                $('#restoreModal').on('click', function(e) {
                    if ($(e.target).is('#restoreModal')) {
                        $(this).removeClass('open').css('display', 'none');
                    }
                });

                // Close on ESC key
                $(document).on('keydown', function(e) {
                    if (e.key === 'Escape' && $('#restoreModal').hasClass('open')) {
                        $('#restoreModal').removeClass('open').css('display', 'none');
                    }
                });

                // Submit Restore
                $('#btn-submit-restore').on('click', function() {
                    var isBn = isBangla();
                    var mode = $('input[name="restore_mode"]:checked').val();
                    var confirmText = $.trim($('#restore_confirm_input').val());

                    if (!confirmText) {
                        Swal.fire({
                            icon: 'warning',
                            title: isBn ? 'নিরাপত্তা নিশ্চিতকরণ প্রয়োজন' : 'Confirmation Required',
                            text: isBn ? 'অনুগ্রহ করে "RESTORE" লিখুন অথবা পাসওয়ার্ড প্রদান করুন।' : 'Please type "RESTORE" or enter your password to proceed.'
                        });
                        return;
                    }

                    var selectedShopIds = [];
                    if (mode === 'shops') {
                        $('.shop-item-check:checked').each(function() {
                            selectedShopIds.push($(this).val());
                        });

                        if (selectedShopIds.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: isBn ? 'দোকান নির্বাচন করুন' : 'Select Shop',
                                text: isBn ? 'অনুগ্রহ করে অন্তত একটি দোকান নির্বাচন করুন।' : 'Please select at least one shop to restore.'
                            });
                            return;
                        }
                    }

                    var confirmPromptText = mode === 'shops'
                        ? (isBn ? 'নির্বাচিত ' + selectedShopIds.length + 'টি দোকানের ডাটা রিস্টোর করা হবে।' : selectedShopIds.length + ' shop(s) will be restored.')
                        : (isBn ? 'সম্পূর্ণ ডাটাবেজ ওভাররাইট হয়ে এই ব্যাকআপের অবস্থায় ফিরে যাবে।' : 'Entire database will be reverted to this backup.');

                    Swal.fire({
                        title: isBn ? 'আপনি কি নিশ্চিত?' : 'Are you sure?',
                        text: confirmPromptText,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: isBn ? 'হ্যাঁ, রিস্টোর শুরু করুন' : 'Yes, Start Restore',
                        cancelButtonText: isBn ? 'বাতিল' : 'Cancel',
                        reverseButtons: true,
                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            $('#restoreModal').removeClass('open').css('display', 'none');

                            Swal.fire({
                                title: isBn ? 'ডাটাবেজ পুনরুদ্ধার হচ্ছে...' : 'Restoring Database...',
                                text: isBn ? 'অনুগ্রহ করে অপেক্ষা করুন, প্রক্রিয়া চলাকালীন ব্রাউজার বন্ধ করবেন না।' : 'Please wait, do not close or refresh the browser.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a',
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: currentRestoreUrl,
                                type: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    mode: mode,
                                    shop_ids: selectedShopIds,
                                    confirm_text: confirmText
                                },
                                dataType: 'json',
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: isBn ? 'পুনরুদ্ধার সফল!' : 'Restore Completed!',
                                        text: response.message || (isBn ? 'ডাটাবেজ সফলভাবে পুনরুদ্ধার করা হয়েছে।' : 'Database restored successfully.'),
                                        timer: 3500,
                                        showConfirmButton: true,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    }).then(function() {
                                        window.location.reload();
                                    });
                                },
                                error: function(xhr) {
                                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) 
                                        ? xhr.responseJSON.message 
                                        : (isBn ? 'পুনরুদ্ধার প্রক্রিয়া ব্যর্থ হয়েছে।' : 'Restore process failed.');

                                    Swal.fire({
                                        icon: 'error',
                                        title: isBn ? 'ব্যর্থ হয়েছে' : 'Failed',
                                        text: errorMsg,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    });
                                }
                            });
                        }
                    });
                });

                // Delete Backup Action
                $(document).on('click', '.btn-delete-backup', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');
                    var filename = $(this).data('filename');
                    var isBn = isBangla();

                    Swal.fire({
                        title: isBn ? 'ব্যাকআপ ডিলিট করবেন?' : 'Delete Backup File?',
                        text: (isBn ? 'ফাইল: ' : 'File: ') + filename + (isBn ? ' স্থায়ীভাবে মুছে যাবে!' : ' will be permanently deleted!'),
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: isBn ? 'হ্যাঁ, মুছে ফেলুন' : 'Yes, Delete',
                        cancelButtonText: isBn ? 'বাতিল' : 'Cancel',
                        reverseButtons: true,
                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: isBn ? 'মুছে ফেলা হচ্ছে...' : 'Deleting...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a',
                                didOpen: function() {
                                    Swal.showLoading();
                                }
                            });

                            $.ajax({
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                dataType: 'json',
                                success: function(response) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: isBn ? 'মুছে ফেলা হয়েছে!' : 'Deleted!',
                                        text: response.message || (isBn ? 'ব্যাকআপ ফাইলটি সফলভাবে মুছে ফেলা হয়েছে।' : 'Backup file deleted successfully.'),
                                        timer: 2000,
                                        showConfirmButton: false,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    });

                                    if (window.LaravelDataTables && window.LaravelDataTables['backup-data-table']) {
                                        window.LaravelDataTables['backup-data-table'].ajax.reload(null, false);
                                    } else {
                                        $('#backup-data-table').DataTable().ajax.reload(null, false);
                                    }
                                },
                                error: function(xhr) {
                                    var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) 
                                        ? xhr.responseJSON.message 
                                        : (isBn ? 'ফাইলটি মুছতে ব্যর্থ হয়েছে।' : 'Failed to delete backup file.');

                                    Swal.fire({
                                        icon: 'error',
                                        title: isBn ? 'ব্যর্থ হয়েছে' : 'Failed',
                                        text: errorMsg,
                                        background: $('html').attr('data-theme') === 'dark' ? '#111827' : '#ffffff',
                                        color: $('html').attr('data-theme') === 'dark' ? '#f8fafc' : '#0f172a'
                                    });
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endpush
</x-core::layout>
