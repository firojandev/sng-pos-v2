<x-core::input
    name="borrower_name"
    label="গ্রহীতার নাম"
    label-en="Borrower Name"
    size="sm"
    :value="$lend->borrower_name"
    :required="true"
/>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
    <x-core::input
        name="amount"
        type="number"
        step="0.01"
        min="0"
        label="পরিমাণ (৳)"
        label-en="Amount (৳)"
        prefix="৳"
        size="sm"
        :value="$lend->amount"
        :required="true"
        :stepper="false"
    />

    <x-core::input
        name="date"
        type="date"
        label="তারিখ"
        label-en="Date"
        size="sm"
        :value="optional($lend->date)->format('Y-m-d') ?? now()->format('Y-m-d')"
        :required="true"
    />
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
    <x-core::select
        name="status"
        label="অবস্থা"
        label-en="Status"
        size="sm"
        :required="true"
    >
        <option value="due" @selected($lend->status !== 'received')>বাকি (Due)</option>
        <option value="received" @selected($lend->status === 'received')>ফেরত পাওয়া (Received)</option>
    </x-core::select>

    <x-core::select
        name="account_id"
        label="অ্যাকাউন্ট"
        label-en="Account"
        size="sm"
    >
        <option value="">-- ডিফল্ট অ্যাকাউন্ট --</option>
        @foreach ($accounts as $acc)
            <option value="{{ $acc->id }}" @selected($lend->account_id === $acc->id)>{{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }})</option>
        @endforeach
    </x-core::select>
</div>

<x-core::textarea
    name="note"
    label="নোট"
    label-en="Note"
    rows="2"
    size="sm"
    :value="$lend->note"
/>
