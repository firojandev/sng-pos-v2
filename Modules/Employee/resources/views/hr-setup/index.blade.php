<x-core::layout title="এইচআর সেটআপ" title-en="HR Setup" subtitle="বিভাগ, পদবি, শিফট, ছুটির দিন ও ছুটির ধরন" subtitle-en="Departments, designations, shifts, holidays and leave types" active="hr-setup">
    <x-employee::tabbar active="setup" />

    @php
        $weekdays = [6 => 'শনি Sat', 7 => 'রবি Sun', 1 => 'সোম Mon', 2 => 'মঙ্গল Tue', 3 => 'বুধ Wed', 4 => 'বৃহঃ Thu', 5 => 'শুক্র Fri'];
    @endphp

    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:16px; align-items:start; margin-top:16px;">
        @foreach (['departments' => [$departments, 'বিভাগ', 'Departments'], 'designations' => [$designations, 'পদবি', 'Designations']] as $kind => [$records, $titleBn, $titleEn])
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">{{ $titleBn }}</span><span class="en" style="display:none;">{{ $titleEn }}</span></div></div>
                <div class="panel-body">
                    @can('hr-setup.edit')
                        <form method="POST" action="{{ route('hr-setup.named.store', $kind) }}" style="display:flex; gap:8px; align-items:flex-end; margin-bottom:12px;">
                            @csrf
                            <div style="flex:1;"><x-core::input size="sm" name="name" :no-margin="true" placeholder="নতুন নাম" placeholder-en="New name" :required="true" /></div>
                            <x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus" icon-only title="যোগ করুন / Add" />
                        </form>
                    @endcan
                </div>
                <div class="table-responsive">
                    <table class="app-table">
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td>{{ $record->name }}</td>
                                    <td class="table-cell-right" style="font-size:12px; color:var(--ink-500);">{{ $record->employees_count }} <span class="bn">জন</span><span class="en" style="display:none;">staff</span></td>
                                    <td class="table-cell-right" style="width:50px;">
                                        @can('hr-setup.edit')
                                            @if ($record->employees_count === 0)
                                                <form method="POST" action="{{ route('hr-setup.named.destroy', [$kind, $record->id]) }}" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                                </form>
                                            @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><x-core::table.empty icon="list" title="কিছু যোগ করা হয়নি" title-en="Nothing added yet" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">শিফট</span><span class="en" style="display:none;">Shifts</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <tbody>
                        @foreach ($shifts as $shift)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $shift->name }}
                                        @if ($shift->is_default)
                                            <x-core::badge color="green" size="xs" label="ডিফল্ট" label-en="Default" />
                                        @endif
                                    </div>
                                    <div style="font-size:11.5px; color:var(--ink-500);">
                                        {{ substr($shift->start_time, 0, 5) }}–{{ substr($shift->end_time, 0, 5) }} · <span class="bn">গ্রেস</span><span class="en" style="display:none;">grace</span> {{ $shift->grace_minutes }}m
                                        · {{ collect($shift->weekend_days ?? [])->map(fn ($day) => $weekdays[$day] ?? $day)->implode(', ') ?: '—' }}
                                    </div>
                                </td>
                                <td class="table-cell-right">
                                    @can('hr-setup.edit')
                                        @unless ($shift->is_default)
                                            <div style="display:flex; gap:4px; justify-content:flex-end;">
                                                <form method="POST" action="{{ route('hr-setup.shifts.default', $shift) }}">
                                                    @csrf
                                                    <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="ডিফল্ট করুন / Make default" />
                                                </form>
                                                <form method="POST" action="{{ route('hr-setup.shifts.destroy', $shift) }}" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                                </form>
                                            </div>
                                        @endunless
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @can('hr-setup.edit')
                <div class="panel-body">
                    <form method="POST" action="{{ route('hr-setup.shifts.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <x-core::input size="sm" name="name" label="শিফটের নাম" label-en="Shift Name" :required="true" />
                        <x-core::input size="sm" type="number" name="grace_minutes" label="গ্রেস (মিনিট)" label-en="Grace (min)" value="10" :stepper="false" :required="true" />
                        <x-core::input size="sm" type="time" name="start_time" label="শুরু" label-en="Starts" :required="true" />
                        <x-core::input size="sm" type="time" name="end_time" label="শেষ" label-en="Ends" :required="true" />
                        <div style="grid-column:1 / -1; display:flex; flex-wrap:wrap; gap:8px;">
                            @foreach ($weekdays as $day => $label)
                                <x-core::checkbox size="sm" name="weekend_days[]" :value="$day" :checked="$day === 5"><span style="font-size:12px;">{{ $label }}</span></x-core::checkbox>
                            @endforeach
                        </div>
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">শিফট যোগ করুন</span><span class="en" style="display:none;">Add Shift</span></x-core::button></div>
                    </form>
                </div>
            @endcan
        </div>

        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">ছুটির দিন</span><span class="en" style="display:none;">Holidays</span></div></div>
            @can('hr-setup.edit')
                <div class="panel-body">
                    <form method="POST" action="{{ route('hr-setup.holidays.store') }}" style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        @csrf
                        <x-core::input size="sm" type="date" name="date" label="তারিখ" label-en="Date" :required="true" />
                        <x-core::input size="sm" type="date" name="to_date" label="পর্যন্ত (ঐচ্ছিক)" label-en="Until (optional)" />
                        <x-core::input size="sm" name="name" label="নাম" label-en="Name" placeholder="যেমন: ঈদুল ফিতর" placeholder-en="e.g. Eid-ul-Fitr" :required="true" />
                        <x-core::select size="sm" name="type" label="ধরন" label-en="Type" :required="true" value="festival"
                            :options="['festival' => 'উৎসব (Festival)', 'public' => 'সরকারি (Public)', 'company' => 'কোম্পানি (Company)']" />
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Add</span></x-core::button></div>
                    </form>
                </div>
            @endcan
            <div class="table-responsive" style="max-height:320px; overflow-y:auto;">
                <table class="app-table">
                    <tbody>
                        @forelse ($holidays as $holiday)
                            <tr>
                                <td style="white-space:nowrap;">{{ $holiday->date->format('d M, Y') }}</td>
                                <td>{{ $holiday->name }}</td>
                                <td class="table-cell-right">
                                    @can('hr-setup.edit')
                                        <form method="POST" action="{{ route('hr-setup.holidays.destroy', $holiday) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><x-core::table.empty icon="calendar" title="কোনো ছুটির দিন নেই" title-en="No holidays" /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="panel" style="margin-top:0; grid-column:1 / -1;">
            <div class="panel-head"><div class="panel-title"><span class="bn">ছুটির ধরন (শ্রম আইন ২০০৬ অনুযায়ী ডিফল্ট)</span><span class="en" style="display:none;">Leave Types (Labour Act 2006 defaults)</span></div></div>
            <div class="table-responsive">
                <table class="app-table">
                    <thead>
                        <tr>
                            <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                            <th><span class="bn">বছরে দিন</span><span class="en" style="display:none;">Days / Year</span></th>
                            <th><span class="bn">প্রতি কত দিন কাজে ১ দিন</span><span class="en" style="display:none;">1 Day per Days Worked</span></th>
                            <th><span class="bn">বেতনসহ</span><span class="en" style="display:none;">Paid</span></th>
                            <th><span class="bn">পরের বছরে জমা</span><span class="en" style="display:none;">Carry Forward</span></th>
                            <th style="width:100px;"><span class="bn">সর্বোচ্চ জমা</span><span class="en" style="display:none;">Max Carried</span></th>
                            <th><span class="bn">মেয়াদ শেষ</span><span class="en" style="display:none;">Expires</span></th>
                            <th style="width:100px;"><span class="bn">কত মাস পরে</span><span class="en" style="display:none;">After (Months)</span></th>
                            <th><span class="bn">নগদায়ন</span><span class="en" style="display:none;">Encashment</span></th>
                            <th><span class="bn">সক্রিয়</span><span class="en" style="display:none;">Active</span></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leaveTypes as $type)
                            @php $formId = 'leave-type-'.$type->id; @endphp
                            <tr>
                                <td>
                                    {{ $type->name }}
                                    @if ($type->gender === 'female')
                                        <x-core::badge color="grey" size="xs" label="শুধু নারী" label-en="Women only" />
                                    @endif
                                </td>
                                <td style="width:120px;"><x-core::input size="sm" :no-margin="true" type="number" name="days_per_year" :form="$formId" :value="$type->days_per_year" :stepper="false" /></td>
                                <td style="width:120px;"><x-core::input size="sm" :no-margin="true" type="number" name="earn_one_day_per" :form="$formId" :value="$type->earn_one_day_per" :stepper="false" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="is_paid" :form="$formId" :value="$type->is_paid ? '1' : '0'" :options="['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)']" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="carry_forward" :form="$formId" :value="$type->carry_forward ? '1' : '0'" :options="['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)']" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" name="max_carry_forward" :form="$formId" :value="$type->max_carry_forward" placeholder="সীমাহীন" placeholder-en="No limit" :stepper="false" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="carry_forward_expires" :form="$formId" :value="$type->carry_forward_expires ? '1' : '0'" :options="['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)']" /></td>
                                <td><x-core::input size="sm" :no-margin="true" type="number" name="carry_forward_expiry_months" :form="$formId" :value="$type->carry_forward_expiry_months" :stepper="false" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="is_encashable" :form="$formId" :value="$type->is_encashable ? '1' : '0'" :options="['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)']" /></td>
                                <td><x-core::select size="sm" :no-margin="true" name="is_active" :form="$formId" :value="$type->is_active ? '1' : '0'" :options="['1' => 'হ্যাঁ (Yes)', '0' => 'না (No)']" /></td>
                                <td class="table-cell-right">
                                    @can('hr-setup.edit')
                                        <form method="POST" action="{{ route('hr-setup.leave-types.update', $type) }}" id="{{ $formId }}">
                                            @csrf
                                            @method('PUT')
                                            <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="সংরক্ষণ / Save" />
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-core::layout>
