<x-core::layout
    title="অ্যাক্টিভিটি লগ"
    title-en="Audit Log"
    subtitle="আর্থিক ও গুরুত্বপূর্ণ লেনদেনের পরিবর্তনের ইতিহাস"
    subtitle-en="An immutable history of changes to financial and inventory records"
    active="audit-log"
>
    <div class="cash-page-head">
        <a href="{{ route('dashboard') }}" class="back" title="Back">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M11 18l-6-6 6-6" stroke="#1C2B27" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <div class="ttl bn">অ্যাক্টিভিটি লগ</div>
        <div class="ttl en" style="display:none;">Audit Log</div>
    </div>

    <form method="GET" action="{{ route('audit-log.index') }}" class="section-row">
        <div class="filters">
            <select name="model" onchange="this.form.submit()">
                <option value="all" @selected($model === 'all')>সব রেকর্ড</option>
                @foreach ($labels as $class => $label)
                    <option value="{{ $class }}" @selected($model === $class)>{{ $label['bn'] }}</option>
                @endforeach
            </select>
            <select name="action" onchange="this.form.submit()">
                <option value="all" @selected($action === 'all')>সব ধরন</option>
                <option value="created" @selected($action === 'created')>তৈরি</option>
                <option value="updated" @selected($action === 'updated')>হালনাগাদ</option>
                <option value="deleted" @selected($action === 'deleted')>বাতিল/মুছে ফেলা</option>
                <option value="restored" @selected($action === 'restored')>পুনরুদ্ধার</option>
            </select>
        </div>
    </form>

    <div class="panel" style="margin-top:0;">
        <div class="panel-body">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="bn">তারিখ ও সময়</th><th class="en" style="display:none;">Date &amp; Time</th>
                            <th class="bn">রেকর্ড</th><th class="en" style="display:none;">Record</th>
                            <th class="bn">ধরন</th><th class="en" style="display:none;">Action</th>
                            <th class="bn">পরিবর্তন</th><th class="en" style="display:none;">Changes</th>
                            <th class="bn">দ্বারা</th><th class="en" style="display:none;">By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            @php
                                $auditLabel = $labels[$log->auditable_type] ?? ['bn' => class_basename($log->auditable_type), 'en' => class_basename($log->auditable_type)];
                                $actionLabel = $log->actionLabel();
                                $fieldCount = count($log->new_values ?? []);
                            @endphp
                            <tr>
                                <td>{{ $log->created_at->format('d M, Y, h:i A') }}</td>
                                <td class="cell-main">
                                    <span class="bn">{{ $auditLabel['bn'] }}</span><span class="en" style="display:none;">{{ $auditLabel['en'] }}</span>
                                    <span class="cell-sub">#{{ $log->auditable_id }}</span>
                                </td>
                                <td>
                                    @if ($log->action === 'created')
                                        <span class="badge b-green bn">{{ $actionLabel['bn'] }}</span><span class="badge b-green en" style="display:none;">{{ $actionLabel['en'] }}</span>
                                    @elseif ($log->action === 'deleted')
                                        <span class="badge b-red bn">{{ $actionLabel['bn'] }}</span><span class="badge b-red en" style="display:none;">{{ $actionLabel['en'] }}</span>
                                    @elseif ($log->action === 'restored')
                                        <span class="badge b-teal bn">{{ $actionLabel['bn'] }}</span><span class="badge b-teal en" style="display:none;">{{ $actionLabel['en'] }}</span>
                                    @else
                                        <span class="badge b-gold bn">{{ $actionLabel['bn'] }}</span><span class="badge b-gold en" style="display:none;">{{ $actionLabel['en'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($log->action === 'updated')
                                        <span class="bn">{{ $fieldCount }}টি ফিল্ড পরিবর্তিত</span><span class="en" style="display:none;">{{ $fieldCount }} field(s) changed</span>
                                    @elseif ($log->action === 'created')
                                        <span class="bn">নতুন রেকর্ড তৈরি</span><span class="en" style="display:none;">New record created</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $log->user->name ?? '—' }}</td>
                                <td>
                                    @if ($log->action === 'updated' && $fieldCount)
                                        <div class="row-actions">
                                            <button type="button" class="act" title="Details" onclick="openModal('auditLog-{{ $log->id }}')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="5.5" r="1.6" fill="#5C6B65"/><circle cx="12" cy="12" r="1.6" fill="#5C6B65"/><circle cx="12" cy="18.5" r="1.6" fill="#5C6B65"/></svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-core::table.empty
                                        icon="file-text"
                                        title="কোনো লগ নেই"
                                        title-en="No audit logs found"
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:14px;">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

    @foreach ($logs as $log)
        @if ($log->action === 'updated' && count($log->new_values ?? []))
            <div class="drawer-backdrop" id="auditLog-{{ $log->id }}">
                <div class="drawer" style="width:460px;">
                    <div class="drawer-head">
                        <div class="drawer-title bn">পরিবর্তনের বিস্তারিত</div>
                        <div class="drawer-title en" style="display:none;">Change Details</div>
                        <button type="button" class="drawer-x" onclick="closeModal('auditLog-{{ $log->id }}')">&times;</button>
                    </div>
                    <div class="tx-section">
                        @foreach ($log->new_values as $field => $newValue)
                            <div class="tx-row">
                                <span class="lbl">{{ $field }}</span>
                                <span class="val" style="font-weight:400; text-align:right;">
                                    <span style="text-decoration:line-through; color:var(--ink-400);">{{ is_scalar($log->old_values[$field] ?? null) ? $log->old_values[$field] : json_encode($log->old_values[$field] ?? null) }}</span>
                                    &rarr;
                                    <span style="font-weight:700;">{{ is_scalar($newValue) ? $newValue : json_encode($newValue) }}</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endforeach
</x-core::layout>
