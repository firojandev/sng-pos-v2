{{-- One chart-of-accounts row and, below it, its children (indented). --}}
<tr>
    <td style="font-family:var(--font-mono, monospace); padding-left:{{ 12 + $depth * 22 }}px;">{{ $account->code }}</td>
    <td style="padding-left:{{ 12 + $depth * 22 }}px; {{ $account->is_group ? 'font-weight:700; color:var(--ink-900);' : 'color:var(--ink-800);' }}">
        {{ $account->name }}
        @unless ($account->is_active)
            <x-core::badge color="grey" size="xs" label="নিষ্ক্রিয়" label-en="Inactive" />
        @endunless
    </td>
    <td>
        @php $typeLabels = ['asset' => 'সম্পদ / Asset', 'liability' => 'দায় / Liability', 'equity' => 'ইকুইটি / Equity', 'income' => 'আয় / Income', 'expense' => 'ব্যয় / Expense']; @endphp
        <span style="font-size:12px; color:var(--ink-600);">{{ $typeLabels[$account->type] ?? $account->type }}</span>
    </td>
    <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">
        @unless ($account->is_group)
            {{ number_format($balances[$account->id] ?? 0, 2) }}
        @endunless
    </td>
    <td class="table-cell-right">
        @can('accounting.edit')
            <x-core::button :href="route('ledger-accounts.edit', $account)" size="sm" variant="soft" color="primary" icon="edit" icon-only title="সম্পাদনা / Edit" />
        @endcan
    </td>
</tr>
@foreach ($childrenByParent[$account->id] ?? [] as $child)
    @include('accounting::accounts._row', ['account' => $child, 'depth' => $depth + 1])
@endforeach
