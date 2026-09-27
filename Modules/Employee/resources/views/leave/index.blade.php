<x-core::layout title="ছুটি" title-en="Leave" subtitle="ছুটির আবেদন ও অনুমোদন" subtitle-en="Leave requests and approvals" active="leave">
    <x-employee::tabbar active="leave" />

    @php
        $statusColors = ['pending' => 'gold', 'approved' => 'green', 'rejected' => 'red', 'cancelled' => 'grey'];
        $statusLabels = ['pending' => 'অপেক্ষমাণ / Pending', 'approved' => 'অনুমোদিত / Approved', 'rejected' => 'প্রত্যাখ্যাত / Rejected', 'cancelled' => 'বাতিল / Cancelled'];
    @endphp

    <div style="display:grid; grid-template-columns:minmax(300px, 380px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        @can('leave.create')
            <div class="panel" style="margin-top:0;">
                <div class="panel-head"><div class="panel-title"><span class="bn">ছুটির আবেদন</span><span class="en" style="display:none;">Apply for Leave</span></div></div>
                <div class="panel-body">
                    <form method="POST" action="{{ route('leave-requests.store') }}" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:12px;">
                        @csrf
                        <x-core::select size="sm" name="employee_id" label="কর্মচারী" label-en="Employee" :required="true" :value="old('employee_id')"
                            :options="$employees->mapWithKeys(fn ($employee) => [$employee->id => $employee->name.' ('.$employee->employee_code.')'])->all()" placeholder="--" placeholder-en="--" />
                        <x-core::select size="sm" name="leave_type_id" label="ছুটির ধরন" label-en="Leave Type" :required="true" :value="old('leave_type_id')"
                            :options="$leaveTypes->pluck('name', 'id')->all()" placeholder="--" placeholder-en="--" />
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <x-core::input size="sm" type="date" name="from_date" label="থেকে" label-en="From" :value="old('from_date')" :required="true" />
                            <x-core::input size="sm" type="date" name="to_date" label="পর্যন্ত" label-en="To" :value="old('to_date')" :required="true" />
                        </div>
                        <x-core::input size="sm" name="reason" label="কারণ" label-en="Reason" :value="old('reason')" />
                        <x-core::input size="sm" type="file" name="attachment" label="সংযুক্তি (ঐচ্ছিক)" label-en="Attachment (optional)" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" />
                        <div><x-core::button type="submit" size="sm" variant="solid" color="primary"><span class="bn">জমা দিন</span><span class="en" style="display:none;">Submit</span></x-core::button></div>
                    </form>
                </div>
            </div>
        @endcan

        <div>
            <form method="GET" style="display:flex; gap:8px; align-items:flex-end; margin-bottom:12px;">
                <div style="width:200px;"><x-core::select size="sm" :no-margin="true" name="status" :options="['' => ['bn' => 'সব', 'en' => 'All']] + $statusLabels" :value="request('status')" /></div>
                <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Filter</span></x-core::button>
            </form>
            @error('days')
                <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $message }}</div>
            @enderror
            <div class="table-container table-teal">
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">কর্মচারী</span><span class="en" style="display:none;">Employee</span></th>
                                <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                                <th><span class="bn">সময়কাল</span><span class="en" style="display:none;">Period</span></th>
                                <th class="table-cell-center"><span class="bn">দিন</span><span class="en" style="display:none;">Days</span></th>
                                <th class="table-cell-center"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                                <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($requests as $leave)
                                <tr>
                                    <td>
                                        {{ $leave->employee?->name }}
                                        @if ($leave->is_self_applied)
                                            <x-core::badge color="blue" size="xs" label="নিজে" label-en="Self" />
                                        @endif
                                        <div style="font-size:11.5px; color:var(--ink-500);">{{ $leave->reason }}</div>
                                        @if ($leave->attachment_path)
                                            <a href="{{ route('leave-requests.attachment', $leave) }}" style="font-size:11.5px;"><x-core::icon name="file" size="xs" /> {{ $leave->attachment_name }}</a>
                                        @endif
                                    </td>
                                    <td>{{ $leave->leaveType?->code }}</td>
                                    <td style="white-space:nowrap; font-size:12.5px;">{{ $leave->from_date->format('d M') }} – {{ $leave->to_date->format('d M, Y') }}</td>
                                    <td class="table-cell-center">{{ $leave->days }}</td>
                                    <td class="table-cell-center"><x-core::badge :color="$statusColors[$leave->status] ?? 'grey'" size="xs">{{ $statusLabels[$leave->status] ?? $leave->status }}</x-core::badge></td>
                                    <td class="table-cell-right">
                                        @can('leave.approve')
                                            <div style="display:flex; gap:4px; justify-content:flex-end;">
                                                @if ($leave->status === 'pending')
                                                    <form method="POST" action="{{ route('leave-requests.approve', $leave) }}">
                                                        @csrf
                                                        <x-core::button type="submit" size="sm" variant="soft" color="primary" icon="check" icon-only title="অনুমোদন / Approve" />
                                                    </form>
                                                    <form method="POST" action="{{ route('leave-requests.reject', $leave) }}">
                                                        @csrf
                                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="x" icon-only title="প্রত্যাখ্যান / Reject" />
                                                    </form>
                                                @elseif ($leave->status === 'approved')
                                                    <form method="POST" action="{{ route('leave-requests.cancel', $leave) }}" class="delete-form" data-title="ছুটি বাতিল করবেন?" data-text="এই দিনগুলো আবার সাধারণ কর্মদিবস হবে।">
                                                        @csrf
                                                        <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="rotate-ccw" icon-only title="বাতিল / Cancel" />
                                                    </form>
                                                @endif
                                            </div>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-core::table.empty icon="calendar" title="কোনো ছুটির আবেদন নেই" title-en="No leave requests" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="margin-top:12px;">{{ $requests->links() }}</div>
        </div>
    </div>
</x-core::layout>
