@if (($type ?? 'customer') === 'supplier')
    @include('core::due-ledger.purchase')
@else
    @include('core::due-ledger.sales')
@endif
