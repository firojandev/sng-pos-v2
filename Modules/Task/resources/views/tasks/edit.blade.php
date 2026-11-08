<x-core::layout title="টাস্ক সম্পাদনা" title-en="Edit Task" :subtitle="$task->title" :subtitle-en="$task->title" active="tasks">
    <div class="panel" style="margin-top:16px; max-width:560px;">
        <div class="panel-body">
            <form method="POST" action="{{ route('tasks.update', $task) }}" style="display:flex; flex-direction:column; gap:12px;">
                @csrf
                @method('PUT')
                <x-core::input size="sm" name="title" label="শিরোনাম" label-en="Title" :value="old('title', $task->title)" :required="true" />
                <x-core::textarea size="sm" name="description" label="সংক্ষিপ্ত বিবরণ" label-en="Short Description" rows="4" :value="old('description', $task->description)" />
                @if ($canAssign)
                    <x-core::searchable-select size="sm" name="assigned_to" label="কাকে দেওয়া হবে" label-en="Assign To" placeholder="নাম বা পদবি দিয়ে খুঁজুন" placeholder-en="Search by name or designation" :options="$people->all()" :value="old('assigned_to', $task->assigned_to)" />
                @endif
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                    <x-core::select size="sm" name="priority" label="অগ্রাধিকার" label-en="Priority" :options="collect(\Modules\Task\Models\Task::priorityLabels())->map(fn ($label) => $label['bn'].' ('.$label['en'].')')->all()" :value="old('priority', $task->priority)" />
                    <x-core::input size="sm" type="date" name="deadline" label="শেষ সময়" label-en="Deadline" :value="old('deadline', $task->deadline?->toDateString())" />
                </div>
                <div style="display:flex; gap:8px;">
                    <x-core::button type="submit" size="sm" variant="solid" color="primary"><span class="bn">সংরক্ষণ</span><span class="en" style="display:none;">Save</span></x-core::button>
                    <x-core::button as="a" href="{{ route('tasks.index') }}" size="sm" variant="secondary"><span class="bn">ফিরে যান</span><span class="en" style="display:none;">Back</span></x-core::button>
                </div>
            </form>
        </div>
    </div>
</x-core::layout>
