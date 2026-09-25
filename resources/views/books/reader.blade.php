@extends('layouts.app')

@section('title', __('Read Online') . ' - ' . $book->title)

@section('content')
<div class="space-y-4" x-data="{ isFullscreen: false }">
    <!-- Top Reader Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('books.show', $book) }}" 
               class="p-2 bg-slate-100 hover:bg-[#1E3A8A] text-slate-600 hover:text-white rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-[#6366F1]">
                    📖 {{ __('In-App Reader') }} &bull; {{ $book->category ? __($book->category->name) : __('General') }}
                </span>
                <h1 class="text-base sm:text-lg font-bold text-slate-900 line-clamp-1">{{ $book->title }}</h1>
                <p class="text-xs text-slate-400">{{ $book->author }} ({{ $book->published_year ?? 'N/A' }})</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <!-- Protection Indicator Badge -->
            @if($book->allow_pdf_download)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Download Allowed') }}</span>
                </span>
                <a href="{{ $pdfUrl }}" target="_blank" download 
                   class="px-3 py-1.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-xs flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Download') }}</span>
                </a>
            @else
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5" title="{{ __('Protected document: Download and print are restricted by library policy.') }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>{{ __('In-App View Only') }}</span>
                </span>
            @endif

            <!-- Fullscreen Button -->
            <button @click="isFullscreen = !isFullscreen" 
                    class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold flex items-center gap-1 transition-colors"
                    title="{{ __('Toggle Fullscreen') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </button>
        </div>
    </div>

    <!-- Embedded PDF Viewer Frame -->
    <div :class="isFullscreen ? 'fixed inset-0 z-50 bg-slate-900 p-2 sm:p-4' : 'relative h-[78vh] rounded-3xl overflow-hidden bg-slate-100 border border-slate-200 shadow-md'"
         class="transition-all duration-300">
        
        <!-- Fullscreen close bar -->
        <div x-show="isFullscreen" class="flex justify-between items-center bg-slate-800 text-white p-3 rounded-t-2xl mb-2" x-cloak>
            <span class="font-bold text-sm">{{ $book->title }}</span>
            <button @click="isFullscreen = false" class="text-slate-300 hover:text-white p-1 rounded-lg hover:bg-slate-700">
                &times; {{ __('Exit Fullscreen') }}
            </button>
        </div>

        <iframe src="{{ $pdfUrl }}{{ $book->allow_pdf_download ? '' : '#toolbar=0&navpanes=0&scrollbar=1' }}" 
                class="w-full h-full rounded-2xl bg-white" 
                frameborder="0"
                allow="fullscreen">
        </iframe>
    </div>
</div>
@endsection
