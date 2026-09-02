<x-core::layout
    title="গ্রাহক"
    title-en="Customers"
    subtitle="দোকানের গ্রাহকদের তথ্য পরিচালনা করুন"
    subtitle-en="Manage your shop's customer records"
    active="customers"
>
    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="section-row">
                <div class="filters"></div>
                <a class="btn btn-gold" href="{{ route('customers.create') }}">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 5v14M5 12h14" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/></svg>
                    <span class="bn">নতুন গ্রাহক</span><span class="en">New Customer</span>
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">নাম</th><th class="en" style="display:none;">Name</th>
                            <th class="bn">ফোন</th><th class="en" style="display:none;">Phone</th>
                            <th class="bn">ঠিকানা</th><th class="en" style="display:none;">Address</th>
                            <th class="bn">বাকি</th><th class="en" style="display:none;">Due</th>
                            <th class="bn">অবস্থা</th><th class="en" style="display:none;">Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            @php
                                $due = (float) $customer->opening_due + (float) ($customer->sales_sum_due_amount ?? 0);
                            @endphp
                            <tr>
                                <td>
                                    <div class="row-avatar">
                                        <div class="av" style="background:var(--teal-800);">{{ mb_substr($customer->name, 0, 1) }}</div>
                                        <div class="cell-main">{{ $customer->name }}</div>
                                    </div>
                                </td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td>{{ $customer->address ?? '—' }}</td>
                                <td>
                                    @if ($due > 0)
                                        <span style="color:var(--red-600); font-weight:700;">৳{{ number_format($due, 2) }}</span>
                                    @else
                                        <span>৳0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($customer->status === 'active')
                                        <span class="badge b-green bn">সক্রিয়</span><span class="badge b-green en" style="display:none;">Active</span>
                                    @else
                                        <span class="badge b-grey bn">নিষ্ক্রিয়</span><span class="badge b-grey en" style="display:none;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <a class="act" title="Edit" href="{{ route('customers.edit', $customer) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" stroke="#5C6B65" stroke-width="1.5" stroke-linejoin="round"/></svg>
                                        </a>
                                        <form method="POST" action="{{ route('customers.destroy', $customer) }}" onsubmit="return confirm('এই গ্রাহককে মুছে ফেলতে চান?');">
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
                            <tr>
                                <td colspan="6">
                                    <x-core::table.empty
                                        icon="users"
                                        title="কোনো গ্রাহক নেই"
                                        title-en="No customers found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
</x-core::layout>
