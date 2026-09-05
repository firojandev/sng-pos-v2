<x-core::layout
    title="ইউজার ব্যবস্থাপনা"
    title-en="Users Management"
    subtitle="সিস্টেম লগইন অ্যাকাউন্ট ও অ্যাক্সেস রোল পরিচালনা করুন"
    subtitle-en="Manage system login accounts and access roles"
    active="users"
>
    <x-user::tabbar active="users" />

    {{-- Executive Summary Stat Grid --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="users"
                color="teal"
                :value="number_format($metrics['totalUsers'])"
                label="সর্বমোট ইউজার"
                label-en="Total Users"
                subtext="সিস্টেম লগইন অ্যাকাউন্ট"
                subtext-en="System login accounts"
            />

            <x-core::stat-card
                icon="shield"
                color="blue"
                :value="number_format($metrics['adminUsers'])"
                label="অ্যাডমিন অ্যাকাউন্ট"
                label-en="Admin Accounts"
                subtext="পূর্ণ নিয়ন্ত্রণ ও ক্ষমতা"
                subtext-en="Full system access"
            />

            <x-core::stat-card
                icon="user-check"
                color="green"
                value-color="green"
                :value="number_format($metrics['staffUsers'])"
                label="অন্যান্য স্টাফ ও কর্মী"
                label-en="Staff / Operators"
                subtext="নির্দিষ্ট রোল ও দায়িত্ব"
                subtext-en="Role-assigned accounts"
            />

            <x-core::stat-card
                icon="zap"
                color="gold"
                :value="$metrics['userLimitText']"
                label="প্ল্যান ক্যাপাসিটি"
                label-en="Plan Capacity"
                :subtext="$metrics['remainingSlotsText']"
            />
        </div>
    @endif

    @php
        $roleFilterOptions = ['' => 'সকল রোল (All Roles)'];
        foreach ($roles as $r) {
            $roleFilterOptions[$r->name] = $r->name;
        }

        $statusFilterOptions = [
            '' => 'সকল অবস্থা (All Status)',
            'verified' => 'ভেরিফাইড (Verified)',
            'unverified' => 'অযাচাইকৃত (Unverified)',
        ];
    @endphp

    {{-- Filter Toolbar & Action Buttons --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-role"
                    name="filter_role"
                    size="sm"
                    :no-margin="true"
                    :options="$roleFilterOptions"
                />
            </div>

            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    id="filter-status"
                    name="filter_status"
                    size="sm"
                    :no-margin="true"
                    :options="$statusFilterOptions"
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
                title="রিসেট / Reset"
            >
                <span class="bn">রিসেট</span>
                <span class="en" style="display:none;">Reset</span>
            </x-core::button>
        </div>

        <x-core::button
            type="button"
            color="primary"
            size="sm"
            icon="plus"
            id="btn-open-create-user-modal"
        >
            <span class="bn">নতুন ইউজার</span>
            <span class="en" style="display:none;">New User</span>
        </x-core::button>
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'users-data-table']) !!}
        </div>
    </div>

    {{-- Create User Modal --}}
    <div class="modal-backdrop" id="createUserModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:16px;">
        <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="user-plus" size="18" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">নতুন ইউজার তৈরি করুন</span>
                            <span class="en" style="display:none;">Create New User</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);">
                            <span class="bn">লগইন ক্রেডেনশিয়াল ও পারমিশন রোল নির্ধারণ করুন</span>
                            <span class="en" style="display:none;">Assign login credentials and permission role</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-400); border-radius:6px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <form method="POST" action="{{ route('users.store') }}" id="create_user_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="create_user_name"
                        label="পুরো নাম"
                        label-en="Full Name"
                        placeholder="যেমন: মোঃ সাকিব চৌধুরী"
                        placeholder-en="e.g. Md. Sakib Chowdhury"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="email"
                        id="create_user_email"
                        type="email"
                        label="ইমেইল অ্যাড্রেস"
                        label-en="Email Address"
                        placeholder="user@example.com"
                        placeholder-en="user@example.com"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="password"
                            id="create_user_password"
                            type="password"
                            label="পাসওয়ার্ড"
                            label-en="Password"
                            placeholder="কমপক্ষে ৮ অক্ষর"
                            placeholder-en="Min 8 characters"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="password_confirmation"
                            id="create_user_password_confirmation"
                            type="password"
                            label="পাসওয়ার্ড নিশ্চিত করুন"
                            label-en="Confirm Password"
                            placeholder="পুনরায় লিখুন"
                            placeholder-en="Re-type password"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    @php
                        $createRoleOptions = ['' => '-- রোল নির্বাচন করুন --'];
                        foreach ($roles as $role) {
                            $createRoleOptions[$role->name] = $role->name;
                        }
                    @endphp
                    <x-core::select
                        name="role"
                        id="create_user_role"
                        label="অ্যাক্সেস রোল"
                        label-en="Access Role"
                        size="sm"
                        :required="true"
                        :options="$createRoleOptions"
                    />

                    <div style="font-size:11.5px; color:var(--ink-500); background:var(--paper-line); padding:8px 12px; border-radius:8px; border:1px solid var(--border);">
                        <span class="bn">নতুন কোনো রোল প্রয়োজন হলে <strong>"রোল ও পারমিশন"</strong> ট্যাব থেকে তৈরি করতে পারেন।</span>
                        <span class="en" style="display:none;">Need a new role? Create one anytime from the <strong>"Roles & Permissions"</strong> tab.</span>
                    </div>
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
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
                        type="submit"
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-save-create-user"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save User</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit User Modal --}}
    <div class="modal-backdrop" id="editUserModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:16px;">
        <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:520px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">ইউজার তথ্য সম্পাদনা</span>
                            <span class="en" style="display:none;">Edit User Details</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);">
                            <span class="bn">ইউজারের নাম, ইমেইল অথবা পাসওয়ার্ড পরিবর্তন করুন</span>
                            <span class="en" style="display:none;">Update name, email, or change password</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-400); border-radius:6px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <form method="POST" action="" id="edit_user_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="name"
                        id="edit_user_name"
                        label="পুরো নাম"
                        label-en="Full Name"
                        placeholder="ইউজারের নাম লিখুন"
                        size="sm"
                        :required="true"
                    />

                    <x-core::input
                        name="email"
                        id="edit_user_email"
                        type="email"
                        label="ইমেইল অ্যাড্রেস"
                        label-en="Email Address"
                        placeholder="user@example.com"
                        size="sm"
                        :required="true"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="password"
                            id="edit_user_password"
                            type="password"
                            label="নতুন পাসওয়ার্ড (ঐচ্ছিক)"
                            label-en="New Password (Optional)"
                            placeholder="••••••••"
                            size="sm"
                        />

                        <x-core::input
                            name="password_confirmation"
                            id="edit_user_password_confirmation"
                            type="password"
                            label="পাসওয়ার্ড নিশ্চিত করুন"
                            label-en="Confirm Password"
                            placeholder="••••••••"
                            size="sm"
                        />
                    </div>

                    <div style="font-size:11px; color:var(--ink-500); margin-top:-6px;">
                        <span class="bn">পাসওয়ার্ড পরিবর্তন করতে না চাইলে ঘরটি খালি রাখুন।</span>
                        <span class="en" style="display:none;">Leave blank if you do not wish to change the password.</span>
                    </div>

                    <x-core::select
                        name="role"
                        id="edit_user_role"
                        label="অ্যাক্সেস রোল"
                        label-en="Access Role"
                        size="sm"
                        :required="true"
                        :options="$createRoleOptions"
                    />
                </div>

                <div style="margin-top:20px; padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
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
                        type="submit"
                        color="primary"
                        size="sm"
                        icon="check"
                        id="btn-save-edit-user"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update User</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            var tableId = 'users-data-table';

            function getTable() {
                return window.LaravelDataTables ? window.LaravelDataTables[tableId] : null;
            }

            // Real-time table filters
            $('#filter-role, #filter-status, #filter-date-from, #filter-date-to').on('change', function () {
                var table = getTable();
                if (table) {
                    table.draw();
                }
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function () {
                $('#filter-role').val('');
                $('#filter-status').val('');
                $('#filter-date-from').val('');
                $('#filter-date-to').val('');

                var table = getTable();
                if (table) {
                    table.draw();
                }
            });

            // Modal helpers
            function showModal($modal) {
                $modal.css('display', 'flex');
                $('body').css('overflow', 'hidden');
            }

            function closeModal($modal) {
                $modal.css('display', 'none');
                $('body').css('overflow', '');
                $modal.find('.form-error-msg').remove();
                $modal.find('.is-invalid').removeClass('is-invalid');
            }

            // Open Create User Modal
            $('#btn-open-create-user-modal').on('click', function () {
                var $form = $('#create_user_form');
                $form[0].reset();
                showModal($('#createUserModal'));
                setTimeout(function () {
                    $('#create_user_name').focus();
                }, 100);
            });

            // Close modals
            $(document).on('click', '.modal-close-btn', function () {
                closeModal($(this).closest('.modal-backdrop'));
            });

            $(document).on('click', '.modal-backdrop', function (e) {
                if ($(e.target).is('.modal-backdrop')) {
                    closeModal($(this));
                }
            });

            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    closeModal($('.modal-backdrop:visible'));
                }
            });

            // Create User AJAX Submission
            $('#create_user_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-user');

                $form.find('.form-error-msg').remove();
                $form.find('.is-invalid').removeClass('is-invalid');
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (res) {
                        closeModal($('#createUserModal'));
                        $form[0].reset();
                        var table = getTable();
                        if (table) {
                            table.ajax.reload(null, false);
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: res.message || 'ইউজার সফলভাবে তৈরি করা হয়েছে।',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, msgs) {
                                var $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    $field.closest('.form-input-group, .form-select-group').after(
                                        '<div class="form-error-msg" style="color:var(--red-600); font-size:11.5px; margin-top:3px;">' + msgs[0] + '</div>'
                                    );
                                }
                            });
                        } else {
                            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'অনাকাঙ্ক্ষিত ত্রুটি ঘটেছে। পুনরায় চেষ্টা করুন।';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি!',
                                    text: errorMsg
                                });
                            } else {
                                alert(errorMsg);
                            }
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Open Edit User Modal via AJAX
            $(document).on('click', '.btn-edit-user', function (e) {
                e.preventDefault();
                var editUrl = $(this).attr('href');

                $.ajax({
                    url: editUrl,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (data) {
                        var user = data.user;
                        var $form = $('#edit_user_form');

                        $form.attr('action', data.update_url);
                        $('#edit_user_name').val(user.name);
                        $('#edit_user_email').val(user.email);
                        $('#edit_user_password').val('');
                        $('#edit_user_password_confirmation').val('');
                        $('#edit_user_role').val(user.role);

                        showModal($('#editUserModal'));
                    },
                    error: function () {
                        // Fallback to direct navigation if AJAX fails
                        window.location.href = editUrl;
                    }
                });
            });

            // Edit User AJAX Submission
            $('#edit_user_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-edit-user');

                $form.find('.form-error-msg').remove();
                $form.find('.is-invalid').removeClass('is-invalid');
                $btn.prop('disabled', true);

                $.ajax({
                    url: $form.attr('action'),
                    method: 'POST',
                    data: $form.serialize(),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (res) {
                        closeModal($('#editUserModal'));
                        var table = getTable();
                        if (table) {
                            table.ajax.reload(null, false);
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'হালনাগাদ সম্পন্ন!',
                                text: res.message || 'ইউজারের তথ্য সফলভাবে পরিবর্তন করা হয়েছে।',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function (field, msgs) {
                                var $field = $form.find('[name="' + field + '"]');
                                if ($field.length) {
                                    $field.addClass('is-invalid');
                                    $field.closest('.form-input-group, .form-select-group').after(
                                        '<div class="form-error-msg" style="color:var(--red-600); font-size:11.5px; margin-top:3px;">' + msgs[0] + '</div>'
                                    );
                                }
                            });
                        } else {
                            var errorMsg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'অনাকাঙ্ক্ষিত ত্রুটি ঘটেছে। পুনরায় চেষ্টা করুন।';
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ত্রুটি!',
                                    text: errorMsg
                                });
                            } else {
                                alert(errorMsg);
                            }
                        }
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // SweetAlert2 interceptor for delete forms in DataTable
            $(document).on('submit', '.delete-form', function (e) {
                var $form = $(this);
                if ($form.data('confirmed')) {
                    return true;
                }

                e.preventDefault();
                var title = $form.data('title') || 'আপনি কি নিশ্চিত?';
                var text = $form.data('text') || 'এই রেকর্ডটি মুছে ফেলা হবে!';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc2626',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'হ্যাঁ, মুছে ফেলুন',
                        cancelButtonText: 'বাতিল',
                        reverseButtons: true
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: $form.attr('action'),
                                method: 'POST',
                                data: $form.serialize(),
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                success: function (res) {
                                    var table = getTable();
                                    if (table) {
                                        table.ajax.reload(null, false);
                                    }
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'মুছে ফেলা হয়েছে!',
                                        text: res.message || 'ইউজার সফলভাবে মুছে ফেলা হয়েছে।',
                                        timer: 1800,
                                        showConfirmButton: false
                                    });
                                },
                                error: function (xhr) {
                                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'ইউজার মুছে ফেলা সম্ভব হয়নি।';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'ব্যর্থ হয়েছে!',
                                        text: msg
                                    });
                                }
                            });
                        }
                    });
                } else if (confirm(title)) {
                    $form.data('confirmed', true).submit();
                }
            });
        });
        </script>
    @endpush
</x-core::layout>
