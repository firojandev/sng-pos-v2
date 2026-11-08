{{-- A statement section: its accounts and a total line. --}}
<tr><td colspan="2" style="background:var(--paper); font-weight:700; color:var(--ink-900);">{{ $title }}</td></tr>
@foreach ($rows as $row)
    <tr>
        <td style="padding-left:24px;">{{ $row['account']->code }} — {{ $row['account']->name }}</td>
        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($row['amount'], 2) }}</td>
    </tr>
@endforeach
@foreach ($extraRows ?? [] as $label => $amount)
    <tr>
        <td style="padding-left:24px;">{{ $label }}</td>
        <td class="table-cell-right" style="font-family:var(--font-mono, monospace);">{{ number_format($amount, 2) }}</td>
    </tr>
@endforeach
<tr>
    <td style="text-align:right; font-weight:700;">{{ $totalLabel }}</td>
    <td class="table-cell-right" style="font-family:var(--font-mono, monospace); font-weight:700; border-top:1px solid var(--border);">{{ number_format($total, 2) }}</td>
</tr>
