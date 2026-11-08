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

        @can('employees.create')
            <div style="display:flex; align-items:center; gap:8px;">
                <x-core::button
                    type="button"
                    variant="secondary"
                    size="sm"
                    icon="upload"
                    id="btn-open-import-employee-modal"
                >
                    <span class="bn">এক্সেল থেকে আপলোড</span>
                    <span class="en" style="display:none;">Import from Excel</span>
                </x-core::button>

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
        @endcan
    </div>

    {{-- Result of the last Excel upload --}}
    @if (session('import_errors'))
        <div style="margin-bottom:16px; padding:12px 14px; border:1px solid var(--red-600); background:var(--red-100); border-radius:10px;">
            <div style="font-weight:700; font-size:13px; color:var(--red-600);">
                <span class="bn">ফাইলে ভুল আছে, তাই কোনো কর্মচারী যোগ হয়নি। নিচের সারিগুলো ঠিক করে আবার আপলোড করুন।</span>
                <span class="en" style="display:none;">The file has mistakes, so nothing was imported. Correct these rows and upload it again.</span>
            </div>
            <ul style="margin:8px 0 0; padding-left:18px; font-size:12.5px; color:var(--ink-900); max-height:220px; overflow-y:auto;">
                @foreach (session('import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('import_report') && (session('import_report.without_login') || session('import_report.emails_failed')))
        <div style="margin-bottom:16px; padding:12px 14px; border:1px solid var(--border); background:var(--gold-100); border-radius:10px;">
            <div style="font-weight:700; font-size:13px; color:var(--gold-ink);">
                <span class="bn">কর্মচারীরা যোগ হয়েছেন, তবে নিচের জনদের লগইন তৈরি হয়নি। সম্পাদনা থেকে পরে লগইন তৈরি করা যাবে।</span>
                <span class="en" style="display:none;">The employees were imported, but no login was created for these. You can create one later from Edit.</span>
            </div>
            <ul style="margin:8px 0 0; padding-left:18px; font-size:12.5px; color:var(--ink-900); max-height:220px; overflow-y:auto;">
                @foreach (session('import_report.without_login', []) as $skipped)
                    <li>সারি (Row) {{ $skipped['row'] }} — {{ $skipped['name'] }}: {{ $skipped['reason'] }}</li>
                @endforeach
                @if (session('import_report.emails_failed'))
                    <li>{{ session('import_report.emails_failed') }}টি ইমেইল পাঠানো যায়নি ({{ session('import_report.emails_failed') }} emails could not be sent)।</li>
                @endif
            </ul>
        </div>
    @endif

    @error('file')
        <div style="margin-bottom:16px; color:var(--red-600); font-size:12.5px;">{{ $message }}</div>
    @enderror
    @error('user_role')
        <div style="margin-bottom:16px; color:var(--red-600); font-size:12.5px;">{{ $message }}</div>
    @enderror

    {{-- DataTable Container --}}
    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'employees-data-table']) !!}
        </div>
    </div>

    {{-- Import Employees Modal --}}
    @can('employees.create')
        <div class="modal-backdrop" id="importEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:16px;">
            <div class="modal-box" style="background:var(--card); border:1px solid var(--border); box-shadow:var(--shadow-card); width:560px; max-width:95vw; max-height:90vh; overflow-y:auto; padding:24px; border-radius:16px;">
                <div class="modal-head" style="margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div style="width:34px; height:34px; border-radius:8px; background:var(--teal-100); color:var(--teal-800); display:flex; align-items:center; justify-content:center;">
                            <x-core::icon name="upload" size="18" />
                        </div>
                        <div>
                            <div class="modal-title" style="font-size:16px; font-weight:700; color:var(--ink-900);">
                                <span class="bn">এক্সেল থেকে কর্মচারী আপলোড</span>
                                <span class="en" style="display:none;">Import Employees from Excel</span>
                            </div>
                            <div style="font-size:12px; color:var(--ink-500);">
                                <span class="bn">একসাথে অনেক কর্মচারী যোগ করুন</span>
                                <span class="en" style="display:none;">Add many employees at once</span>
                            </div>
                        </div>
                    </div>
                    <x-core::button type="button" variant="ghost" color="secondary" size="sm" icon="x" icon-only class="modal-close-btn" title="বন্ধ / Close" />
                </div>

                <form method="POST" action="{{ route('employees.import.store') }}" enctype="multipart/form-data" id="import_employee_form" style="display:flex; flex-direction:column; gap:14px;">
                    @csrf

                    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px; border:1px solid var(--border); background:var(--paper); border-radius:10px;">
                        <div style="font-size:12.5px; color:var(--ink-700);">
                            <div style="font-weight:700; color:var(--ink-900);"><span class="bn">ধাপ ১: ফরম্যাট ডাউনলোড করুন</span><span class="en" style="display:none;">Step 1: Download the format</span></div>
                            <span class="bn">ফাইলে কর্মচারীদের তথ্য লিখুন; নির্দেশনা ফাইলের ভেতরেই আছে।</span>
                            <span class="en" style="display:none;">Fill in your employees; the instructions are inside the file.</span>
                        </div>
                        <x-core::button :href="route('employees.import.template')" variant="secondary" size="sm" icon="download" style="flex-shrink:0;">
                            <span class="bn">ফরম্যাট</span>
                            <span class="en" style="display:none;">Format</span>
                        </x-core::button>
                    </div>

                    @if ($canCreateLogin)
                        <div style="border:1px solid var(--border); background:var(--paper); border-radius:10px; padding:12px;">
                            <div style="font-weight:700; font-size:12.5px; color:var(--ink-900); margin-bottom:8px;"><span class="bn">ধাপ ২: লগইন অ্যাকাউন্ট (ঐচ্ছিক)</span><span class="en" style="display:none;">Step 2: Login accounts (optional)</span></div>
                            <x-core::checkbox
                                name="create_user"
                                id="import_employee_create_user"
                                size="sm"
                                label="কর্মচারীদের লগইন অ্যাকাউন্টও তৈরি করুন"
                                label-en="Also create login accounts for the employees"
                            />
                            <div id="import_employee_login_fields" hidden>
                                <x-core::select
                                    name="user_role"
                                    id="import_employee_user_role"
                                    label="সবার জন্য ইউজার রোল"
                                    label-en="User role for everyone"
                                    size="sm"
                                    :required="$loginRoleRequired"
                                    :disabled="true"
                                    :error="false"
                                    :options="['' => $loginRoleRequired ? '-- রোল নির্বাচন করুন (Select role) --' : '-- কোনো রোল নেই (No role) --'] + $loginRoleOptions"
                                />
                                <p style="font-size:12px; color:var(--ink-600); margin:8px 0 0;">
                                    <span class="bn">ইমেইল থাকা প্রত্যেক কর্মচারীর লগইন তৈরি হবে এবং তার ইমেইলে পাসওয়ার্ড সেট করার লিংক যাবে। ইমেইল ছাড়া কর্মচারীরা যোগ হবেন, তবে লগইন ছাড়া।</span>
                                    <span class="en" style="display:none;">Every employee with an email gets a login and is emailed a link to set their password. Employees without an email are imported without a login.</span>
                                </p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <div style="font-weight:700; font-size:12.5px; color:var(--ink-900);"><span class="bn">{{ $canCreateLogin ? 'ধাপ ৩' : 'ধাপ ২' }}: পূরণ করা ফাইলটি দিন</span><span class="en" style="display:none;">{{ $canCreateLogin ? 'Step 3' : 'Step 2' }}: Choose the filled-in file</span></div>
                        <x-core::input size="sm" type="file" name="file" id="import_employee_file" accept=".xlsx,.xls,.csv" :required="true" :error="false"
                            helper="একটি সারিতেও ভুল থাকলে কিছুই যোগ হবে না; ভুলগুলো দেখানো হবে।"
                            helper-en="If any row has a mistake nothing is imported; the mistakes are listed for you." />
                    </div>

                    <div style="padding-top:14px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:flex-end; gap:8px;">
                        <x-core::button type="button" variant="secondary" size="sm" class="modal-close-btn">
                            <span class="bn">বাতিল</span>
                            <span class="en" style="display:none;">Cancel</span>
                        </x-core::button>
                        <x-core::button type="submit" color="primary" size="sm" icon="upload" id="btn-submit-import-employee">
                            <span class="bn">আপলোড করুন</span>
                            <span class="en" style="display:none;">Upload</span>
                        </x-core::button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

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

            <form method="POST" action="{{ route('employees.store') }}" id="create_employee_form" data-employee-form>
                @csrf
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="employee_code"
                        id="create_employee_code"
                        label="কর্মচারী কোড"
                        label-en="Employee Code"
                        :value="$nextEmployeeCode"
                        placeholder="EMP-0001"
                        placeholder-en="EMP-0001"
                        size="sm"
                    />
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
                        @include('employee::partials.named-select', [
                            'field' => 'designation', 'idPrefix' => 'create_employee', 'options' => $designationOptions, 'required' => true,
                            'selectedId' => null, 'newValue' => null,
                            'label' => 'পদবি', 'labelEn' => 'Designation', 'placeholder' => 'নতুন পদবির নাম', 'placeholderEn' => 'New designation name',
                        ])

                        @include('employee::partials.named-select', [
                            'field' => 'department', 'idPrefix' => 'create_employee', 'options' => $departmentOptions, 'required' => false,
                            'selectedId' => null, 'newValue' => null,
                            'label' => 'বিভাগ', 'labelEn' => 'Department', 'placeholder' => 'নতুন বিভাগের নাম', 'placeholderEn' => 'New department name',
                        ])
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

                    @include('employee::partials.login-account', ['idPrefix' => 'create_employee'])

                    <x-core::select
                        name="user_id"
                        id="create_employee_user_id"
                        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
                        label-en="Linked User Login Account (Optional)"
                        size="sm"
                        :options="$userSelectOptions"
                    />

                    <p data-linked-user-note hidden style="font-size:12px; color:var(--ink-400); margin:-8px 0 0;">
                        <span class="bn">নতুন লগইন অ্যাকাউন্টটি তৈরি হয়ে এখানে নিজে থেকেই সংযুক্ত হবে।</span>
                        <span class="en" style="display:none;">The new login account is created and linked here automatically.</span>
                    </p>

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

            <form method="POST" action="" id="edit_employee_form" data-employee-form>
                @csrf
                @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px;">
                    <x-core::input
                        name="employee_code"
                        id="edit_employee_code"
                        label="কর্মচারী কোড"
                        label-en="Employee Code"
                        placeholder="EMP-0001"
                        placeholder-en="EMP-0001"
                        size="sm"
                        :required="true"
                    />
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
                        @include('employee::partials.named-select', [
                            'field' => 'designation', 'idPrefix' => 'edit_employee', 'options' => $designationOptions, 'required' => true,
                            'selectedId' => null, 'newValue' => null,
                            'label' => 'পদবি', 'labelEn' => 'Designation', 'placeholder' => 'নতুন পদবির নাম', 'placeholderEn' => 'New designation name',
                        ])

                        @include('employee::partials.named-select', [
                            'field' => 'department', 'idPrefix' => 'edit_employee', 'options' => $departmentOptions, 'required' => false,
                            'selectedId' => null, 'newValue' => null,
                            'label' => 'বিভাগ', 'labelEn' => 'Department', 'placeholder' => 'নতুন বিভাগের নাম', 'placeholderEn' => 'New department name',
                        ])
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

                    <div data-login-wrap id="edit_employee_login_wrap">
                        @include('employee::partials.login-account', ['idPrefix' => 'edit_employee'])
                    </div>

                    <x-core::select
                        name="user_id"
                        id="edit_employee_user_id"
                        label="সংযুক্ত ইউজার লগইন অ্যাকাউন্ট (ঐচ্ছিক)"
                        label-en="Linked User Login Account (Optional)"
                        size="sm"
                        :options="$userSelectOptions"
                    />

                    <p data-linked-user-note hidden style="font-size:12px; color:var(--ink-400); margin:-8px 0 0;">
                        <span class="bn">নতুন লগইন অ্যাকাউন্টটি তৈরি হয়ে এখানে নিজে থেকেই সংযুক্ত হবে।</span>
                        <span class="en" style="display:none;">The new login account is created and linked here automatically.</span>
                    </p>

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

    @include('employee::partials.form-scripts')

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
                window.employeeForm.sync($form);
                showModal($('#createEmployeeModal'));
                setTimeout(function () {
                    $('#create_employee_name').focus();
                }, 100);
            });

            // Import modal: the role is asked for only when logins are created
            $('#btn-open-import-employee-modal').on('click', function () {
                showModal($('#importEmployeeModal'));
            });

            $('#import_employee_create_user').on('change', function () {
                $('#import_employee_login_fields').prop('hidden', !this.checked);
                $('#import_employee_user_role').prop('disabled', !this.checked);
            });

            $('#import_employee_form').on('submit', function () {
                $('#btn-submit-import-employee').prop('disabled', true);
            });

            // A designation/department typed in as new joins both dropdowns.
            function addNamedOptions(employee) {
                $.each(['designation', 'department'], function (i, field) {
                    var id = employee[field + '_id'];
                    if (!id || !employee[field]) {
                        return;
                    }
                    $('[data-named-field="' + field + '"] [data-named-select]').each(function () {
                        if (!$(this).find('option[value="' + id + '"]').length) {
                            $(this).find('option[value="__new"]').before($('<option></option>').val(id).text(employee[field]));
                        }
                    });
                });
            }

            function showFieldError($form, field, message) {
                var $field = $form.find('[name="' + field + '"]');
                if (!$field.length) {
                    return;
                }
                var $anchor = $field.closest('.form-input-group, .form-select-group');
                if (!$anchor.length) {
                    $anchor = $field.closest('.form-check');
                }
                $field.addClass('is-invalid');
                $anchor.after($('<div class="form-error-msg" style="color:var(--red-600); font-size:11.5px; margin-top:3px;"></div>').text(message));
            }

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
                        // The next new employee gets the next free code.
                        if (res.next_employee_code) {
                            $('#create_employee_code').attr('value', res.next_employee_code);
                        }
                        $form[0].reset();
                        addNamedOptions(res.employee || {});
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
                                showFieldError($form, field, msgs[0]);
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
                        $('#edit_employee_code').val(employee.employee_code || '');
                        $('#edit_employee_name').val(employee.name);
                        $('#edit_employee_phone').val(employee.phone);
                        $('#edit_employee_email').val(employee.email || '');
                        // The dropdown choice; a name with no record yet goes in as new.
                        $.each(['designation', 'department'], function (i, field) {
                            var id = employee[field + '_id'];
                            var isNew = !id && !!employee[field];
                            $('#edit_employee_' + field + '_id').val(isNew ? '__new' : (id || ''));
                            $('#edit_employee_' + field).prop('disabled', false).val(isNew ? employee[field] : '');
                        });
                        $('#edit_employee_salary').val(employee.salary);
                        $('#edit_employee_joining_date').val(employee.joining_date || '');
                        $('#edit_employee_address').val(employee.address || '');

                        // Logins created since the page loaded are in the fresh list.
                        var $userSelect = $('#edit_employee_user_id');
                        $userSelect.find('option').not('[value=""]').remove();
                        $.each(data.users || [], function (i, user) {
                            $userSelect.append($('<option></option>').val(user.id).text(user.name + ' (' + (user.email || '') + ')'));
                        });
                        $userSelect.val(employee.user_id || '');

                        // A login can be created only for an employee without one.
                        $form.find('[name="create_user"]').prop('checked', false);
                        $form.find('[name="user_password"]').val('');
                        $('#edit_employee_login_wrap').prop('hidden', !!employee.user_id);
                        window.employeeForm.sync($form);

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
                        addNamedOptions(res.employee || {});
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
                                showFieldError($form, field, msgs[0]);
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
