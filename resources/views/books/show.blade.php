@extends('layouts.app')

@section('title', $book->title . ' - ' . __('Book Details'))

@php
    $userReservation = auth()->check() ? $book->reservations()->where('user_id', auth()->id())->whereIn('status', ['Pending', 'Approved'])->latest()->first() : null;
    $initialResStatus = $userReservation ? strtolower($userReservation->status) : 'none';
@endphp

@section('content')
<div class="space-y-6" x-data="{ 
    editModalOpen: false,
    deleteModalOpen: false,
    coverPreview: '{{ $book->cover_image }}',
    coverFileName: '',
    pdfFileName: '{{ $book->pdf_file ? basename($book->pdf_file) : '' }}',
    pdfCoverData: '',
    isExtractingCover: false,
    autoCoverExtracted: false,
    isDirectExtracting: false,
    currentBook: {
        id: '{{ $book->id }}',
        title: '{{ addslashes($book->title) }}',
        author: '{{ addslashes($book->author) }}',
        isbn: '{{ $book->isbn }}',
        category_id: '{{ $book->category_id }}',
        total_copies: {{ $book->total_copies }},
        available_copies: {{ $book->available_copies }},
        location_shelf: '{{ addslashes($book->location_shelf) }}',
        published_year: '{{ $book->published_year }}',
        cover_image: '{{ $book->cover_image }}',
        pdf_file: '{{ addslashes($book->pdf_file ?? '') }}',
        allow_pdf_download: {{ $book->allow_pdf_download ? 'true' : 'false' }},
        target_academic_year: '{{ $book->target_academic_year ?? '' }}',
        recommended_major: '{{ addslashes($book->recommended_major ?? '') }}',
        description: '{{ addslashes(str_replace(["\r", "\n"], [' ', ' '], $book->description ?? '')) }}'
    },
    async handlePdfSelected(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.pdfFileName = file.name;
        this.isExtractingCover = true;
        this.autoCoverExtracted = false;
        try {
            if (window.extractPdfCover) {
                const coverDataUrl = await window.extractPdfCover(file);
                this.coverPreview = coverDataUrl;
                this.pdfCoverData = coverDataUrl;
                this.autoCoverExtracted = true;
            }
        } catch (e) {
            console.error('Failed to extract PDF cover:', e);
        } finally {
            this.isExtractingCover = false;
        }
    },
    async extractCurrentPdfCover() {
        if (!this.currentBook.pdf_file) return;
        this.isExtractingCover = true;
        this.autoCoverExtracted = false;
        try {
            if (window.extractPdfCover) {
                const coverDataUrl = await window.extractPdfCover(this.currentBook.pdf_file);
                this.coverPreview = coverDataUrl;
                this.pdfCoverData = coverDataUrl;
                this.autoCoverExtracted = true;
            }
        } catch (e) {
            console.error('Failed to extract PDF cover from URL:', e);
            alert('Could not extract cover from this PDF file.');
        } finally {
            this.isExtractingCover = false;
        }
    },
    async extractDirectCover() {
        if (this.isDirectExtracting) return;
        this.isDirectExtracting = true;
        try {
            if (!window.extractPdfCover) throw new Error('PDF Cover extractor not ready');
            const pdfSource = '{{ $book->pdf_file ? (str_starts_with($book->pdf_file, 'http') ? $book->pdf_file : asset(ltrim($book->pdf_file, '/'))) : '' }}';
            if (!pdfSource) throw new Error('No PDF file for this book');
            const coverDataUrl = await window.extractPdfCover(pdfSource);
            const res = await fetch('{{ route('books.extract-cover', $book) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ pdf_cover_data: coverDataUrl })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.triggerToast('{{ __('🎉 បានស្រង់យករូបគម្របជោគជ័យ') }}', '{{ __('បានចាប់យករូបគម្របទំព័រទី១ ពី PDF និងរក្សាទុកក្នុងប្រព័ន្ធរួចរាល់!') }}', 'success');
                setTimeout(() => window.location.reload(), 900);
            } else {
                this.triggerToast('{{ __('បរាជ័យ') }}', data.message || '{{ __('មិនអាចស្រង់យករូបគម្របបានទេ។') }}', 'error');
            }
        } catch (err) {
            console.error(err);
            this.triggerToast('{{ __('បរាជ័យ') }}', '{{ __('មានបញ្ហាក្នុងការស្រង់យករូបគម្របពី PDF។') }}', 'error');
        } finally {
            this.isDirectExtracting = false;
        }
    },
    resState: '{{ $initialResStatus }}',
    isReserving: false,
    toastTitle: '',
    toastMessage: '',
    toastType: 'success',
    showToast: false,
    pollInterval: null,
    init() {
        @auth
            if (!{{ $layoutIsAdmin ? 'true' : 'false' }}) {
                this.pollInterval = setInterval(() => {
                    this.pollReservationStatus();
                }, 3500);
            }
        @endauth
    },
    async pollReservationStatus() {
        try {
            const res = await fetch('{{ route('reservations.user-status') }}', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            const bookId = {{ $book->id }};
            if (data.approved_book_ids && data.approved_book_ids.includes(bookId)) {
                if (this.resState !== 'approved') {
                    this.resState = 'approved';
                    this.triggerToast('{{ __('🎉 បានកក់ជោគជ័យ') }}', '{{ __('Admin បានទទួលការកក់សៀវភៅរបស់អ្នករួចរាល់ហើយ! លោកអ្នកអាចមកទទួលសៀវភៅបាន។') }}', 'success');
                }
            } else if (data.pending_book_ids && data.pending_book_ids.includes(bookId)) {
                this.resState = 'pending';
            }
        } catch (e) {}
    },
    triggerToast(title, msg, type = 'success') {
        this.toastTitle = title;
        this.toastMessage = msg;
        this.toastType = type;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 6000);
    },
    async quickReserve() {
        if (this.isReserving) return;
        this.isReserving = true;
        try {
            const res = await fetch('/books/{{ $book->id }}/reserve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ book_id: {{ $book->id }} })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.resState = 'pending';
                this.triggerToast('{{ __('កំពុងផ្ញើសារការកក់...') }}', data.message || '{{ __('ព័ត៌មានការកក់ត្រូវបានផ្ញើទៅកាន់ Admin រួចរាល់ហើយ។') }}', 'success');
            } else {
                this.triggerToast('{{ __('Hold Notice') }}', data.message || '{{ __('Could not reserve book.') }}', 'error');
            }
        } catch (e) {
            this.triggerToast('{{ __('Hold Notice') }}', '{{ __('An error occurred. Please try again.') }}', 'error');
        } finally {
            this.isReserving = false;
        }
    }
}">

    <!-- Top Navigation & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('books.index') }}" 
               class="p-2 bg-white rounded-xl border border-slate-200 text-slate-600 hover:text-[#1E3A8A] hover:border-blue-200 transition-colors shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-0.5">
                    <a href="{{ route('books.index') }}" class="hover:text-[#1E3A8A] transition-colors">{{ __('Books') }}</a>
                    <span>/</span>
                    <span class="text-slate-400 font-mono">ISBN: {{ $book->isbn ?? 'N/A' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#1E3A8A] tracking-tight line-clamp-1">{{ $book->title }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Read PDF Online -->
            <a href="{{ route('books.read', $book) }}" 
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-[#6366F1] text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span>{{ __('Read Online (PDF)') }}</span>
            </a>

            @if($book->allow_pdf_download && $book->pdf_file)
                <a href="{{ route('books.download', $book) }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs"
                   title="{{ __('Download PDF') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>{{ __('Download PDF') }}</span>
                </a>
            @endif

            @if($layoutIsAdmin)
                @if($book->pdf_file)
                    <button type="button" 
                            @click="extractDirectCover()" 
                            :disabled="isDirectExtracting"
                            title="{{ __('ស្វ័យប្រវត្តិចាប់យករូបគម្របទំព័រទី១ ពីឯកសារ PDF') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 text-indigo-700 text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs cursor-pointer">
                        <span x-show="!isDirectExtracting" class="flex items-center gap-1">
                            <span>⚡</span>
                            <span class="hidden sm:inline">{{ __('ស្រង់គម្របពី PDF') }}</span>
                        </span>
                        <span x-show="isDirectExtracting" class="flex items-center gap-1 animate-pulse">
                            <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span class="text-xs">{{ __('កំពុងស្រង់...') }}</span>
                        </span>
                    </button>
                @endif

                <!-- Edit Button -->
                <button @click="editModalOpen = true" 
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs">
                    <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    <span>{{ __('Edit Book') }}</span>
                </button>

                <!-- Issue Loan Button -->
                @if($book->available_copies > 0)
                    <a href="{{ route('borrows.index', ['book_id' => $book->id]) }}" 
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-md shadow-blue-950/20">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>{{ __('Issue Book') }}</span>
                    </a>
                @endif

                <!-- Delete Trigger (Exclusive Manager / Super Admin Right) -->
                @if($layoutUser?->canDeleteBooks())
                    <button @click="deleteModalOpen = true" 
                            class="p-2 bg-white border border-rose-200 text-rose-500 hover:bg-rose-50 hover:border-rose-300 rounded-xl transition-colors shadow-xs" 
                            title="{{ __('Delete Book') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                @endif
            @elseif($book->available_copies > 0)
                <!-- Member: 3 States (Approved / Pending / Ready to Reserve) -->
                <template x-if="resState === 'approved'">
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs sm:text-sm font-bold rounded-xl shadow-xs">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ __('✓ បានកក់ជោគជ័យ') }}</span>
                    </span>
                </template>
                <template x-if="resState === 'pending'">
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-50 text-amber-700 border border-amber-200 text-xs sm:text-sm font-bold rounded-xl shadow-xs animate-pulse">
                        <svg class="w-4 h-4 text-amber-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>{{ __('🕒 រង់ចាំ Admin ទទួលការកក់') }}</span>
                    </span>
                </template>
                <template x-if="resState === 'none'">
                    <button type="button" 
                            @click="quickReserve()"
                            :disabled="isReserving"
                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-md cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="isReserving">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <template x-if="!isReserving">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </template>
                        <span x-text="isReserving ? '{{ __('កំពុងផ្ញើសារការកក់...') }}' : '{{ __('Reserve This Book') }}'"></span>
                    </button>
                </template>
            @endif
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Cover & Quick Details (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Cover Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-5 flex flex-col items-center">
                <div class="relative w-full max-w-[260px] aspect-3/4 rounded-2xl overflow-hidden shadow-xl ring-1 ring-slate-200 group">
                    <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                         alt="{{ $book->title }}" 
                         class="w-full h-full object-cover">
                    
                    <!-- Shelf Location Tag -->
                    <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between">
                        <span class="px-3 py-1 rounded-lg bg-slate-900/85 backdrop-blur-md text-white text-xs font-mono">
                            📍 {{ $book->location_shelf }}
                        </span>
                    </div>
                </div>

                <!-- Status Badges Row (Available / Borrowed / Reserved) -->
                <div class="w-full mt-5 flex flex-wrap items-center justify-center gap-2">
                    <!-- Status: Available (មាន) -->
                    @if($book->available_copies > 0)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-2 h-2 rounded-full bg-[#10B981] animate-pulse"></span>
                            {{ __('Available') }} ({{ $book->available_copies }} {{ __('In Stock') }})
                        </span>
                    @else
                        <!-- Status: Borrowed (បានខ្ចី) -->
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                            {{ __('Borrowed Out') }} (0 {{ __('Left') }})
                        </span>
                    @endif

                    <!-- Status: Reserved (បានកក់) -->
                    @if($book->active_reservations_count > 0)
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            {{ __('Reserved') }} ({{ $book->active_reservations_count }})
                        </span>
                    @endif
                </div>

                <!-- Stock Metrics Grid -->
                <div class="grid grid-cols-4 gap-2 w-full mt-5 pt-5 border-t border-slate-100 text-center">
                    <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="block text-[10px] uppercase font-bold text-slate-400 tracking-wider">{{ __('Total') }}</span>
                        <span class="text-base sm:text-lg font-black text-slate-800">{{ $book->total_copies }}</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <span class="block text-[10px] uppercase font-bold text-emerald-600 tracking-wider">{{ __('Available') }}</span>
                        <span class="text-base sm:text-lg font-black text-emerald-700">{{ $book->available_copies }}</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-sky-50/60 border border-sky-100" title="{{ __('Total Real Views') }}">
                        <span class="block text-[10px] uppercase font-bold text-sky-600 tracking-wider">{{ __('Views') }}</span>
                        <span class="text-base sm:text-lg font-black text-sky-700">{{ number_format($book->views_count ?? 0) }}</span>
                    </div>
                    <div class="p-2.5 rounded-2xl bg-indigo-50/60 border border-indigo-100" title="{{ __('Active Loans') }}">
                        <span class="block text-[10px] uppercase font-bold text-indigo-600 tracking-wider">{{ __('Loans') }}</span>
                        <span class="text-base sm:text-lg font-black text-indigo-700">{{ $book->active_borrows_count }}</span>
                    </div>
                </div>

                <!-- In-App PDF Reader & Cover Extraction Buttons -->
                <div class="w-full mt-4 space-y-2">
                    <a href="{{ route('books.read', $book) }}" 
                       target="_blank"
                       class="w-full py-2.5 rounded-xl bg-gradient-to-r from-[#1E3A8A] to-[#6366F1] hover:from-blue-900 hover:to-indigo-600 text-white text-xs font-bold text-center flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20 transition-all">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span>{{ __('Open In-App PDF Reader') }}</span>
                    </a>

                    @if($book->allow_pdf_download && $book->pdf_file)
                        <a href="{{ route('books.download', $book) }}" 
                           class="w-full py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold text-center flex items-center justify-center gap-2 shadow-xs transition-all">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>{{ __('Download PDF Document') }}</span>
                        </a>
                    @endif

                    @if($layoutIsAdmin && $book->pdf_file)
                        <button type="button" 
                                @click="extractDirectCover()" 
                                :disabled="isDirectExtracting"
                                class="w-full py-2.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 text-xs font-bold text-center flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer">
                            <span x-show="!isDirectExtracting" class="flex items-center gap-1.5">
                                <span>⚡</span>
                                <span>{{ __('ស្រង់យករូបគម្របពី PDF (Extract Cover)') }}</span>
                            </span>
                            <span x-show="isDirectExtracting" class="flex items-center gap-1.5 animate-pulse text-indigo-600">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span>{{ __('កំពុងស្រង់យករូបគម្របទំព័រទី១...') }}</span>
                            </span>
                        </button>
                    @endif
                </div>
            </div>

            <!-- Metadata Details Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-5 space-y-3.5 text-xs">
                <h3 class="font-bold text-slate-800 uppercase tracking-wider text-[11px] pb-2 border-b border-slate-100">
                    {{ __('Book Information') }}
                </h3>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('Category') }}</span>
                    <span class="font-bold text-[#6366F1]">{{ $book->category ? __($book->category->name) : __('General') }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('Author') }}</span>
                    <span class="font-semibold text-slate-800">{{ $book->author }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('ISBN') }}</span>
                    <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md">{{ $book->isbn ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('Published Year') }}</span>
                    <span class="font-semibold text-slate-800">{{ $book->published_year ?? 'N/A' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('Shelf Location') }}</span>
                    <span class="font-mono font-bold text-slate-800">📍 {{ $book->location_shelf }}</span>
                </div>

                @if(!empty($book->target_academic_year))
                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-slate-400 font-medium">{{ __('Target Academic Year') }}</span>
                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100 text-xs">
                            🎯 {{ $book->target_academic_year_label }}
                        </span>
                    </div>
                @endif

                @if(!empty($book->recommended_major))
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 font-medium">{{ __('Recommended Major') }}</span>
                        <span class="font-semibold text-slate-800">{{ $book->recommended_major }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                    <span class="text-slate-400 font-medium">{{ __('ចំនួនអ្នកមើល (Total Views)') }}</span>
                    <span class="font-mono font-bold text-sky-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        {{ number_format($book->views_count ?? 0) }}
                    </span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">{{ __('ចំនួនទាញយក (Downloads)') }}</span>
                    <span class="font-mono font-bold text-emerald-700 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        {{ number_format($book->downloads_count ?? 0) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Column: Description, Active Borrowers & Queue (8 cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Book Description Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                <h3 class="text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>{{ __('Description') }}</span>
                </h3>
                <p class="text-sm leading-relaxed text-slate-600">
                    {{ $book->description ?: __('No detailed synopsis or summary available for this catalog entry.') }}
                </p>
            </div>

            @if($layoutIsAdmin)
                <!-- Active Borrowers Section (Currently Borrowed - Admin Only) -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-[#6366F1] flex items-center justify-center font-bold text-xs">
                                {{ $book->active_borrows_count }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Active Borrowers') }}</h3>
                                <p class="text-xs text-slate-400">{{ __('Members currently holding physical copies of this book') }}</p>
                            </div>
                        </div>

                        @if($book->available_copies > 0)
                            <a href="{{ route('borrows.index', ['book_id' => $book->id]) }}" 
                               class="text-xs font-semibold text-[#6366F1] hover:underline flex items-center gap-1">
                                <span>+ {{ __('Issue Book') }}</span>
                            </a>
                        @endif
                    </div>

                    @if($book->activeBorrows->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach($book->activeBorrows as $borrow)
                                <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $borrow->user?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80' }}" 
                                             class="w-10 h-10 rounded-full object-cover ring-2 ring-slate-100">
                                        <div>
                                            <p class="font-bold text-slate-800 text-sm">{{ $borrow->user?->name ?? __('Unknown Patron') }}</p>
                                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                                <span class="font-mono">{{ $borrow->user?->card_id ?? 'No Card ID' }}</span>
                                                <span>&bull;</span>
                                                <span>{{ __($borrow->user?->member_type ?? 'Member') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4 text-xs sm:text-right">
                                        <div>
                                            <span class="text-slate-400 block">{{ __('Due Date') }}</span>
                                            <span class="font-bold {{ $borrow->status === 'Overdue' ? 'text-rose-600' : 'text-slate-700' }}">
                                                {{ \Carbon\Carbon::parse($borrow->due_date)->format('M d, Y') }}
                                            </span>
                                        </div>

                                        <div>
                                            @if($borrow->status === 'Overdue')
                                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
                                                    {{ __('Overdue') }}
                                                </span>
                                            @else
                                                <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-[#6366F1] border border-indigo-200">
                                                    {{ __('Borrowed') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Quick Return Form -->
                                        <form action="{{ route('borrows.return', $borrow->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-[#1E3A8A] text-slate-700 hover:text-white text-xs font-semibold transition-colors">
                                                {{ __('Return Book') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                            {{ __('No active borrowers for this book.') }}
                        </div>
                    @endif
                </div>

                <!-- Active Reservations Section (Admin Only) -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">
                                {{ $book->active_reservations_count }}
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Reservation Queue') }}</h3>
                                <p class="text-xs text-slate-400">{{ __('Members waiting for a copy of this book') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($book->activeReservations->isNotEmpty())
                        <div class="divide-y divide-slate-100">
                            @foreach($book->activeReservations as $res)
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $res->user?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80' }}" 
                                             class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200">
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $res->user?->name ?? __('Unknown Patron') }}</p>
                                            <p class="text-slate-400">{{ __('Date Placed') }}: {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}</p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $res->status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                            {{ __($res->status) }}
                                        </span>
                                        @if($res->notes)
                                            <span class="text-slate-400 italic">"{{ $res->notes }}"</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                            {{ __('No active reservations for this book.') }}
                        </div>
                    @endif
                </div>

                <!-- Past Circulation History (Admin Only) -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ __('Circulation History') }}</span>
                    </h3>

                    @if($book->borrows->where('status', 'Returned')->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                                        <th class="py-2.5 px-3">{{ __('Patron Name') }}</th>
                                        <th class="py-2.5 px-3">{{ __('Loan Date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('Return Date') }}</th>
                                        <th class="py-2.5 px-3">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($book->borrows->where('status', 'Returned') as $history)
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="py-2.5 px-3 font-semibold text-slate-800">{{ $history->user?->name ?? __('Unknown Patron') }}</td>
                                            <td class="py-2.5 px-3 text-slate-500">{{ \Carbon\Carbon::parse($history->borrow_date)->format('M d, Y') }}</td>
                                            <td class="py-2.5 px-3 text-emerald-600 font-medium">{{ \Carbon\Carbon::parse($history->return_date)->format('M d, Y') }}</td>
                                            <td class="py-2.5 px-3">
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    {{ __('Returned') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                            {{ __('No past circulation history found.') }}
                        </div>
                    @endif
                </div>
            @else
                <!-- Member View: Guidelines & Hold Action -->
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 space-y-4">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span>
                        <span>{{ __('Library Borrowing & Reservation Guidelines') }}</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100">
                            <span class="text-xs text-blue-600 font-bold block mb-1">📅 {{ __('Loan Period') }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ __('14 Days Standard') }}</span>
                            <p class="text-[11px] text-slate-500 mt-1">{{ __('Return in person to the circulation desk before the due date.') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                            <span class="text-xs text-indigo-600 font-bold block mb-1">📚 {{ __('Quota Limit') }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ __('3 - 5 Books Concurrent') }}</span>
                            <p class="text-[11px] text-slate-500 mt-1">{{ __('Students can borrow up to 3 books; Faculty up to 5 books.') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-100">
                            <span class="text-xs text-amber-600 font-bold block mb-1">⏳ {{ __('Hold Pickup') }}</span>
                            <span class="text-sm font-semibold text-slate-800">{{ __('48 Hours Hold Shelf') }}</span>
                            <p class="text-[11px] text-slate-500 mt-1">{{ __('Once approved, the book is held at the front desk for 48 hours.') }}</p>
                        </div>
                    </div>

                    @if($book->available_copies > 0)
                        <div class="mt-4 p-5 rounded-2xl bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">📖</span>
                                <div>
                                    <h4 class="font-bold text-emerald-900 text-sm">{{ __('This book is available in stock!') }}</h4>
                                    <p class="text-xs text-emerald-700 mt-0.5">{{ __('Place an online reservation or bring your Library Card to the front desk.') }}</p>
                                </div>
                            </div>
                            <form action="{{ route('books.reserve', $book) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all">
                                    {{ __('Reserve Book Now') }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="mt-4 p-5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-slate-500 text-xs">
                            <span class="text-3xl">⏳</span>
                            <div>
                                <p class="font-semibold text-slate-700 text-sm">{{ __('All physical copies are currently checked out.') }}</p>
                                <p class="mt-0.5">{{ __('You can read the entire digital version online right now with our In-App PDF Reader above.') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL: EDIT BOOK -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Edit Book Information') }}</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            <form action="{{ route('books.update', $book) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="show">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Book Title') }} *</label>
                        <input type="text" name="title" required x-model="currentBook.title" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Author') }} *</label>
                        <input type="text" name="author" required x-model="currentBook.author" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('ISBN') }}</label>
                        <input type="text" name="isbn" x-model="currentBook.isbn" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Category') }}</label>
                        <select name="category_id" x-model="currentBook.category_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            @foreach(\App\Models\Category::all() as $cat)
                                <option value="{{ $cat->id }}">{{ __($cat->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Shelf Location') }} *</label>
                        <input type="text" name="location_shelf" required x-model="currentBook.location_shelf" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Total Copies') }} *</label>
                        <input type="number" name="total_copies" required min="1" x-model="currentBook.total_copies" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Available Copies') }} *</label>
                        <input type="number" name="available_copies" required min="0" x-model="currentBook.available_copies" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Published Year') }}</label>
                        <input type="number" name="published_year" x-model="currentBook.published_year" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>

                    <!-- Smart Academic Year & Major Recommendation in Edit (ការណែនាំតាមឆ្នាំសិក្សា) -->
                    <div class="sm:col-span-2 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
                        <span class="text-xs font-bold text-indigo-900 block flex items-center gap-1.5">
                            <span>🎯 {{ __('Smart Academic Recommendation (ការណែនាំតាមឆ្នាំសិក្សា)') }}</span>
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Target Academic Year (ឆ្នាំសិក្សាគោលដៅ)') }}</label>
                                <select name="target_academic_year" x-model="currentBook.target_academic_year" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white outline-none">
                                    <option value="">{{ __('All Academic Years / General') }}</option>
                                    <option value="1">ឆ្នាំទី ១ (Year 1)</option>
                                    <option value="2">ឆ្នាំទី ២ (Year 2)</option>
                                    <option value="3">ឆ្នាំទី ៣ (Year 3)</option>
                                    <option value="4">ឆ្នាំទី ៤ (Year 4)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Recommended Major / Dept') }}</label>
                                <input type="text" name="recommended_major" x-model="currentBook.recommended_major" placeholder="{{ __('e.g. Computer Science, Business') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Cover Image Upload in Edit (រូបភាពគម្របមែនទែន) -->
                    <div class="sm:col-span-2">
                        <input type="hidden" name="pdf_cover_data" :value="pdfCoverData">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 flex items-center justify-between">
                            <span>{{ __('Book Cover Image') }}</span>
                            <template x-if="autoCoverExtracted">
                                <span class="text-[11px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                    ⚡ {{ __('ស្រង់ពីទំព័រទី១ នៃ PDF') }}
                                </span>
                            </template>
                        </label>
                        <div class="p-3.5 bg-slate-50/80 border border-dashed border-slate-300 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center gap-3.5 transition-all hover:bg-slate-50 hover:border-indigo-300">
                            <div class="w-16 h-20 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-xs relative">
                                <template x-if="isExtractingCover">
                                    <div class="absolute inset-0 bg-indigo-50/90 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </div>
                                </template>
                                <template x-if="coverPreview">
                                    <img :src="coverPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!coverPreview && !isExtractingCover">
                                    <div class="text-center p-2 text-slate-300">
                                        <svg class="w-7 h-7 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 rounded-xl text-xs font-bold text-[#1E3A8A] cursor-pointer transition-all shadow-xs">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span>{{ __('Choose Cover Image') }}</span>
                                    <input type="file" name="cover_image_file" accept="image/*" class="hidden" @change="if ($event.target.files.length) { coverPreview = URL.createObjectURL($event.target.files[0]); coverFileName = $event.target.files[0].name; autoCoverExtracted = false; pdfCoverData = ''; }">
                                </label>
                                <p class="text-[11px] text-slate-500 mt-1.5 truncate" x-text="coverFileName ? '📄 ' + coverFileName : (autoCoverExtracted ? '✓ {{ __('បានស្រង់យករូបគម្របទំព័រទី១ ពី File PDF') }}' : '{{ __('Keep current file or upload a new replacement') }}')"></p>
                            </div>
                        </div>
                        <div class="mt-1.5">
                            <input type="url" name="cover_image" x-model="currentBook.cover_image" placeholder="{{ __('Or enter direct URL') }} (https://...)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 focus:ring-2 focus:ring-indigo-100 outline-none">
                        </div>
                    </div>

                    <!-- PDF Document Upload in Edit (ឯកសារ PDF មែនទែន) -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 flex items-center justify-between">
                            <span>{{ __('Real PDF Document') }}</span>
                            <span class="text-[11px] font-medium text-indigo-600">⚡ {{ __('ស្វ័យប្រវត្តិចាប់យករូបគម្របទំព័រទី១') }}</span>
                        </label>
                        <div class="p-3.5 bg-slate-50/80 border border-dashed border-slate-300 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center gap-3.5 transition-all hover:bg-slate-50 hover:border-rose-300">
                            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-500 border border-rose-100 flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-50/40 rounded-xl text-xs font-bold text-rose-600 cursor-pointer transition-all shadow-xs">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span>{{ __('Choose PDF Document') }}</span>
                                        <input type="file" name="pdf_upload" accept="application/pdf" class="hidden" @change="handlePdfSelected($event)">
                                    </label>
                                    <template x-if="currentBook.pdf_file">
                                        <button type="button" 
                                                @click="extractCurrentPdfCover()" 
                                                :disabled="isExtractingCover"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200 transition-all cursor-pointer">
                                            <span x-show="!isExtractingCover">⚡ {{ __('ស្រង់រូបគម្របពី PDF បច្ចុប្បន្ន') }}</span>
                                            <span x-show="isExtractingCover" class="animate-pulse flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                {{ __('កំពុងស្រង់...') }}
                                            </span>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1.5 font-mono truncate" x-text="pdfFileName ? '📕 ' + pdfFileName : (currentBook.pdf_file ? '✓ {{ __('Current PDF Document') }}' : '{{ __('Supports PDF up to 1GB (ប្រព័ន្ធស្រង់ទំព័រទី១ ជាគម្របស្វ័យប្រវត្តិ)') }}')"></p>
                                <div x-show="isExtractingCover" class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-indigo-600 animate-pulse">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>{{ __('កំពុងស្រង់យករូបគម្របទំព័រទី១ ពី PDF ដោយស្វ័យប្រវត្តិ...') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-1.5">
                            <input type="url" name="pdf_file" x-model="currentBook.pdf_file" placeholder="{{ __('Or enter direct URL') }} (https://.../document.pdf)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                        <input type="checkbox" id="edit_show_pdf_dl" name="allow_pdf_download" value="1" x-model="currentBook.allow_pdf_download" class="rounded text-[#6366F1] focus:ring-indigo-200">
                        <label for="edit_show_pdf_dl" class="text-xs text-slate-700 font-medium cursor-pointer">{{ __('Allow Member to Download/Print PDF (If unchecked, view-only in app)') }}</label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Description / Summary') }}</label>
                        <textarea name="description" rows="3" x-model="currentBook.description" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">{{ __('Update Book') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: DELETE CONFIRMATION -->
    @if($layoutUser?->canDeleteBooks())
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center" @click.away="deleteModalOpen = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            
            <h3 class="text-lg font-bold text-slate-900 mb-1">{{ __('Delete Book Confirmation') }}</h3>
            <p class="text-xs text-slate-500 mb-6">
                {{ __('This action will permanently remove') }} <strong class="text-slate-800">"{{ $book->title }}"</strong> {{ __('from the library catalog.') }}
            </p>

            <form action="{{ route('books.destroy', $book) }}" method="POST" class="flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md">
                    {{ __('Yes, Delete Book') }}
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- FLOATING IN-PLACE TOAST ALERT -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-4"
         x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-50 max-w-md bg-white border border-slate-200/90 rounded-2xl shadow-2xl p-4 flex items-start gap-3.5 ring-1 ring-black/5"
         x-cloak>
        <div :class="toastType === 'success' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-rose-50 text-rose-600 border-rose-100'" 
             class="w-10 h-10 rounded-xl border flex items-center justify-center shrink-0 shadow-xs text-base">
            <template x-if="toastType === 'success'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M5 13l4 4L19 7"></path></svg>
            </template>
            <template x-if="toastType !== 'success'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </template>
        </div>
        <div class="flex-1 min-w-0 pr-1">
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="w-2 h-2 rounded-full" :class="toastType === 'success' ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500'"></span>
                <h4 class="text-xs font-bold text-slate-900" x-text="toastTitle"></h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium" x-text="toastMessage"></p>
            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                <span>🔔</span>
                <span>{{ __('ប្រព័ន្ធជូនដំណឹងស្វ័យប្រវត្តិ') }}</span>
            </p>
        </div>
        <button @click="showToast = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">&times;</button>
    </div>

</div>
@endsection
