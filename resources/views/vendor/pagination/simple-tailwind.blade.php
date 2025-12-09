@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">

        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-lg cursor-not-allowed opacity-50" 
                  style="background-color: var(--bg-card); color: var(--text-muted); border-color: var(--border-color);">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-lg transition-colors hover:opacity-80" 
               style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color);">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" 
               class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-lg transition-colors hover:opacity-80" 
               style="background-color: var(--bg-card); color: var(--text-primary); border-color: var(--border-color);">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium border rounded-lg cursor-not-allowed opacity-50" 
                  style="background-color: var(--bg-card); color: var(--text-muted); border-color: var(--border-color);">
                {!! __('pagination.next') !!}
            </span>
        @endif

    </nav>
@endif
