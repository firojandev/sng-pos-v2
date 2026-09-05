<x-core::layout
    title="কর্মচারী ব্যবস্থাপনা"
    title-en="Employees Management"
    subtitle="দোকানের সকল কর্মচারীদের তথ্য, পদবি ও বেতন পরিচালনা করুন"
    subtitle-en="Manage shop employee records, designations, and payroll"
    active="employees"
>
    {{-- Executive Summary Stat Grid --}}
    @if (isset($metrics))
        <div class="stat-grid" style="margin-bottom:16px;">
            <x-core::stat-card
                icon="users"
                color="teal"
                :value="number_format($metrics['totalEmployees'])"
                label="সর্বমোট কর্মচারী"
                label-en="Total Employees"
                subtext="দোকানে নিবন্ধিত কর্মী"
                subtext-en="Registered staff members"
            />

            <x-core::stat-card
                icon="user-check"
                color="green"
                value-color="green"
                :value="number_format($metrics['activeEmployees'])"
                label="সক্রিয় কর্মচারী"
                label-en="Active Employees"
                subtext="বর্তমানে কর্মরত কর্মী"
                subtext-en="Currently active on duty"
            />

            <x-core::stat-card
                icon="credit-card"
                color="blue"
                value-color="blue"
                :value="'৳' . number_format($metrics['totalSalary'], 2)"
                label="মাসিক মোট বেতন"
                label-en="Monthly Payroll"
                subtext="সক্রিয় কর্মীদের বেতন বরাদ্দ"
                subtext-en="Active staff payroll"
            />

            <x-core::stat-card
                icon="briefcase"
                color="gold"
                :value="number_format($metrics['departmentsCount'])"
                label="মোট বিভাগ"
                label-en="Departments"
                subtext="কর্মরত বিভিন্ন বিভাগ"
                subtext-en="Operating departments"
            />
        </div>
    @endif

    @php
        $statusOptions = [
            '' => 'সকল অবস্থা (All Status)',
            'active' => 'সক্রিয় (Active)',
            'inactive' => 'নিষ্ক্রিয় (Inactive)',
        ];

        $deptOptions = ['' => 'সকল বিভাগ (All Departments)'];
        foreach ($departments as $dept) {
            $deptOptions[$dept] = $dept;
        }

        $desigOptions = ['' => 'সকল পদবি (All Designations)'];
        foreach ($designations as $desig) {
            $desigOptions[$desig] = $desig;
        }

        $userSelectOptions = ['' => '-- কোনো অ্যাকাউন্ট সংযুক্ত নেই --'];
        foreach ($users as $u) {
            $userSelectOptions[$u->id] = $u->name . ' (' . $u->email . ')';
        }
    @endphp

    {{-- Filter Toolbar & Action Buttons --}}
    <div class="section-row" style="margin-bottom:16px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
        <div class="filters" style="display:flex; align-items:center; flex-wrap:nowrap; gap:8px; overflow-x:auto; max-width:100%; padding-bottom:2px;">
            <div style="width:160px; flex-shrink:0;">
                <x-core::select
                    id="filter-status"
                    name="filter_status"
                    size="sm"
                    :no-margin="true"
                    :options="$statusOptions"
                />
            </div>

            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-department"
                    name="filter_department"
                    size="sm"
                    :no-margin="true"
                    :options="$deptOptions"
                />
            </div>

            <div style="width:170px; flex-shrink:0;">
                <x-core::select
                    id="filter-designation"
                    name="filter_designation"
                    size="sm"
                    :no-margin="true"
                    :options="$desigOptions"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-from"
                    name="filter_date_from"
                    size="sm"
                    :no-margin="true"
                    placeholder="যোগদান হতে"
                    title="যোগদানের তারিখ হতে / Joining From"
                />
            </div>

            <div style="width:140px; flex-shrink:0;">
                <x-core::input
                    type="date"
                    id="filter-date-to"
                    name="filter_date_to"
                    size="sm"
                    :no-margin="true"
                    placeholder="যোগদান পর্যন্ত"
                    title="যোগদানের তারিখ পর্যন্ত / Joining To"
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
            id="btn-open-create-employee-modal"
        >
            <span class="bn">নতুন কর্মচারী</span>
            <span class="en" style="display:none;">New Employee</span>
        </x-core::button>
    </div>

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'employees-data-table']) !!}
        </div>
    </div>

    {{-- Create Employee Modal --}}
    <div class="modal-backdrop" id="createEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:16px;">
        <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:620px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="user-plus" size="18" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">নতুন কর্মচারী যোগ করুন</span>
                            <span class="en" style="display:none;">Add New Employee</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);">
                            <span class="bn">কর্মচারীর ব্যক্তিগত, যোগাযোগের ও বেতনের তথ্য লিখুন</span>
                            <span class="en" style="display:none;">Enter personal, contact, and salary details</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-400); border-radius:6px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <form method="POST" action="{{ route('employees.store') }}" id="create_employee_form">
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="name"
                            id="create_employee_name"
                            label="নাম"
                            label-en="Full Name"
                            placeholder="কর্মচারীর পুরো নাম"
                            placeholder-en="Full name"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="phone"
                            id="create_employee_phone"
                            label="মোবাইল নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            placeholder-en="01XXXXXXXXX"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <x-core::input
                        name="email"
                        id="create_employee_email"
                        type="email"
                        label="ইমেইল অ্যাড্রেস"
                        label-en="Email Address"
                        placeholder="employee@example.com"
                        placeholder-en="employee@example.com"
                        size="sm"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="designation"
                            id="create_employee_designation"
                            label="পদবি"
                            label-en="Designation"
                            placeholder="যেমন: বিক্রয়কর্মী / ম্যানেজার"
                            placeholder-en="e.g. Sales / Manager"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="department"
                            id="create_employee_department"
                            label="বিভাগ"
                            label-en="Department"
                            placeholder="যেমন: বিক্রয় / হিসাব"
                            placeholder-en="e.g. Sales / Accounts"
                            size="sm"
                        />
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="salary"
                            id="create_employee_salary"
                            type="number"
                            step="0.01"
                            min="0"
                            label="মাসিক বেতন (৳)"
                            label-en="Monthly Salary (৳)"
                            placeholder="0.00"
                            prefix="৳"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="joining_date"
                            id="create_employee_joining_date"
                            type="date"
                            label="যোগদানের তারিখ"
                            label-en="Joining Date"
                            size="sm"
                        />
                    </div>

                    <x-core::input
                        name="address"
                        id="create_employee_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="কর্মচারীর বর্তমান ঠিকানা"
                        placeholder-en="Employee address"
                        size="sm"
                    />

                    <x-core::select
                        name="user_id"
                        id="create_employee_user_id"
                        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
                        label-en="Linked User Login Account (Optional)"
                        size="sm"
                        :options="$userSelectOptions"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="create_employee_status"
                            value="active"
                        />
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
                        id="btn-save-create-employee"
                    >
                        <span class="bn">সংরক্ষণ করুন</span>
                        <span class="en" style="display:none;">Save Employee</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Employee Modal --}}
    <div class="modal-backdrop" id="editEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:16px;">
        <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:620px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
            <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                        <x-core::icon name="edit" size="18" />
                    </div>
                    <div>
                        <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                            <span class="bn">কর্মচারীর তথ্য সম্পাদনা</span>
                            <span class="en" style="display:none;">Edit Employee Details</span>
                        </div>
                        <div style="font-size:12px; color:var(--ink-500);">
                            <span class="bn">তথ্য ও বেতন হালনাগাদ করুন</span>
                            <span class="en" style="display:none;">Update employee information and salary</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="modal-close-btn" style="width:28px; height:28px; font-size:18px; cursor:pointer; background:none; border:none; color:var(--ink-400); border-radius:6px; display:flex; align-items:center; justify-content:center;">&times;</button>
            </div>

            <form method="POST" action="" id="edit_employee_form">
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="name"
                            id="edit_employee_name"
                            label="নাম"
                            label-en="Full Name"
                            placeholder="কর্মচারীর পুরো নাম"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="phone"
                            id="edit_employee_phone"
                            label="মোবাইল নম্বর"
                            label-en="Phone Number"
                            placeholder="01XXXXXXXXX"
                            size="sm"
                            :required="true"
                        />
                    </div>

                    <x-core::input
                        name="email"
                        id="edit_employee_email"
                        type="email"
                        label="ইমেইল অ্যাড্রেস"
                        label-en="Email Address"
                        placeholder="employee@example.com"
                        size="sm"
                    />

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="designation"
                            id="edit_employee_designation"
                            label="পদবি"
                            label-en="Designation"
                            placeholder="যেমন: বিক্রয়কর্মী / ম্যানেজার"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="department"
                            id="edit_employee_department"
                            label="বিভাগ"
                            label-en="Department"
                            placeholder="যেমন: বিক্রয় / হিসাব"
                            size="sm"
                        />
                    </div>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <x-core::input
                            name="salary"
                            id="edit_employee_salary"
                            type="number"
                            step="0.01"
                            min="0"
                            label="মাসিক বেতন (৳)"
                            label-en="Monthly Salary (৳)"
                            placeholder="0.00"
                            prefix="৳"
                            size="sm"
                            :required="true"
                        />

                        <x-core::input
                            name="joining_date"
                            id="edit_employee_joining_date"
                            type="date"
                            label="যোগদানের তারিখ"
                            label-en="Joining Date"
                            size="sm"
                        />
                    </div>

                    <x-core::input
                        name="address"
                        id="edit_employee_address"
                        label="ঠিকানা"
                        label-en="Address"
                        placeholder="কর্মচারীর বর্তমান ঠিকানা"
                        size="sm"
                    />

                    <x-core::select
                        name="user_id"
                        id="edit_employee_user_id"
                        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
                        label-en="Linked User Login Account (Optional)"
                        size="sm"
                        :options="$userSelectOptions"
                    />

                    <div>
                        <label class="bn" style="display:block; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">অবস্থা</label>
                        <label class="en" style="display:none; margin-bottom:6px; font-weight:600; font-size:13px; color:var(--ink-800);">Status</label>
                        <x-core::status-toggle
                            name="status"
                            id="edit_employee_status"
                            value="active"
                        />
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
                        id="btn-save-edit-employee"
                    >
                        <span class="bn">হালনাগাদ করুন</span>
                        <span class="en" style="display:none;">Update Employee</span>
                    </x-core::button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}

        <script>
        $(function () {
            var tableId = 'employees-data-table';

            function getTable() {
                return window.LaravelDataTables ? window.LaravelDataTables[tableId] : null;
            }

            // Real-time table filters
            $('#filter-status, #filter-department, #filter-designation, #filter-date-from, #filter-date-to').on('change', function () {
                var table = getTable();
                if (table) {
                    table.draw();
                }
            });

            // Reset filters
            $('#btn-reset-filters').on('click', function () {
                $('#filter-status').val('');
                $('#filter-department').val('');
                $('#filter-designation').val('');
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

            // Open Create Employee Modal
            $('#btn-open-create-employee-modal').on('click', function () {
                var $form = $('#create_employee_form');
                $form[0].reset();
                showModal($('#createEmployeeModal'));
                setTimeout(function () {
                    $('#create_employee_name').focus();
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

            // Create Employee AJAX Submission
            $('#create_employee_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-create-employee');

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
                        closeModal($('#createEmployeeModal'));
                        $form[0].reset();
                        var table = getTable();
                        if (table) {
                            table.ajax.reload(null, false);
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: res.message || 'কর্মচারী সফলভাবে যোগ করা হয়েছে।',
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

            // Open Edit Employee Modal via AJAX
            $(document).on('click', '.btn-edit-employee', function (e) {
                e.preventDefault();
                var editUrl = $(this).attr('href');

                $.ajax({
                    url: editUrl,
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function (data) {
                        var employee = data.employee;
                        var $form = $('#edit_employee_form');

                        $form.attr('action', data.update_url);
                        $('#edit_employee_name').val(employee.name);
                        $('#edit_employee_phone').val(employee.phone);
                        $('#edit_employee_email').val(employee.email || '');
                        $('#edit_employee_designation').val(employee.designation);
                        $('#edit_employee_department').val(employee.department || '');
                        $('#edit_employee_salary').val(employee.salary);
                        $('#edit_employee_joining_date').val(employee.joining_date || '');
                        $('#edit_employee_address').val(employee.address || '');
                        $('#edit_employee_user_id').val(employee.user_id || '');

                        // Status toggle update
                        var statusVal = employee.status || 'active';
                        $('#edit_employee_status').val(statusVal);
                        var $toggleBox = $('#edit_employee_status').closest('.status-toggle');
                        if ($toggleBox.length) {
                            $toggleBox.find('.status-toggle-btn').removeClass('active');
                            $toggleBox.find('.status-toggle-btn[data-val="' + statusVal + '"]').addClass('active');
                        }

                        showModal($('#editEmployeeModal'));
                    },
                    error: function () {
                        window.location.href = editUrl;
                    }
                });
            });

            // Edit Employee AJAX Submission
            $('#edit_employee_form').on('submit', function (e) {
                e.preventDefault();
                var $form = $(this);
                var $btn = $('#btn-save-edit-employee');

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
                        closeModal($('#editEmployeeModal'));
                        var table = getTable();
                        if (table) {
                            table.ajax.reload(null, false);
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'হালনাগাদ সম্পন্ন!',
                                text: res.message || 'কর্মচারীর তথ্য সফলভাবে পরিবর্তন করা হয়েছে।',
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
                                        text: res.message || 'কর্মচারী সফলভাবে মুছে ফেলা হয়েছে।',
                                        timer: 1800,
                                        showConfirmButton: false
                                    });
                                },
                                error: function (xhr) {
                                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'কর্মচারী মুছে ফেলা সম্ভব হয়নি।';
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
