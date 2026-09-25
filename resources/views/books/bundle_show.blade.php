@extends('layouts.app')

@section('title', $bundle['title'] . ' - ' . __('បណ្តុំសៀវភៅ') . ' - E-Library')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header Navigation (Matching User Screenshot) -->
    <div class="space-y-2">
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-slate-400 font-khmer pb-0.5">
            <a href="{{ route('books.index') }}" class="hover:text-slate-700 transition-colors">
                {{ __('បណ្ណាល័យ') }}
            </a>
            <span class="text-slate-300">/</span>
            <a href="{{ route('books.bundles') }}" class="hover:text-slate-700 transition-colors">
                {{ __('បណ្តុំសៀវភៅ') }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-semibold">{{ $bundle['title'] }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $bundle['title'] }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 font-khmer mt-1">
                    {{ $bundle['count_label'] ?? __('សៀវភៅ :count ក្បាល', ['count' => count($books)]) }}
                </p>
            </div>

            <!-- Back to Bundles Button -->
            <a href="{{ route('books.bundles') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold shadow-2xs transition-all shrink-0 self-start sm:self-auto font-khmer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('ត្រឡប់ទៅបណ្តុំសៀវភៅ') }}</span>
            </a>
        </div>
    </div>

    <!-- Responsive Grid of Books (Matching Screenshot) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6 pt-2">
        @forelse($books as $book)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-2xs hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col overflow-hidden group">
                <!-- Top Gradient Card Area with Centered Book Cover -->
                <a href="{{ route('books.show', $book->id) }}" 
                   class="h-48 sm:h-56 w-full bg-gradient-to-b {{ $book->card_gradient ?? 'from-slate-100 to-blue-50/40' }} p-3.5 sm:p-4 flex items-center justify-center relative overflow-hidden cursor-pointer">
                    <img src="{{ $book->cover_image ? (str_starts_with($book->cover_image, 'http') ? $book->cover_image : asset(ltrim($book->cover_image, '/'))) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                         alt="{{ $book->title }}" 
                         loading="lazy"
                         class="h-36 sm:h-44 w-auto object-contain rounded-sm shadow-md group-hover:scale-105 transition-all duration-300">
                </a>

                <!-- Bottom Metadata Area: Title, Author, Views & Downloads -->
                <div class="p-4 sm:p-5 bg-white flex flex-col justify-between flex-1">
                    <div>
                        <a href="{{ route('books.show', $book->id) }}" 
                           class="text-xs sm:text-sm font-bold text-slate-800 hover:text-[#23677a] transition-colors font-khmer line-clamp-1 block" 
                           title="{{ $book->title }}">
                            {{ $book->title }}
                        </a>
                        <p class="text-[11px] sm:text-xs text-slate-400 font-khmer mt-1 truncate">
                            by {{ $book->author }}
                        </p>
                    </div>

                    <!-- Statistics: Views & Downloads -->
                    <div class="flex items-center gap-3.5 text-[11px] sm:text-xs text-slate-400 font-khmer mt-3 pt-2.5 border-t border-slate-100">
                        <span class="flex items-center gap-1.5" title="{{ __('ការចូលមើល') }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>{{ number_format($book->views_count ?? 0) }}</span>
                        </span>
                        <span class="flex items-center gap-1.5" title="{{ __('ការទាញយក') }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>{{ number_format($book->downloads_count ?? 0) }}</span>
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 text-slate-400 font-khmer">
                {{ __('មិនមានសៀវភៅនៅក្នុងបណ្តុំនេះនៅឡើយទេ។') }}
            </div>
        @endforelse
    </div>
</div>
@endsection
