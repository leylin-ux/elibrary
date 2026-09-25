@extends('layouts.app')

@section('title', __('បណ្តុំសៀវភៅ') . ' - E-Library')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Header Navigation (Matching Screenshot) -->
    <div class="space-y-2">
        <nav class="flex items-center gap-2 text-xs sm:text-sm text-slate-400 font-khmer pb-0.5">
            <a href="{{ route('books.index') }}" class="hover:text-slate-700 transition-colors">
                {{ __('បណ្ណាល័យ') }}
            </a>
            <span class="text-slate-300">/</span>
            <span class="text-slate-800 font-semibold">{{ __('បណ្តុំសៀវភៅ') }}</span>
        </nav>

        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-khmer tracking-tight">
                    {{ __('បណ្តុំសៀវភៅ') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 font-khmer mt-1 max-w-2xl">
                    {{ __('សៀវភៅដែលបានរៀបជាក្រុមតាមមហាវិទ្យាល័យ និងតាមជំនាញសិក្សា ដើម្បីងាយស្រួលស្វែងរក។') }}
                </p>
            </div>

            <!-- Back to Catalog Button -->
            <a href="{{ route('books.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs sm:text-sm font-semibold shadow-2xs transition-all shrink-0 self-start sm:self-auto font-khmer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>{{ __('ត្រឡប់ទៅបណ្ណាល័យ') }}</span>
            </a>
        </div>
    </div>

    <!-- 5-Column Responsive Grid (Matching Latest Releases / x-book-card) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 sm:gap-4 pt-2">
        @foreach($bookBundles as $bundle)
            <a href="{{ $bundle['url'] }}" 
               class="bg-white rounded-2xl p-3 border border-slate-100 shadow-2xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between group cursor-pointer">
                
                <div>
                    <!-- Cover Top Area: Matching aspect ratio aspect-[3/4.2] of book cards -->
                    <div class="relative aspect-[3/4.2] rounded-xl overflow-hidden bg-gradient-to-b {{ $bundle['bg_gradient'] }} p-2.5 mb-2.5 flex items-center justify-center shadow-inner">
                        @php
                            $getCover = function($img) {
                                if (!$img) return 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80';
                                return str_starts_with($img, 'http://') || str_starts_with($img, 'https://') ? $img : asset(ltrim($img, '/'));
                            };
                        @endphp

                        @if(($bundle['type'] ?? 'single') === 'stack' && isset($bundle['extra_images']) && count($bundle['extra_images']) > 0)
                            <!-- Stacked book 1 (Left tilted) -->
                            <img src="{{ $getCover($bundle['extra_images'][0]) }}" 
                                 alt=""
                                 loading="lazy"
                                 class="absolute -rotate-12 -translate-x-3.5 h-[76%] aspect-[1/1.4] object-contain rounded-md shadow-md opacity-85 group-hover:-translate-x-4.5 transition-all duration-300">
                            
                            <!-- Stacked book 2 (Right tilted) -->
                            @if(isset($bundle['extra_images'][1]))
                                <img src="{{ $getCover($bundle['extra_images'][1]) }}" 
                                     alt=""
                                     loading="lazy"
                                     class="absolute rotate-12 translate-x-3.5 h-[76%] aspect-[1/1.4] object-contain rounded-md shadow-md opacity-85 group-hover:translate-x-4.5 transition-all duration-300">
                            @endif
                            
                            <!-- Center front book (Real Book Cover) -->
                            <img src="{{ $getCover($bundle['cover_image']) }}" 
                                 alt="{{ $bundle['title'] }}"
                                 loading="lazy"
                                 class="relative z-10 h-[84%] aspect-[1/1.4] object-contain rounded-md shadow-lg group-hover:scale-105 transition-all duration-300">
                        @else
                            <img src="{{ $getCover($bundle['cover_image']) }}" 
                                 alt="{{ $bundle['title'] }}"
                                 loading="lazy"
                                 class="w-full h-full object-contain rounded-lg shadow-sm group-hover:scale-[1.03] transition-transform duration-300">
                        @endif

                        <!-- Bundle Pill Badge on top left -->
                        <div class="absolute top-2 left-2 z-20">
                            <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#1E3A8A]/90 backdrop-blur-xs text-white shadow-xs">
                                📚 {{ __('បណ្តុំ') }}
                            </span>
                        </div>

                        <!-- Count pill on top right -->
                        <div class="absolute top-2 right-2 z-20">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/90 backdrop-blur-xs text-white shadow-xs">
                                {{ $bundle['count'] ?? count($bundle['extra_images'] ?? []) + 1 }} {{ __('ក្បាល') }}
                            </span>
                        </div>
                    </div>

                    <!-- Bundle Title (2 lines clamp, min-h-[38px] matching book-card) -->
                    <h3 class="font-bold text-slate-800 text-[13px] line-clamp-2 leading-snug min-h-[38px] group-hover:text-[#1E3A8A] transition-colors" title="{{ $bundle['title'] }}">
                        {{ $bundle['title'] }}
                    </h3>

                    <!-- Subtitle matching book-card author styling -->
                    <p class="text-[11px] text-slate-400 mt-1 truncate">
                        {{ $bundle['count_label'] ?? __('សៀវភៅ :count ក្បាល', ['count' => $bundle['count']]) }}
                    </p>
                </div>

                <!-- Bottom Footer Stats Bar matching book-card -->
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2.5 pt-2 border-t border-slate-100/70">
                    <span class="inline-flex items-center gap-1 text-sky-700 font-medium group-hover:underline text-[11px]">
                        <span>{{ __('មើលបណ្តុំ') }}</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                    <span class="font-medium font-mono text-[10px] text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">
                        {{ $bundle['count'] ?? 4 }} {{ __('Books') }}
                    </span>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
