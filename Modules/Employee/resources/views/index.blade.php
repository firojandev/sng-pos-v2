<x-core::layout
    title="কর্মচারী"
    title-en="Employees"
    subtitle="দোকানের কর্মচারীদের তথ্য পরিচালনা করুন"
    subtitle-en="Manage your shop's employee records"
    active="employees"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('employees.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন কর্মচারী</span><span class="en">New Employee</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">পদবি</th><th class="en" style="display:none;">Designation</th>
                            <th class="bn">ফোন</th><th class="en" style="display:none;">Phone</th>
                            <th class="bn">বেতন</th><th class="en" style="display:none;">Salary</th>
                            <th class="bn">যোগদান</th><th class="en" style="display:none;">Joined</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr>
                                <td>
                                    <div class="row-avatar">
                                        <div class="av" style="background:var(--teal-800);">{{ mb_substr($employee->name, 0, 1) }}</div>
                                        <div class="cell-main">{{ $employee->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $employee->designation }}</td>
                                <td>{{ $employee->phone }}</td>
                                <td>৳{{ number_format($employee->salary, 0) }}</td>
                                <td>{{ optional($employee->joining_date)->format('d M, Y') ?? '—' }}</td>
                                <td>
                                    @if ($employee->status === 'active')
                                        <span class="badge b-green bn">সক্রিয়</span><span class="badge b-green en" style="display:none;">Active</span>
                                    @else
                                        <span class="badge b-grey bn">নিষ্ক্রিয়</span><span class="badge b-grey en" style="display:none;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('employees.edit', $employee) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" onsubmit="return confirm('এই কর্মচারীকে মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="act" title="Delete">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13" stroke="#C1443C" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="helper" style="margin-top:0;">কোনো কর্মচারী নেই</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $employees->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
