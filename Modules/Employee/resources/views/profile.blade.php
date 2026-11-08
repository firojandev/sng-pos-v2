<x-core::layout
    title="কর্মচারীর প্রোফাইল"
    title-en="Employee Profile"
    subtitle="{{ $employee->employee_code }} — {{ $employee->name }}"
    subtitle-en="{{ $employee->employee_code }} — {{ $employee->name }}"
    active="employees"
>
    <x-employee::tabbar active="employees" />

    @php
        $hasPayroll = (bool) (auth()->user()?->isSuperAdmin() || auth()->user()?->shop?->hasFeature('payroll'));
        $field = fn (string $name) => old($name, $employee->{$name} instanceof \Carbon\CarbonInterface ? $employee->{$name}->toDateString() : $employee->{$name});
    @endphp

    <form method="POST" action="{{ route('employees.profile.update', $employee) }}" style="margin-top:16px;">
        @csrf
        @method('PUT')

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(340px, 1fr)); gap:16px; align-items:start;">
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">চাকরির তথ্য</span><span class="en" style="display:none;">Job</span></div></div>
                <div class="panel-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <x-core::input size="sm" name="employee_code" label="কর্মচারী কোড" label-en="Employee Code" :value="$field('employee_code')" :required="true" />
                    <x-core::input size="sm" name="name" label="নাম" label-en="Name" :value="$field('name')" :required="true" />
                    <x-core::select size="sm" name="shop_id" label="কর্মস্থল (দোকান)" label-en="Works At (Shop)" :options="$shops->all()" :value="$field('shop_id')" :required="true" />
                    <x-core::select size="sm" name="department_id" label="বিভাগ" label-en="Department" :options="$departments->all()" :value="$field('department_id')" placeholder="--" placeholder-en="--" />
                    <x-core::select size="sm" name="designation_id" label="পদবি" label-en="Designation" :options="$designations->all()" :value="$field('designation_id')" placeholder="--" placeholder-en="--" />
                    <x-core::select size="sm" name="shift_id" label="শিফট" label-en="Shift" :options="$shifts->all()" :value="$field('shift_id')" placeholder="ডিফল্ট শিফট" placeholder-en="Default shift" />
                    <x-core::select size="sm" name="employment_type" label="চাকরির ধরন" label-en="Employment Type" :required="true" :value="$field('employment_type')"
                        :options="['permanent' => 'স্থায়ী (Permanent)', 'probation' => 'শিক্ষানবিশ (Probation)', 'contract' => 'চুক্তিভিত্তিক (Contract)', 'casual' => 'অস্থায়ী (Casual)']" />
                    <x-core::input size="sm" type="number" step="0.01" min="0" name="salary" label="মোট বেতন (মাসিক)" label-en="Gross Salary (Monthly)" :value="$field('salary')" :stepper="false" :required="true" />
                    <x-core::input size="sm" type="date" name="joining_date" label="যোগদানের তারিখ" label-en="Joining Date" :value="$field('joining_date')" />
                    <x-core::input size="sm" type="date" name="confirmation_date" label="স্থায়ীকরণের তারিখ" label-en="Confirmation Date" :value="$field('confirmation_date')" />
                    <x-core::input size="sm" name="device_user_id" label="হাজিরা মেশিন আইডি" label-en="Attendance Device ID" :value="$field('device_user_id')" />
                    <x-core::select size="sm" name="user_id" label="লগইন ইউজার (সেলফ-সার্ভিস)" label-en="Login User (Self-service)" :options="$users->all()" :value="$field('user_id')" placeholder="সংযুক্ত নয়" placeholder-en="Not linked" />
                    <x-core::select size="sm" name="status" label="অবস্থা" label-en="Status" :required="true" :value="$field('status')"
                        :options="['active' => 'সক্রিয় (Active)', 'inactive' => 'নিষ্ক্রিয় (Inactive)', 'resigned' => 'পদত্যাগ (Resigned)', 'terminated' => 'চাকরিচ্যুত (Terminated)']" />
                    <x-core::input size="sm" type="date" name="separation_date" label="বিচ্ছেদের তারিখ" label-en="Separation Date" :value="$field('separation_date')" />
                    <x-core::input size="sm" name="separation_reason" label="বিচ্ছেদের কারণ" label-en="Separation Reason" :value="$field('separation_reason')" />
                </div>
            </div>

            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">ব্যক্তিগত তথ্য</span><span class="en" style="display:none;">Personal</span></div></div>
                <div class="panel-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <x-core::select size="sm" name="gender" label="লিঙ্গ" label-en="Gender" :value="$field('gender')" placeholder="--" placeholder-en="--"
                        :options="['male' => 'পুরুষ (Male)', 'female' => 'নারী (Female)', 'other' => 'অন্যান্য (Other)']" />
                    <x-core::input size="sm" type="date" name="date_of_birth" label="জন্ম তারিখ" label-en="Date of Birth" :value="$field('date_of_birth')" />
                    <x-core::select size="sm" name="blood_group" label="রক্তের গ্রুপ" label-en="Blood Group" :value="$field('blood_group')" placeholder="--" placeholder-en="--"
                        :options="array_combine(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'], ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])" />
                    <x-core::select size="sm" name="marital_status" label="বৈবাহিক অবস্থা" label-en="Marital Status" :value="$field('marital_status')" placeholder="--" placeholder-en="--"
                        :options="['single' => 'অবিবাহিত (Single)', 'married' => 'বিবাহিত (Married)', 'divorced' => 'তালাকপ্রাপ্ত (Divorced)', 'widowed' => 'বিধবা/বিপত্নীক (Widowed)']" />
                    <x-core::input size="sm" name="nid" label="জাতীয় পরিচয়পত্র" label-en="National ID" :value="$field('nid')" />
                    <x-core::input size="sm" name="tin" label="টিআইএন" label-en="TIN" :value="$field('tin')" />
                    @if ($hasPayroll)
                    <x-core::select size="sm" name="tax_category" label="আয়করের শ্রেণি" label-en="Tax Category" :value="$field('tax_category')" placeholder="স্বয়ংক্রিয় (লিঙ্গ/বয়স অনুযায়ী)" placeholder-en="Automatic (by gender/age)"
                        :options="collect(config('payroll.tax_categories', []))->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" />
                    <x-core::select size="sm" name="tax_location" label="ন্যূনতম করের এলাকা" label-en="Minimum Tax Location" :value="$field('tax_location')" placeholder="কোম্পানির ডিফল্ট" placeholder-en="Company default"
                        :options="collect(config('payroll.tax_locations', []))->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" />
                    @endif
                    <x-core::input size="sm" name="father_name" label="পিতার নাম" label-en="Father's Name" :value="$field('father_name')" />
                    <x-core::input size="sm" name="mother_name" label="মাতার নাম" label-en="Mother's Name" :value="$field('mother_name')" />
                </div>
            </div>

            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">যোগাযোগ ও পেমেন্ট</span><span class="en" style="display:none;">Contact & Payment</span></div></div>
                <div class="panel-body" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <x-core::input size="sm" name="phone" label="মোবাইল" label-en="Phone" :value="$field('phone')" :required="true" />
                    <x-core::input size="sm" type="email" name="email" label="ইমেইল" label-en="Email" :value="$field('email')" />
                    <x-core::input size="sm" name="address" label="বর্তমান ঠিকানা" label-en="Present Address" :value="$field('address')" />
                    <x-core::input size="sm" name="permanent_address" label="স্থায়ী ঠিকানা" label-en="Permanent Address" :value="$field('permanent_address')" />
                    <x-core::input size="sm" name="emergency_contact_name" label="জরুরি যোগাযোগ (নাম)" label-en="Emergency Contact" :value="$field('emergency_contact_name')" />
                    <x-core::input size="sm" name="emergency_contact_phone" label="জরুরি যোগাযোগ (মোবাইল)" label-en="Emergency Phone" :value="$field('emergency_contact_phone')" />
                    <x-core::input size="sm" name="bank_name" label="ব্যাংক" label-en="Bank" :value="$field('bank_name')" />
                    <x-core::input size="sm" name="bank_account_no" label="ব্যাংক অ্যাকাউন্ট নম্বর" label-en="Bank Account No." :value="$field('bank_account_no')" />
                    <x-core::input size="sm" name="mfs_number" label="বিকাশ/নগদ নম্বর" label-en="bKash/Nagad Number" :value="$field('mfs_number')" />
                </div>
            </div>

            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">ছুটির ব্যালেন্স ({{ now()->year }})</span><span class="en" style="display:none;">Leave Balance ({{ now()->year }})</span></div></div>
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                                <th class="table-cell-right"><span class="bn">প্রাপ্য</span><span class="en" style="display:none;">Entitled</span></th>
                                <th class="table-cell-right"><span class="bn">ব্যবহৃত</span><span class="en" style="display:none;">Used</span></th>
                                <th class="table-cell-right"><span class="bn">বাকি</span><span class="en" style="display:none;">Available</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($balances as $balance)
                                <tr>
                                    <td>{{ $balance['type']->name }}</td>
                                    <td class="table-cell-right">{{ $balance['entitled'] }}</td>
                                    <td class="table-cell-right">{{ $balance['used'] }}</td>
                                    <td class="table-cell-right" style="font-weight:700;">{{ $balance['available'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><x-core::table.empty icon="calendar" title="কোনো ছুটির ধরন নেই" title-en="No leave types" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @can('employees.edit')
            <div style="display:flex; gap:10px; margin-top:16px; align-items:center;">
                @if ($employee->isRecordOf(auth()->user()))
                    <span style="font-size:12.5px; color:var(--ink-500);">
                        <span class="bn">এটি আপনার নিজের রেকর্ড — অন্য একজন এডমিন পরিবর্তন করবেন।</span>
                        <span class="en" style="display:none;">This is your own record — another admin changes it.</span>
                    </span>
                @else
                    <x-core::button type="submit" size="sm" variant="solid" color="primary">
                        <span class="bn">সংরক্ষণ করুন</span><span class="en" style="display:none;">Save</span>
                    </x-core::button>
                @endif
                <x-core::button as="a" href="{{ route('employees.index') }}" size="sm" variant="secondary">
                    <span class="bn">তালিকায় ফিরুন</span><span class="en" style="display:none;">Back to List</span>
                </x-core::button>
                @if ($hasPayroll && Route::has('payroll.salary.show'))
                    @can('payroll.view')
                        <x-core::button as="a" href="{{ route('payroll.salary.show', $employee) }}" size="sm" variant="secondary" icon="wallet">
                            <span class="bn">বেতন ও পে-রোল</span><span class="en" style="display:none;">Salary & Payroll</span>
                        </x-core::button>
                    @endcan
                @endif
            </div>
        @endcan
    </form>

    <div class="panel" style="margin-top:16px;">
        <div class="panel-head"><div class="panel-title"><span class="bn">ডকুমেন্ট</span><span class="en" style="display:none;">Documents</span></div></div>
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th><span class="bn">শিরোনাম</span><span class="en" style="display:none;">Title</span></th>
                        <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                        <th><span class="bn">মেয়াদ</span><span class="en" style="display:none;">Expires</span></th>
                        <th><span class="bn">যোগ করেছেন</span><span class="en" style="display:none;">Uploaded</span></th>
                        <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employee->documents as $document)
                        <tr>
                            <td style="font-weight:600;">{{ $document->title }} <div style="font-size:11.5px; color:var(--ink-500);">{{ $document->original_name }} · {{ number_format($document->size / 1024, 0) }} KB</div></td>
                            <td>{{ \Modules\Employee\Models\EmployeeDocument::TYPES[$document->type] ?? $document->type }}</td>
                            <td style="white-space:nowrap;">
                                @if ($document->expires_on)
                                    <span style="{{ $document->expires_on->isPast() ? 'color:var(--red-600); font-weight:600;' : '' }}">{{ $document->expires_on->format('d M, Y') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-size:12.5px;">{{ $document->created_at->format('d M, Y') }} <span style="color:var(--ink-500);">{{ $document->uploader?->name }}</span></td>
                            <td class="table-cell-right">
                                <div style="display:flex; gap:4px; justify-content:flex-end;">
                                    <x-core::button as="a" href="{{ route('employees.documents.download', [$employee, $document]) }}" size="sm" variant="soft" color="primary" icon="download" icon-only title="ডাউনলোড / Download" />
                                    @can('employees.edit')
                                        <form method="POST" action="{{ route('employees.documents.destroy', [$employee, $document]) }}" class="delete-form" data-title="ডকুমেন্ট মুছে ফেলবেন?">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-core::table.empty icon="file" title="কোনো ডকুমেন্ট নেই" title-en="No documents" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @can('employees.edit')
            <div class="panel-body">
                <form method="POST" action="{{ route('employees.documents.store', $employee) }}" enctype="multipart/form-data" style="display:flex; gap:8px; align-items:flex-end; flex-wrap:wrap;">
                    @csrf
                    <div style="width:220px;"><x-core::input size="sm" name="title" label="শিরোনাম" label-en="Title" :required="true" /></div>
                    <div style="width:200px;"><x-core::select size="sm" name="type" label="ধরন" label-en="Type" :options="\Modules\Employee\Models\EmployeeDocument::TYPES" value="other" :required="true" /></div>
                    <div style="width:160px;"><x-core::input size="sm" type="date" name="expires_on" label="মেয়াদ (ঐচ্ছিক)" label-en="Expires (optional)" /></div>
                    <div style="width:240px;"><x-core::input size="sm" type="file" name="document" label="ফাইল (PDF/ছবি, ৫ MB)" label-en="File (PDF/image, 5 MB)" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" :required="true" /></div>
                    <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="upload"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Upload</span></x-core::button>
                </form>
            </div>
        @endcan
    </div>
</x-core::layout>
