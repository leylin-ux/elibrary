@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between w-full py-2">
        {{-- Mobile View --}}
        <div class="flex items-center justify-between w-full sm:hidden gap-2">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    &laquo; {{ __('Previous') }}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-[#1E3A8A] transition-colors shadow-2xs">
                    &laquo; {{ __('Previous') }}
                </a>
            @endif

            <span class="text-xs text-slate-500 font-medium">
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-[#1E3A8A] transition-colors shadow-2xs">
                    {{ __('Next') }} &raquo;
                </a>
            @else
                <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-400 bg-slate-50 border border-slate-200 rounded-xl cursor-not-allowed">
                    {{ __('Next') }} &raquo;
                </span>
            @endif
        </div>

        {{-- Desktop / Tablet View (Left: Results text, Right: Pagination buttons) --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full gap-4">
            <div>
                <p class="text-xs text-slate-500 font-medium">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-800 font-mono">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-bold text-slate-800 font-mono">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-bold text-slate-800 font-mono">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div class="flex items-center justify-end">
                <span class="inline-flex items-center -space-x-px rounded-xl border border-slate-200 bg-white shadow-2xs overflow-hidden text-xs">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="{{ __('Previous') }}" class="px-2.5 py-2 text-slate-300 bg-slate-50 cursor-not-allowed border-r border-slate-200 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-2.5 py-2 text-slate-600 hover:bg-slate-50 hover:text-[#1E3A8A] transition-colors border-r border-slate-200 flex items-center justify-center" aria-label="{{ __('Previous') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="px-3.5 py-2 text-slate-400 font-mono border-r border-slate-200 bg-slate-50 flex items-center justify-center" aria-disabled="true">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="px-3.5 py-2 font-bold bg-[#1E3A8A] text-white border-r border-[#1E3A8A] flex items-center justify-center shadow-xs">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="px-3.5 py-2 font-semibold text-slate-600 hover:bg-slate-50 hover:text-[#1E3A8A] transition-colors border-r border-slate-200 flex items-center justify-center" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-2.5 py-2 text-slate-600 hover:bg-slate-50 hover:text-[#1E3A8A] transition-colors flex items-center justify-center" aria-label="{{ __('Next') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="{{ __('Next') }}" class="px-2.5 py-2 text-slate-300 bg-slate-50 cursor-not-allowed flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
