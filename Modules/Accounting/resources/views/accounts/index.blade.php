<x-core::layout
    title="হিসাবের তালিকা"
    title-en="Chart of Accounts"
    subtitle="কোম্পানির লেজার অ্যাকাউন্ট ও তাদের ব্যালেন্স"
    subtitle-en="The company's ledger accounts and their balances"
    active="ledger-accounts"
>
    <x-accounting::tabbar active="accounts" />

    <div class="section-row" style="margin:16px 0;">
        <div class="filters"></div>
        @can('accounting.edit')
            <x-core::button as="a" href="{{ route('ledger-accounts.create') }}" size="sm" variant="solid" color="primary" icon="plus">
                <span class="bn">নতুন অ্যাকাউন্ট</span>
                <span class="en" style="display:none;">New Account</span>
            </x-core::button>
        @endcan
    </div>

    <div class="table-container table-teal">
        <div class="table-responsive">
            <table class="app-table">
                <thead>
                    <tr>
                        <th style="width:120px;"><span class="bn">কোড</span><span class="en" style="display:none;">Code</span></th>
                        <th><span class="bn">অ্যাকাউন্ট</span><span class="en" style="display:none;">Account</span></th>
                        <th><span class="bn">ধরন</span><span class="en" style="display:none;">Type</span></th>
                        <th class="table-cell-right"><span class="bn">ব্যালেন্স</span><span class="en" style="display:none;">Balance</span></th>
                        <th class="table-cell-right"><span class="bn">অ্যাকশন</span><span class="en" style="display:none;">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roots as $root)
                        @include('accounting::accounts._row', ['account' => $root, 'depth' => 0])
                    @empty
                        <tr>
                            <td colspan="5"><x-core::table.empty icon="file-text" title="কোনো অ্যাকাউন্ট নেই" title-en="No accounts" /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-core::layout>
