@if ($paginator->hasPages())
<nav class="admin-pagination" aria-label="Pagination">
    <div class="admin-pagination-info">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }} results
    </div>
    <div class="admin-pagination-links">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="admin-page-btn admin-page-btn--disabled">‹ Prev</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="admin-page-btn" rel="prev">‹ Prev</a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="admin-page-btn admin-page-btn--dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="admin-page-btn admin-page-btn--active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="admin-page-btn">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="admin-page-btn" rel="next">Next ›</a>
        @else
            <span class="admin-page-btn admin-page-btn--disabled">Next ›</span>
        @endif
    </div>
</nav>
@endif
