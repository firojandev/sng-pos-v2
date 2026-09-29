<x-core::layout title="টাস্ক" title-en="Tasks" subtitle="নিজের করণীয় তালিকা ও অন্যকে দেওয়া কাজ" subtitle-en="Your to-do list and tasks given to others" active="tasks">
    @php
        $labels = \Modules\Task\Models\Task::statusLabels();
        $statusOptions = collect($labels)->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all();
        $tabs = [
            'mine' => ['আমার টাস্ক', 'My Tasks'],
            'reported' => ['অন্যকে দেওয়া', 'Assigned by Me'],
        ] + ($canManage ? ['all' => ['সবার টাস্ক', 'All Tasks']] : []);
        $me = auth()->id();
    @endphp

    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-top:16px;">
        @foreach (['todo', 'in_progress', 'completed', 'canceled'] as $key)
            <div class="total-pill" style="background:var(--paper); border:1px solid var(--border);">
                <x-core::badge :color="$labels[$key]['color']" size="xs">{{ $labels[$key]['bn'] }} / {{ $labels[$key]['en'] }}</x-core::badge>
                <b>{{ $counts[$key] ?? 0 }}</b>
            </div>
        @endforeach
    </div>

    <div style="display:grid; grid-template-columns:minmax(280px, 340px) 1fr; gap:16px; align-items:start; margin-top:16px;">
        <div class="panel" style="margin-top:0;">
            <div class="panel-head"><div class="panel-title"><span class="bn">নতুন টাস্ক</span><span class="en" style="display:none;">New Task</span></div></div>
            <div class="panel-body">
                <form method="POST" action="{{ route('tasks.store') }}" style="display:flex; flex-direction:column; gap:12px;">
                    @csrf
                    <x-core::input size="sm" name="title" label="শিরোনাম" label-en="Title" :value="old('title')" :required="true" />
                    <x-core::textarea size="sm" name="description" label="সংক্ষিপ্ত বিবরণ" label-en="Short Description" rows="3" :value="old('description')" />
                    @if ($canAssign)
                        <x-core::select size="sm" name="assigned_to" label="কাকে দেওয়া হবে" label-en="Assign To" :options="$people->all()" :value="old('assigned_to', $me)" />
                    @else
                        <p style="font-size:12px; color:var(--ink-500); margin:0;"><span class="bn">এটি আপনার নিজের করণীয় তালিকায় যোগ হবে।</span><span class="en" style="display:none;">This goes on your own to-do list.</span></p>
                    @endif
                    <x-core::input size="sm" type="date" name="deadline" label="শেষ সময়" label-en="Deadline" :value="old('deadline')" />
                    <div><x-core::button type="submit" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">যোগ করুন</span><span class="en" style="display:none;">Add Task</span></x-core::button></div>
                </form>
            </div>
        </div>

        <div>
            <div class="tabbar" style="margin-top:0;">
                @foreach ($tabs as $key => [$bn, $en])
                    <a href="{{ route('tasks.index', ['tab' => $key]) }}" class="tabbtn {{ $tab === $key ? 'active' : '' }}"><span class="bn">{{ $bn }}</span><span class="en">{{ $en }}</span></a>
                @endforeach
            </div>

            <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:nowrap; margin:12px 0;">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div style="flex:1; min-width:160px;"><x-core::input size="sm" :no-margin="true" name="q" :value="request('q')" placeholder="খুঁজুন..." placeholder-en="Search..." /></div>
                <div style="width:170px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="status" :value="$status"
                    :options="['open' => 'চলমান ও করণীয় (Open)', '' => 'সব (All)'] + $statusOptions" /></div>
                @if ($tab !== 'mine')
                    <div style="width:200px; flex-shrink:0;"><x-core::select size="sm" :no-margin="true" name="assignee" :value="request('assignee')" :options="['' => 'সব কর্মী (Everyone)'] + $people->all()" /></div>
                @endif
                <x-core::button type="submit" size="sm" variant="secondary" icon="filter"><span class="bn">দেখুন</span><span class="en" style="display:none;">Filter</span></x-core::button>
            </form>

            @if ($errors->any())
                <div style="color:var(--red-600); font-size:12.5px; margin-bottom:10px;">{{ $errors->first() }}</div>
            @endif

            <div class="table-container table-teal">
                <div class="table-responsive">
                    <table class="app-table">
                        <thead>
                            <tr>
                                <th><span class="bn">টাস্ক</span><span class="en" style="display:none;">Task</span></th>
                                <th><span class="bn">দায়িত্বপ্রাপ্ত</span><span class="en" style="display:none;">Assigned To</span></th>
                                <th><span class="bn">রিপোর্টার</span><span class="en" style="display:none;">Reported By</span></th>
                                <th><span class="bn">শেষ সময়</span><span class="en" style="display:none;">Deadline</span></th>
                                <th><span class="bn">সম্পন্ন</span><span class="en" style="display:none;">Finished</span></th>
                                <th style="width:170px;"><span class="bn">অবস্থা</span><span class="en" style="display:none;">Status</span></th>
                                <th class="table-cell-right"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tasks as $task)
                                <tr>
                                    <td>
                                        <div style="font-weight:600;">{{ $task->title }}
                                            @if ($task->isPersonal())
                                                <x-core::badge color="grey" size="xs" label="নিজের" label-en="Personal" />
                                            @endif
                                        </div>
                                        @if ($task->description)
                                            <div style="font-size:12px; color:var(--ink-600); max-width:420px;">{{ \Illuminate\Support\Str::limit($task->description, 160) }}</div>
                                        @endif
                                    </td>
                                    <td style="font-size:12.5px;">{{ $task->assignee?->name ?? '—' }}</td>
                                    <td style="font-size:12.5px;">{{ $task->reporter?->name ?? '—' }}</td>
                                    <td style="white-space:nowrap; font-size:12.5px; {{ $task->isOverdue() ? 'color:var(--red-600); font-weight:700;' : '' }}">
                                        {{ $task->deadline?->format('d M, Y') ?? '—' }}
                                        @if ($task->isOverdue())
                                            <div style="font-size:11px;"><span class="bn">সময় পেরিয়েছে</span><span class="en" style="display:none;">Overdue</span></div>
                                        @endif
                                    </td>
                                    <td style="white-space:nowrap; font-size:12.5px;">{{ $task->finished_at?->format('d M, Y h:i A') ?? '—' }}</td>
                                    <td>
                                        @if ($task->canChangeStatusBy(auth()->user()))
                                            <form method="POST" action="{{ route('tasks.status', $task) }}">
                                                @csrf
                                                @method('PATCH')
                                                <x-core::select size="sm" :no-margin="true" name="status" :value="$task->status" :options="$statusOptions" class="task-status-select" />
                                            </form>
                                        @else
                                            <x-core::badge :color="$labels[$task->status]['color']" size="xs">{{ $labels[$task->status]['bn'] }} / {{ $labels[$task->status]['en'] }}</x-core::badge>
                                        @endif
                                    </td>
                                    <td class="table-cell-right">
                                        @if ($task->canBeEditedBy(auth()->user()))
                                            <div style="display:flex; gap:4px; justify-content:flex-end;">
                                                <x-core::button as="a" href="{{ route('tasks.edit', $task) }}" size="sm" variant="soft" color="primary" icon="edit" icon-only title="সম্পাদনা / Edit" />
                                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="delete-form" data-title="টাস্ক মুছে ফেলবেন?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-core::button type="submit" size="sm" variant="soft" color="danger" icon="trash-2" icon-only title="মুছুন / Delete" />
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><x-core::table.empty icon="check-circle" title="কোনো টাস্ক নেই" title-en="No tasks" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div style="margin-top:12px;">{{ $tasks->links() }}</div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function () {
                $(document).on('change', '.task-status-select, form[action*="/status"] select[name="status"]', function () {
                    $(this).closest('form').trigger('submit');
                });
            });
        </script>
    @endpush
</x-core::layout>
