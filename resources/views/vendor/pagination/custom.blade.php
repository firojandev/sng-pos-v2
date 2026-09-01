@if ($paginator->hasPages())
    <div class="pagination">
        <div>
            <span class="bn">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} টি, মোট {{ $paginator->total() }} টি</span>
            <span class="en" style="display:none;">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
        </div>

        <div class="pg-btns">
            @if ($paginator->onFirstPage())
                <button type="button" disabled aria-label="Previous">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pg-dots">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button type="button" class="active" aria-current="page">{{ $page }}</button>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            @else
                <button type="button" disabled aria-label="Next">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            @endif
        </div>
    </div>
@endif
