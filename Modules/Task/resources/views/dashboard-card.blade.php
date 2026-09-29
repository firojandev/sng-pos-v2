@php $labels = \Modules\Task\Models\Task::statusLabels(); @endphp

<div class="panel" style="margin-top:16px;">
    <div class="panel-head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="panel-title"><span class="bn">আমার টাস্ক</span><span class="en" style="display:none;">My Tasks</span>
            <span style="font-size:12px; font-weight:500; color:var(--ink-500);">· {{ $myTasks['open'] }} <span class="bn">চলমান</span><span class="en" style="display:none;">open</span>{{ $myTasks['overdue'] ? ' · ' : '' }}@if ($myTasks['overdue'])<span style="color:var(--red-600);">{{ $myTasks['overdue'] }} <span class="bn">সময় পেরিয়েছে</span><span class="en" style="display:none;">overdue</span></span>@endif</span>
        </div>
        <x-core::button as="a" href="{{ route('tasks.index') }}" size="sm" variant="secondary" icon="list"><span class="bn">সব টাস্ক</span><span class="en" style="display:none;">All Tasks</span></x-core::button>
    </div>
    <div class="table-responsive">
        <table class="app-table">
            <tbody>
                @forelse ($myTasks['tasks'] as $task)
                    <tr>
                        <td style="font-weight:600;">{{ $task->title }}
                            <div style="font-size:11.5px; color:var(--ink-500); font-weight:400;">{{ $task->isPersonal() ? 'নিজের / Personal' : ($task->reporter?->name ? 'রিপোর্টার / By '.$task->reporter->name : '') }}</div>
                        </td>
                        <td style="white-space:nowrap; font-size:12.5px; {{ $task->isOverdue() ? 'color:var(--red-600); font-weight:700;' : '' }}">{{ $task->deadline?->format('d M, Y') ?? '—' }}</td>
                        <td style="width:170px;">
                            <form method="POST" action="{{ route('tasks.status', $task) }}">
                                @csrf
                                @method('PATCH')
                                <x-core::select size="sm" :no-margin="true" name="status" :value="$task->status" :options="collect($labels)->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" onchange="this.form.submit()" />
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3"><x-core::table.empty icon="check-circle" title="কোনো চলমান টাস্ক নেই" title-en="No open tasks" /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
