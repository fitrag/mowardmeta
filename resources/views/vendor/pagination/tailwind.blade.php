@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <div class="flex-1 flex items-center justify-between">
            <div>
                <p class="text-sm" style="color: var(--text-secondary);">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium" style="color: var(--text-primary);">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium" style="color: var(--text-primary);">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium" style="color: var(--text-primary);">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="px-3 py-2 text-sm rounded-lg cursor-not-allowed opacity-50" style="background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-2 text-sm rounded-lg transition-colors" style="background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                <div class="hidden sm:flex items-center gap-1">
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <span class="px-3 py-2 text-sm" style="color: var(--text-secondary);">{{ $element }}</span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span class="px-3 py-2 text-sm font-medium text-primary-600 bg-primary-500/20 rounded-lg" style="border: 1px solid rgba(99, 102, 241, 0.3);">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="px-3 py-2 text-sm rounded-lg transition-colors" style="background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-2 text-sm rounded-lg transition-colors" style="background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                @else
                    <span class="px-3 py-2 text-sm rounded-lg cursor-not-allowed opacity-50" style="background-color: var(--bg-hover); color: var(--text-secondary); border: 1px solid var(--border-color);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
