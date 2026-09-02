<x-core::layout
    title="প্ল্যানসমূহ"
    title-en="Plans"
    subtitle="সাবস্ক্রিপশন প্ল্যানসমূহ ও রিসোর্স কোটা পরিচালনা করুন"
    subtitle-en="Manage subscription plans, quotas and features"
    active="plans"
>
    <div class="section-row" style="margin-bottom:16px;">
        <div class="filters"></div>
        <x-core::button
            as="a"
            href="{{ route('plans.create') }}"
            variant="solid"
            color="primary"
            size="sm"
            icon="plus"
        >
            <span class="bn">নতুন প্ল্যান তৈরি করুন</span>
            <span class="en">Create New Plan</span>
        </x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'plans-data-table']) !!}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}
    @endpush
</x-core::layout>
