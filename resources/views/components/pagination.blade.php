@if ($paginator->hasPages())
    <nav aria-label="{{ __('Pagination Navigation') }}">
        @if ($paginator->onFirstPage())
            <span class="pagination-disabled" aria-label="{{ __('pagination.previous') }}"><i class="bi bi-chevron-left" aria-hidden="true"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pagination-ellipsis" aria-disabled="true">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        @else
            <span class="pagination-disabled" aria-label="{{ __('pagination.next') }}"><i class="bi bi-chevron-right" aria-hidden="true"></i></span>
        @endif
    </nav>
@endif
