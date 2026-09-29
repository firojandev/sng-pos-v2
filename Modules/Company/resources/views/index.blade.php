<x-core::layout
    title="কোম্পানিসমূহ"
    title-en="Companies"
    subtitle="কোম্পানি ও তাদের অধীনস্থ দোকান পরিচালনা করুন"
    subtitle-en="Manage companies and the shops they run"
    active="companies"
>
    <div class="section-row" style="margin-bottom:16px; display:flex; justify-content:flex-end;">
        <x-core::button as="a" href="{{ route('companies.create') }}" size="sm" variant="solid" color="primary" icon="plus"><span class="bn">নতুন কোম্পানি</span><span class="en" style="display:none;">New Company</span></x-core::button>
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            {!! $dataTable->table(['class' => 'app-table', 'id' => 'companies-data-table']) !!}
        </div>
    </div>

    @push('scripts')
        {!! $dataTable->scripts() !!}
    @endpush
</x-core::layout>
