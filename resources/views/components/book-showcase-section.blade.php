@props([
    'title',
    'icon' => '✨',
    'iconBg' => 'bg-amber-50 text-amber-500 border-amber-100/70',
    'books' => [],
    'viewAllUrl' => null,
])

<div class="space-y-3.5 mb-8">
    <!-- Header: Icon + Title on left, View All button on right -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl {{ $iconBg }} flex items-center justify-center text-base shadow-2xs border">
                {{ $icon }}
            </div>
            <h2 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight font-khmer">
                {{ $title }}
            </h2>
        </div>

        @if($viewAllUrl)
            <a href="{{ $viewAllUrl }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-600 hover:text-slate-900 text-xs font-semibold shadow-2xs transition-all group">
                <span>{{ __('បង្ហាញទាំងអស់') }}</span>
                <span class="text-slate-400 group-hover:text-slate-700 group-hover:translate-x-0.5 transition-all">&rarr;</span>
            </a>
        @endif
    </div>

    <!-- 5-Card Responsive Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 sm:gap-4">
        @forelse($books as $book)
            <x-book-card :book="$book" />
        @empty
            <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                {{ __('No books available in this section.') }}
            </div>
        @endforelse
    </div>
</div>
