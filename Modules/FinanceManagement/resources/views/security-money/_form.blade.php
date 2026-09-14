<x-core::input
    name="receiver_name"
    label="গ্রহীতার নাম"
    label-en="Receiver Name"
    size="sm"
    :value="$securityMoney->receiver_name"
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
        :value="$securityMoney->amount"
        :required="true"
        :stepper="false"
    />

    <x-core::input
        name="date"
        type="date"
        label="তারিখ"
        label-en="Date"
        size="sm"
        :value="optional($securityMoney->date)->format('Y-m-d') ?? now()->format('Y-m-d')"
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
        <option value="paid" @selected($securityMoney->status !== 'received')>প্রদত্ত (Paid)</option>
        <option value="received" @selected($securityMoney->status === 'received')>গৃহীত (Received)</option>
    </x-core::select>

    <x-core::select
        name="account_id"
        label="অ্যাকাউন্ট"
        label-en="Account"
        size="sm"
    >
        <option value="">-- ডিফল্ট অ্যাকাউন্ট --</option>
        @foreach ($accounts as $acc)
            <option value="{{ $acc->id }}" @selected($securityMoney->account_id === $acc->id)>{{ $acc->display_name }} ({{ $acc->typeLabel()['bn'] }})</option>
        @endforeach
    </x-core::select>
</div>

<x-core::textarea
    name="note"
    label="নোট"
    label-en="Note"
    rows="2"
    size="sm"
    :value="$securityMoney->note"
/>
