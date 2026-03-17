<nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="w-full">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="text-sm font-medium text-gray-600">
            Showing page {{ number_format($paginator->currentPage()) }} of {{ number_format(max($paginator->lastPage(), 1)) }}
            <span class="text-gray-400">({{ number_format($paginator->total()) }} total records)</span>
        </p>

        <div class="flex flex-wrap items-center justify-start gap-2 lg:justify-end">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">
                    &laquo; Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">
                    &laquo; Prev
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span aria-disabled="true" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-[#f8faf9] px-3 text-sm font-medium text-gray-400 shadow-sm">
                        {{ $element }}
                    </span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-[#1a3a2d] bg-[#1a3a2d] px-3 text-sm font-semibold text-white shadow-[0_12px_24px_-14px_rgba(26,58,45,0.8)]">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="inline-flex h-10 min-w-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 text-sm font-medium text-gray-600 shadow-sm transition hover:border-[#1a3a2d]/25 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex h-10 items-center rounded-xl border border-[#1a3a2d]/15 bg-white px-4 text-sm font-medium text-[#496255] shadow-sm transition hover:border-[#1a3a2d]/30 hover:bg-[#f5faf7] hover:text-[#1a3a2d]">
                    Next &raquo;
                </a>
            @else
                <span aria-disabled="true" class="inline-flex h-10 items-center rounded-xl border border-gray-200 bg-white px-4 text-sm font-medium text-gray-400 shadow-sm cursor-default">
                    Next &raquo;
                </span>
            @endif
        </div>
    </div>
</nav>