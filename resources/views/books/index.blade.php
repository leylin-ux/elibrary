@extends('layouts.app')

@section('title', __('Books Management'))

@section('content')
@php
    $userPendingBookIds = [];
    $userApprovedBookIds = [];
    if (!$layoutIsAdmin && auth()->check()) {
        $userReservations = \App\Models\Reservation::where('user_id', auth()->id())
            ->whereIn('status', ['Pending', 'Approved'])
            ->get();
        $userPendingBookIds = $userReservations->where('status', 'Pending')->pluck('book_id')->values()->all();
        $userApprovedBookIds = $userReservations->where('status', 'Approved')->pluck('book_id')->values()->all();
    }

    $activeFilterCount = (request()->filled('search') ? 1 : 0) 
        + (request()->filled('category_id') && request('category_id') !== 'all' ? 1 : 0) 
        + (request()->filled('subcategory') && request('subcategory') !== 'all' ? 1 : 0) 
        + (request()->filled('education_level') && request('education_level') !== 'all' ? 1 : 0) 
        + (request()->filled('subject') && request('subject') !== 'all' ? 1 : 0) 
        + (request()->filled('grade') && request('grade') !== 'all' ? 1 : 0) 
        + (request()->filled('academic_year') ? 1 : 0) 
        + (request()->filled('status') ? 1 : 0);

    $currentSort = request('sort', 'latest');
    $sortLabels = [
        'latest' => 'ថ្មីៗ',
        'trending' => 'ពេញនិយម',
        'popular' => 'ពេញនិយម',
        'downloads' => 'ទាញយកច្រើន',
        'title_asc' => 'តាមចំណងជើង',
        'oldest' => 'ចាស់ជាងគេ',
    ];
    $currentSortLabel = $sortLabels[$currentSort] ?? 'ថ្មីៗ';
@endphp

<script>
    // Early restore scroll position before page paints if returning to catalog
    try {
        const savedScrollY = sessionStorage.getItem('books_catalog_scroll_y');
        if (savedScrollY !== null) {
            const y = parseInt(savedScrollY, 10);
            if (!isNaN(y)) {
                if ('scrollRestoration' in history) {
                    history.scrollRestoration = 'manual';
                }
                window.scrollTo(0, y);
            }
        }
    } catch(e) {}
</script>

<div class="space-y-6" x-data="{ 
    viewMode: localStorage.getItem('books_view_mode') || 'grid', 
    filterDrawerOpen: false,
    addModalOpen: {{ $errors->any() && !old('_method') ? 'true' : 'false' }}, 
    editModalOpen: {{ $errors->any() && old('_method') === 'PUT' ? 'true' : 'false' }},
    deleteModalOpen: false,
    detailModalOpen: false,
    bookToDelete: { id: null, title: '' },
    coverPreview: '',
    coverFileName: '',
    pdfFileName: '',
    pdfCoverData: '',
    isExtractingCover: false,
    autoCoverExtracted: false,
    editCoverPreview: '',
    editCoverFileName: '',
    editPdfFileName: '',
    editPdfCoverData: '',
    isEditExtractingCover: false,
    editAutoCoverExtracted: false,
    selectedBook: null,
    currentBook: { 
        id: null, 
        title: '', 
        author: '', 
        isbn: '', 
        category_id: 1, 
        total_copies: 1, 
        available_copies: 1, 
        location_shelf: 'Shelf A-1', 
        published_year: 2024, 
        cover_image: '', 
        pdf_file: '',
        allow_pdf_download: false,
        target_academic_year: null,
        recommended_major: '',
        description: '' 
    },
    async handlePdfSelected(event, isEdit = false) {
        const file = event.target.files[0];
        if (!file) return;

        if (isEdit) {
            this.editPdfFileName = file.name;
            this.isEditExtractingCover = true;
            this.editAutoCoverExtracted = false;
        } else {
            this.pdfFileName = file.name;
            this.isExtractingCover = true;
            this.autoCoverExtracted = false;
        }

        try {
            if (window.extractPdfCover) {
                const coverDataUrl = await window.extractPdfCover(file);
                if (isEdit) {
                    this.editCoverPreview = coverDataUrl;
                    this.editPdfCoverData = coverDataUrl;
                    this.editAutoCoverExtracted = true;
                } else {
                    this.coverPreview = coverDataUrl;
                    this.pdfCoverData = coverDataUrl;
                    this.autoCoverExtracted = true;
                }
            }
        } catch (e) {
            console.error('Failed to extract PDF cover:', e);
        } finally {
            if (isEdit) {
                this.isEditExtractingCover = false;
            } else {
                this.isExtractingCover = false;
            }
        }
    },
    async extractCurrentPdfCover() {
        if (!this.currentBook.pdf_file) return;
        this.isEditExtractingCover = true;
        this.editAutoCoverExtracted = false;
        try {
            if (window.extractPdfCover) {
                const coverDataUrl = await window.extractPdfCover(this.currentBook.pdf_file);
                this.editCoverPreview = coverDataUrl;
                this.editPdfCoverData = coverDataUrl;
                this.editAutoCoverExtracted = true;
            }
        } catch (e) {
            console.error('Failed to extract PDF cover from URL:', e);
            alert('Could not extract cover from this PDF file.');
        } finally {
            this.isEditExtractingCover = false;
        }
    },
    openEdit(book) {
        this.currentBook = {
            id: book.id,
            title: book.title || '',
            author: book.author || '',
            isbn: book.isbn || '',
            category_id: book.category_id || '',
            total_copies: book.total_copies || 1,
            available_copies: book.available_copies !== undefined ? book.available_copies : 0,
            location_shelf: book.location_shelf || '',
            published_year: book.published_year || 2024,
            cover_image: book.cover_image || '',
            pdf_file: book.pdf_file || '',
            allow_pdf_download: !!book.allow_pdf_download,
            target_academic_year: book.target_academic_year || '',
            recommended_major: book.recommended_major || '',
            description: book.description || ''
        };
        this.editCoverPreview = book.cover_image || '';
        this.editCoverFileName = '';
        this.editPdfFileName = book.pdf_file ? book.pdf_file.split('/').pop() : '';
        this.editPdfCoverData = '';
        this.isEditExtractingCover = false;
        this.editAutoCoverExtracted = false;
        this.detailModalOpen = false;
        this.editModalOpen = true;
    },
    openDetail(book) {
        this.selectedBook = book;
        this.detailModalOpen = true;

        if (book && book.id) {
            fetch('/books/' + book.id + '/record-view', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.success && data.views_count !== undefined) {
                    this.selectedBook.views_count = data.views_count;
                    const cardViewEl = document.getElementById('book-views-' + book.id);
                    if (cardViewEl) {
                        cardViewEl.textContent = new Intl.NumberFormat().format(data.views_count);
                    }
                    const cardCompEl = document.getElementById('book-card-views-' + book.id);
                    if (cardCompEl) {
                        cardCompEl.textContent = new Intl.NumberFormat().format(data.views_count);
                    }
                }
            })
            .catch(err => console.error('Failed to record view:', err));
        }
    },
    confirmDelete(book) {
        this.bookToDelete = { id: book.id, title: book.title };
        this.detailModalOpen = false;
        this.deleteModalOpen = true;
    },
    pendingBookIds: {{ json_encode($userPendingBookIds) }},
    approvedBookIds: {{ json_encode($userApprovedBookIds) }},
    reservingId: null,
    toastMessage: '',
    toastType: 'success',
    showToast: false,
    triggerToast(msg, type = 'success') {
        this.toastMessage = msg;
        this.toastType = type;
        this.showToast = true;
        setTimeout(() => { this.showToast = false; }, 5500);
    },
    init() {
        @if(!$layoutIsAdmin && auth()->check())
            setInterval(() => {
                this.pollReservationStatus();
            }, 3500);
        @endif
    },
    async pollReservationStatus() {
        try {
            const res = await fetch('{{ route('reservations.user-status') }}', {
                headers: { 'Accept': 'application/json' }
            });
            if (res.ok) {
                const data = await res.json();
                if (data.success) {
                    if (data.approved_items && data.approved_items.length > 0) {
                        data.approved_items.forEach(item => {
                            if (!this.approvedBookIds.includes(item.book_id)) {
                                this.approvedBookIds.push(item.book_id);
                                this.pendingBookIds = this.pendingBookIds.filter(id => id !== item.book_id);
                                this.triggerToast('🎉 {{ __('បានកក់ជោគជ័យ! Admin បានទទួលការកក់សៀវភៅ') }} «' + item.book_title + '» {{ __('របស់អ្នករួចរាល់ហើយ! (កូដកក់៖ ') }}' + item.code + ')', 'success');
                            }
                        });
                    }
                    this.pendingBookIds = data.pending_book_ids || [];
                    this.approvedBookIds = data.approved_book_ids || [];
                }
            }
        } catch (e) {}
    },
    async quickReserve(bookId) {
        if (!bookId || this.reservingId) return;
        this.reservingId = bookId;
        try {
            const res = await fetch('/books/' + bookId + '/reserve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ book_id: bookId })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                if (!this.pendingBookIds.includes(bookId)) {
                    this.pendingBookIds.push(bookId);
                }
                this.triggerToast('📩 ' + (data.message || '{{ __('បានផ្ញើសារការកក់ទៅកាន់ Admin រួចរាល់! សូមរង់ចាំ Admin ទទួលការកក់។') }}'), 'success');
            } else {
                this.triggerToast(data.message || '{{ __('Could not reserve book.') }}', 'error');
            }
        } catch (e) {
            this.triggerToast('{{ __('An error occurred. Please try again.') }}', 'error');
        } finally {
            this.reservingId = null;
        }
    }
}">

    <!-- Top Header & Action Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">
                {{ $layoutIsAdmin ? __('Books Management') : __('Browse Library Catalog & E-Books') }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $layoutIsAdmin ? __('Manage inventory, edit details, upload PDF, and track book distribution.') : __('Search books, reserve for library pickup, or read online PDFs directly.') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            <!-- View Mode Toggle (Grid / Table) -->
            <div class="flex items-center bg-slate-200/80 p-1 rounded-xl border border-slate-200">
                <button @click="viewMode = 'grid'; localStorage.setItem('books_view_mode', 'grid')" 
                        :class="viewMode === 'grid' ? 'bg-white text-[#1E3A8A] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="p-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>{{ __('Grid') }}</span>
                </button>
                <button @click="viewMode = 'table'; localStorage.setItem('books_view_mode', 'table')" 
                        :class="viewMode === 'table' ? 'bg-white text-[#1E3A8A] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="p-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>{{ __('Table') }}</span>
                </button>
            </div>

            <!-- Add Book Modal Button (Admin Only) / Reservations Shortcut (Member) -->
            @if($layoutIsAdmin)
                <button @click="addModalOpen = true" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-950/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ __('Add Book') }}</span>
                </button>
            @else
                <a href="{{ route('reservations.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-emerald-950/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>{{ __('My Reservations') }}</span>
                </a>
            @endif
        </div>
    </div>

    <!-- SECTION: បណ្តុំសៀវភៅ (BOOK BUNDLES / COLLECTIONS - MATCHING USER SCREENSHOT) -->
    @if(isset($bookBundles) && count($bookBundles) > 0)
    <div class="space-y-3.5 pb-2" x-data="{
        scrollTrack(dir) {
            const track = $refs.bundlesContainer;
            if (track) {
                const card = track.firstElementChild;
                const scrollAmount = card ? (card.offsetWidth + 16) * 2 : 280;
                track.scrollBy({ left: dir * scrollAmount, behavior: 'smooth' });
            }
        }
    }">
        <!-- Header: Icon + Title matching Latest Releases -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-sky-700 border border-blue-100/80 flex items-center justify-center text-base shadow-2xs">
                    📚
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-800 tracking-tight font-khmer">
                        {{ __('បណ្តុំសៀវភៅ') }}
                    </h2>
                </div>
            </div>
            
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ route('books.bundles') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white border border-slate-200 hover:border-slate-300 hover:bg-slate-50 text-slate-600 hover:text-slate-900 text-xs font-semibold shadow-2xs transition-all group">
                    <span>{{ __('បង្ហាញទាំងអស់') }}</span>
                    <span class="text-slate-400 group-hover:text-slate-700 group-hover:translate-x-0.5 transition-all">&rarr;</span>
                </a>
                <div class="flex items-center gap-1">
                    <button type="button" 
                            @click="scrollTrack(-1)"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 text-slate-600 flex items-center justify-center transition-all cursor-pointer"
                            title="{{ __('Previous') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" 
                            @click="scrollTrack(1)"
                            class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-slate-200 shadow-2xs hover:bg-slate-50 text-slate-600 flex items-center justify-center transition-all cursor-pointer"
                            title="{{ __('Next') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Carousel Cards Track (Matching size & length of Latest Releases / x-book-card) -->
        <div x-ref="bundlesContainer" class="flex items-stretch gap-3.5 sm:gap-4 overflow-x-auto no-scrollbar scroll-smooth py-1 -my-1">
            @foreach($bookBundles as $bundle)
                <a href="{{ $bundle['url'] }}" 
                   class="bundle-card-item shrink-0 bg-white rounded-2xl p-3 border border-slate-100 shadow-2xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between group cursor-pointer">
                    
                    <div>
                        <!-- Cover Top Area: Matching aspect ratio aspect-[3/4.2] of book cards -->
                        <div class="relative aspect-[3/4.2] rounded-xl overflow-hidden bg-gradient-to-b {{ $bundle['bg_gradient'] }} p-2 mb-2.5 flex items-center justify-center shadow-inner">
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

                            <!-- Bundle tag on bottom left -->
                            <div class="absolute bottom-2 left-2 z-20">
                                <span class="px-2 py-0.5 rounded-md bg-slate-900/75 backdrop-blur-xs text-white text-[9px] font-semibold flex items-center gap-1">
                                    BUNDLE
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
                        <span class="flex items-center gap-1 hover:text-slate-600 transition-colors">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="font-medium font-mono text-[10px]">{{ $bundle['count'] ?? 4 }}</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Subtle Scroll Indicator Line -->
        <div class="h-1 bg-slate-200/60 rounded-full w-full overflow-hidden mt-0.5">
            <div class="h-full bg-slate-300/80 rounded-full w-1/4"></div>
        </div>
    </div>
    @endif

    <!-- BOOKS CATALOG CONTAINER (SEAMLESS IN-PLACE REFRESH) -->
    <div id="books-catalog-container" class="space-y-6">
        <!-- Heading: សៀវភៅ <count> ក្បាល (Matching Screenshot) -->
        <div id="catalog-section" class="flex items-baseline gap-2 pt-1 scroll-mt-20">
        <h2 class="text-xl sm:text-2xl font-bold text-slate-900 font-khmer">{{ __('សៀវភៅ') }}</h2>
        <span class="text-xs text-slate-400 font-khmer">{{ number_format($books->total()) }} {{ __('ក្បាល') }}</span>
    </div>

    <!-- CATEGORY PILLS BAR & CONTROLS (MATCHING USER SCREENSHOT) -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between gap-3 bg-transparent">
            <!-- Left: Horizontal Scrollable Category Pills -->
            <div id="category-pills-track" class="flex items-center gap-2 overflow-x-auto no-scrollbar scroll-smooth py-1 -my-1 min-w-0 flex-1">
                <!-- All (ទាំងអស់) -->
                @php
                    $isAllSelected = !request()->filled('category_id') || request('category_id') === 'all';
                @endphp
                <a href="{{ route('books.index', request()->except('category_id', 'page')) }}#catalog-section" 
                   class="px-4 sm:px-5 py-2 rounded-full text-xs sm:text-sm shrink-0 transition-all font-khmer {{ $isAllSelected ? 'active-category-pill bg-[#dce4ea] border border-[#23677a] text-[#23677a] font-semibold shadow-2xs' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-800 font-medium shadow-2xs' }}">
                    {{ __('ទាំងអស់') }}
                </a>

                <!-- Each Category Pill -->
                @foreach($categories as $cat)
                    @php
                        $isSelected = request('category_id') == $cat->id;
                    @endphp
                    <a href="{{ $isSelected ? route('books.index', request()->except('category_id', 'page')) . '#catalog-section' : route('books.index', array_merge(request()->except('page'), ['category_id' => $cat->id])) . '#catalog-section' }}" 
                       class="px-4 sm:px-5 py-2 rounded-full text-xs sm:text-sm shrink-0 transition-all font-khmer {{ $isSelected ? 'active-category-pill bg-[#dce4ea] border border-[#23677a] text-[#23677a] font-semibold shadow-2xs' : 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-800 font-medium shadow-2xs' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <!-- Right: Sort Dropdown & Filter Button -->
            <div id="catalog-controls" class="flex items-center gap-2 shrink-0">
                <!-- Sort Dropdown -->
                <div class="relative" x-data="{ sortOpen: false }" @click.outside="sortOpen = false">
                    <button type="button" 
                            @click="sortOpen = !sortOpen" 
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-medium shadow-2xs transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        <span>{{ $currentSortLabel }}</span>
                        <svg class="w-3 h-3 text-slate-400 transition-transform" :class="sortOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Sort Dropdown Menu -->
                    <div x-show="sortOpen" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         class="absolute right-0 mt-1.5 w-44 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-30 text-xs text-slate-700 divide-y divide-slate-50"
                         x-cloak>
                        <a href="{{ route('books.index', array_merge(request()->except('page'), ['sort' => 'latest'])) }}#catalog-section" 
                           class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition-colors {{ request('sort', 'latest') === 'latest' ? 'font-bold text-indigo-600 bg-indigo-50/50' : '' }}">
                            <span class="flex items-center gap-2"><span>⇅</span> <span>ថ្មីៗ (Newest)</span></span>
                            @if(request('sort', 'latest') === 'latest')
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a href="{{ route('books.index', array_merge(request()->except('page'), ['sort' => 'trending'])) }}#catalog-section" 
                           class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition-colors {{ request('sort') === 'trending' ? 'font-bold text-indigo-600 bg-indigo-50/50' : '' }}">
                            <span class="flex items-center gap-2"><span>🔥</span> <span>ពេញនិយម (Popular)</span></span>
                            @if(request('sort') === 'trending')
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a href="{{ route('books.index', array_merge(request()->except('page'), ['sort' => 'downloads'])) }}#catalog-section" 
                           class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition-colors {{ request('sort') === 'downloads' ? 'font-bold text-indigo-600 bg-indigo-50/50' : '' }}">
                            <span class="flex items-center gap-2"><span>⬇</span> <span>ទាញយកច្រើន (Downloads)</span></span>
                            @if(request('sort') === 'downloads')
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a href="{{ route('books.index', array_merge(request()->except('page'), ['sort' => 'title_asc'])) }}#catalog-section" 
                           class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition-colors {{ request('sort') === 'title_asc' ? 'font-bold text-indigo-600 bg-indigo-50/50' : '' }}">
                            <span class="flex items-center gap-2"><span>🔤</span> <span>ចំណងជើង (A-Z)</span></span>
                            @if(request('sort') === 'title_asc')
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                        <a href="{{ route('books.index', array_merge(request()->except('page'), ['sort' => 'oldest'])) }}#catalog-section" 
                           class="flex items-center justify-between px-3.5 py-2 hover:bg-slate-50 transition-colors {{ request('sort') === 'oldest' ? 'font-bold text-indigo-600 bg-indigo-50/50' : '' }}">
                            <span class="flex items-center gap-2"><span>⏳</span> <span>ចាស់ជាងគេ (Oldest)</span></span>
                            @if(request('sort') === 'oldest')
                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Filter Drawer Toggle Button (Exact badge like screenshot) -->
                <button type="button" 
                        @click="filterDrawerOpen = !filterDrawerOpen" 
                        :class="filterDrawerOpen ? 'bg-slate-800 text-white border-slate-800' : '{{ $activeFilterCount > 0 ? "bg-teal-50/80 border-[#0d9488]/50 text-teal-800 ring-1 ring-[#0d9488]/30" : "bg-white border-slate-200 text-slate-700 hover:bg-slate-50" }}'"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full border text-xs font-medium shadow-2xs transition-all cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    <span>{{ __('តម្រង') }}</span>
                    @if($activeFilterCount > 0)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold bg-[#0d9488] text-white">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>

        <!-- SLIDE-OVER FILTER DRAWER (MATCHING USER SCREENSHOT IMAGE 2) -->
        <div x-show="filterDrawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="filter-drawer-overlay fixed inset-0 bg-slate-900/45 backdrop-blur-xs z-50 flex justify-end"
             style="position: fixed; inset: 0; background-color: rgba(15, 23, 42, 0.45); backdrop-filter: blur(2px); z-index: 9999; justify-content: flex-end;"
             @click.self="filterDrawerOpen = false"
             @keydown.escape.window="filterDrawerOpen = false"
             x-cloak>
            
            <!-- Drawer Panel (Strictly Right Side Max 380px) -->
            <div x-show="filterDrawerOpen"
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="filter-drawer-panel w-full bg-white h-full shadow-2xl flex flex-col justify-between p-6 sm:p-7 overflow-y-auto shrink-0"
                 id="filter-drawer-panel"
                 style="width: 100%; max-width: 380px; height: 100%; background-color: #ffffff; box-shadow: -10px 0 30px -5px rgba(0,0,0,0.18); flex-shrink: 0;"
                 @click.outside="filterDrawerOpen = false">
                
                <form id="filter-drawer-form" method="GET" action="{{ route('books.index') }}#catalog-section" class="flex flex-col h-full justify-between">
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    @if(request('sort'))
                        <input type="hidden" name="sort" value="{{ request('sort') }}">
                    @endif
                    @if(request('status'))
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif

                    <div class="space-y-4 sm:space-y-5">
                        <!-- Header (Matching Image 2 without X button) -->
                        <div class="pb-1">
                            <h2 class="text-xl font-bold text-slate-900 font-khmer">តម្រង</h2>
                            <p class="text-xs text-slate-500 mt-0.5 font-khmer">រួមបញ្ចូលគ្នាដើម្បីបង្រួមលទ្ធផល</p>
                        </div>

                        <!-- 1. ប្រភេទ (Category) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5 font-khmer">{{ __('ប្រភេទ') }}</label>
                            <div class="relative">
                                <select name="category_id" 
                                        class="w-full appearance-none rounded-2xl border border-slate-200/90 bg-white px-4 py-3 pr-10 text-sm text-slate-800 focus:border-[#23677a] focus:ring-2 focus:ring-[#23677a]/20 outline-none transition-all shadow-2xs cursor-pointer font-khmer">
                                    <option value="">{{ __('ទាំងអស់') }}</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- 2. ប្រភេទរង (Subcategory) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5 font-khmer">{{ __('ប្រភេទរង') }}</label>
                            <div class="relative">
                                <select name="subcategory" 
                                        class="w-full appearance-none rounded-2xl border border-slate-200/90 bg-white px-4 py-3 pr-10 text-sm text-slate-800 focus:border-[#23677a] focus:ring-2 focus:ring-[#23677a]/20 outline-none transition-all shadow-2xs cursor-pointer font-khmer">
                                    <option value="">{{ __('ទាំងអស់') }}</option>
                                    @foreach($filterOptions['subcategories'] as $subcat)
                                        <option value="{{ $subcat }}" {{ request('subcategory') === $subcat ? 'selected' : '' }}>
                                            {{ $subcat }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- 3. កម្រិតអប់រំ (Education Level) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5 font-khmer">{{ __('កម្រិតអប់រំ') }}</label>
                            <div class="relative">
                                <select name="education_level" 
                                        class="w-full appearance-none rounded-2xl border border-slate-200/90 bg-white px-4 py-3 pr-10 text-sm text-slate-800 focus:border-[#23677a] focus:ring-2 focus:ring-[#23677a]/20 outline-none transition-all shadow-2xs cursor-pointer font-khmer">
                                    <option value="">{{ __('ទាំងអស់') }}</option>
                                    @foreach($filterOptions['education_levels'] as $edLevel)
                                        <option value="{{ $edLevel }}" {{ request('education_level') === $edLevel ? 'selected' : '' }}>
                                            {{ $edLevel }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- 4. ជំនាញ / មុខវិជ្ជា (Subject / Major) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5 font-khmer">{{ __('ជំនាញ / មុខវិជ្ជា') }}</label>
                            <div class="relative">
                                <select name="subject" 
                                        class="w-full appearance-none rounded-2xl border border-slate-200/90 bg-white px-4 py-3 pr-10 text-sm text-slate-800 focus:border-[#23677a] focus:ring-2 focus:ring-[#23677a]/20 outline-none transition-all shadow-2xs cursor-pointer font-khmer">
                                    <option value="">{{ __('ទាំងអស់') }}</option>
                                    @foreach($filterOptions['subjects'] as $subj)
                                        <option value="{{ $subj }}" {{ request('subject') === $subj ? 'selected' : '' }}>
                                            {{ $subj }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- 5. ឆ្នាំសិក្សា (Academic Year) -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-900 mb-1.5 font-khmer">{{ __('ឆ្នាំសិក្សា') }}</label>
                            <div class="relative">
                                <select name="grade" 
                                        class="w-full appearance-none rounded-2xl border border-slate-200/90 bg-white px-4 py-3 pr-10 text-sm text-slate-800 focus:border-[#23677a] focus:ring-2 focus:ring-[#23677a]/20 outline-none transition-all shadow-2xs cursor-pointer font-khmer">
                                    <option value="">{{ __('ទាំងអស់') }}</option>
                                    @foreach($filterOptions['grades'] as $grd)
                                        <option value="{{ $grd }}" {{ request('grade') === $grd ? 'selected' : '' }}>
                                            {{ $grd }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex items-center justify-end gap-5 pt-6 mt-8">
                        <a href="{{ route('books.index') }}#catalog-section" 
                           class="text-sm font-semibold text-slate-700 hover:text-slate-900 transition-colors font-khmer cursor-pointer">
                            {{ __('សម្អាតតម្រង') }}
                        </a>
                        <button type="submit" 
                                class="filter-btn-apply px-7 py-2.5 rounded-full text-white text-sm font-bold shadow-md transition-all font-khmer cursor-pointer"
                                style="background-color: #23677a !important; color: #ffffff !important; border-radius: 9999px; box-shadow: 0 4px 10px rgba(35, 103, 122, 0.3); border: none;">
                            {{ __('អនុវត្ត') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(!request()->filled('search') && !request()->filled('category_id') && !request()->filled('status') && !request()->filled('subcategory') && !request()->filled('education_level') && !request()->filled('subject') && !request()->filled('grade'))
        <div x-show="viewMode === 'grid'" class="space-y-6" x-cloak>
            <!-- SECTION 1: ✨ ស្នាដៃថ្មីចុងក្រោយ (LATEST RELEASES SHOWCASE) -->
            <x-book-showcase-section 
                :title="__('ស្នាដៃថ្មីចុងក្រោយ')" 
                icon="✨" 
                iconBg="bg-amber-50 text-amber-500 border-amber-100/70"
                :books="$latestBooks" 
                :viewAllUrl="route('books.index', ['sort' => 'latest'])" />

            <!-- SECTION 2: 🔥 ស្នាដៃកំពុងពេញនិយម (TRENDING RELEASES SHOWCASE) -->
            <x-book-showcase-section 
                :title="__('ស្នាដៃកំពុងពេញនិយម')" 
                icon="🔥" 
                iconBg="bg-rose-50 text-rose-500 border-rose-100/70"
                :books="$trendingBooks" 
                :viewAllUrl="route('books.index', ['sort' => 'trending'])" />
        </div>
    @endif

    <!-- Showing Count & Active Filter Badges Bar -->
    <div id="active-filter-badges" class="flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
        <div class="flex flex-wrap items-center gap-2">
            <span>{{ __('Showing') }} <strong class="text-slate-700">{{ $books->count() }}</strong> {{ __('of') }} <strong class="text-slate-700">{{ $books->total() }}</strong> {{ __('books') }}</span>
            
            @if(request()->filled('category_id') && request('category_id') !== 'all')
                @php $activeCat = $categories->firstWhere('id', request('category_id')); @endphp
                @if($activeCat)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 font-medium">
                        <span>ប្រភេទ:</span>
                        <strong>{{ $activeCat->name }}</strong>
                        <a href="{{ route('books.index', request()->except('category_id', 'page')) }}#catalog-section" class="hover:text-red-500 ml-1 font-bold">&times;</a>
                    </span>
                @endif
            @endif

            @if(request()->filled('subcategory') && request('subcategory') !== 'all')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200 font-medium">
                    <span>ប្រភេទរង:</span>
                    <strong>{{ request('subcategory') }}</strong>
                    <a href="{{ route('books.index', request()->except('subcategory', 'page')) }}#catalog-section" class="hover:text-red-500 ml-1 font-bold">&times;</a>
                </span>
            @endif

            @if(request()->filled('education_level') && request('education_level') !== 'all')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-200 font-medium">
                    <span>កម្រិតអប់រំ:</span>
                    <strong>{{ request('education_level') }}</strong>
                    <a href="{{ route('books.index', request()->except('education_level', 'page')) }}#catalog-section" class="hover:text-red-500 ml-1 font-bold">&times;</a>
                </span>
            @endif

            @if(request()->filled('subject') && request('subject') !== 'all')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium">
                    <span>មុខវិជ្ជា:</span>
                    <strong>{{ request('subject') }}</strong>
                    <a href="{{ route('books.index', request()->except('subject', 'page')) }}#catalog-section" class="hover:text-red-500 ml-1 font-bold">&times;</a>
                </span>
            @endif

            @if(request()->filled('grade') && request('grade') !== 'all')
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-purple-50 text-purple-800 border border-purple-200 font-medium">
                    <span>ឆ្នាំសិក្សា:</span>
                    <strong>{{ request('grade') }}</strong>
                    <a href="{{ route('books.index', request()->except('grade', 'page')) }}#catalog-section" class="hover:text-red-500 ml-1 font-bold">&times;</a>
                </span>
            @endif

            @if($activeFilterCount > 0)
                <a href="{{ route('books.index') }}#catalog-section" class="text-xs text-rose-600 hover:text-rose-800 font-semibold underline ml-1">
                    {{ __('សម្អាតទាំងអស់') }}
                </a>
            @endif
        </div>
    </div>

    <!-- 1. GRID VIEW (5 COLUMNS SHOWCASE) -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3.5 sm:gap-4" x-cloak>
        @forelse($books as $book)
            <div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-2xs hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <!-- Book Cover & Ambient Backdrop (Matching Screenshot) -->
                    <div class="relative aspect-[3/4.2] rounded-xl overflow-hidden bg-gradient-to-b from-sky-100/50 via-slate-50 to-indigo-50/30 p-2 mb-2.5 flex items-center justify-center shadow-inner cursor-pointer" @click="openDetail({{ json_encode($book) }})">
                        <img src="{{ $book->cover_image ? asset(ltrim($book->cover_image, '/')) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                             alt="{{ $book->title }}" 
                             loading="lazy"
                             class="w-full h-full object-contain rounded-lg shadow-sm group-hover:scale-[1.03] transition-transform duration-300">
                        
                        <!-- Top Badges: Available (មាន) / Borrowed (បានខ្ចី) -->
                        <div class="absolute top-2 right-2 flex flex-col items-end gap-1">
                            @if($book->available_copies > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/90 backdrop-blur-xs text-white shadow-xs">
                                    {{ __('Available') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/90 backdrop-blur-xs text-white shadow-xs">
                                    {{ __('Borrowed') }}
                                </span>
                            @endif
                        </div>

                        <!-- Academic Year Badge (Smart Study Level) -->
                        @if(!empty($book->target_academic_year))
                            <div class="absolute top-2 left-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-600/90 backdrop-blur-xs text-white shadow-xs">
                                    🎯 {{ __('ឆ្នាំទី :year', ['year' => $book->target_academic_year]) }}
                                </span>
                            </div>
                        @endif

                        <!-- Shelf Location Tag -->
                        @if(!empty($book->location_shelf))
                            <div class="absolute bottom-2 left-2">
                                <span class="px-2 py-0.5 rounded-md bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-mono">
                                    📍 {{ $book->location_shelf }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Book Information -->
                    <div>
                        <h3 class="font-bold text-slate-800 text-[13px] line-clamp-2 leading-snug min-h-[38px] group-hover:text-[#1E3A8A] transition-colors cursor-pointer font-khmer" 
                            @click="openDetail({{ json_encode($book) }})" 
                            title="{{ $book->title }}">
                            {{ $book->title }}
                        </h3>

                        <p class="text-[11px] text-slate-400 mt-1 truncate" title="{{ $book->author }}">
                            by <span class="text-slate-500 font-medium">{{ $book->author }}</span>
                        </p>
                    </div>
                </div>

                <!-- Card Bottom: Views, Downloads & Action Buttons -->
                <div class="mt-2.5 pt-2 border-t border-slate-100/80 flex items-center justify-between">
                    <!-- Views & Downloads Stats -->
                    <div class="flex items-center gap-2.5 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1" title="{{ __('Total Views') }}: {{ number_format($book->views_count ?? 0) }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="font-medium font-mono text-[10px]" id="book-views-{{ $book->id }}">{{ number_format($book->views_count ?? 0) }}</span>
                        </span>

                        <span class="flex items-center gap-1" title="{{ __('Total Downloads / Loans') }}: {{ number_format($book->downloads_count ?? 0) }}">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span class="font-medium font-mono text-[10px]">{{ number_format($book->downloads_count ?? 0) }}</span>
                        </span>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-1">
                        <!-- Read PDF Button -->
                        @if($book->pdf_file)
                            <a href="{{ route('books.read', $book) }}"
                               target="_blank"
                               class="p-1 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors"
                               title="{{ __('Read Online (PDF)') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </a>
                        @endif

                        <!-- View Details Button (Eye) -->
                        <button type="button" 
                                @click="openDetail({{ json_encode($book) }})"
                                class="p-1 text-slate-500 hover:text-[#1E3A8A] hover:bg-slate-100 rounded-lg transition-colors"
                                title="{{ __('View Details') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>

                        @if($layoutIsAdmin)
                            <!-- Edit Trigger (Pencil) -->
                            <button type="button"
                                    @click="openEdit({{ json_encode($book) }})"
                                    class="p-1.5 text-slate-500 hover:text-[#6366F1] hover:bg-white rounded-lg transition-colors"
                                    title="{{ __('Edit Book') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>

                            <!-- Delete Trigger (Trash) -->
                            <button type="button" 
                                    @click="confirmDelete({{ json_encode($book) }})" 
                                    class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-white rounded-lg transition-colors" 
                                    title="{{ __('Delete Book') }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 text-sm bg-white rounded-3xl border border-dashed border-slate-200">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <p class="font-semibold text-slate-700 mb-1">{{ __('No books found matching your filter criteria.') }}</p>
                <p class="text-xs text-slate-400">{{ __('Try clearing filters or search terms.') }}</p>
            </div>
        @endforelse
    </div>

    <!-- 2. TABLE VIEW -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">{{ __('Book / Author') }}</th>
                        <th class="py-3.5 px-6">{{ __('Category') }}</th>
                        <th class="py-3.5 px-6">{{ __('ISBN') }}</th>
                        <th class="py-3.5 px-6">{{ __('Shelf Location') }}</th>
                        <th class="py-3.5 px-6">{{ __('Status') }}</th>
                        <th class="py-3.5 px-6 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($books as $book)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3 cursor-pointer" @click="openDetail({{ json_encode($book) }})">
                                    <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                                         class="w-10 h-14 rounded-lg object-cover ring-1 ring-slate-200">
                                    <div>
                                        <p class="font-bold text-slate-800 max-w-xs truncate hover:text-[#1E3A8A] transition-colors">{{ $book->title }}</p>
                                        <p class="text-xs text-slate-500">{{ $book->author }} ({{ $book->published_year ?? 'N/A' }})</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-xs font-semibold text-[#6366F1]">
                                {{ $book->category ? __($book->category->name) : __('General') }}
                            </td>
                            <td class="py-3.5 px-6 text-xs font-mono text-slate-500">
                                {{ $book->isbn ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-6 text-xs text-slate-600 font-mono">
                                📍 {{ $book->location_shelf }}
                            </td>
                            <td class="py-3.5 px-6">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Available / Borrowed status -->
                                    @if($book->available_copies > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            {{ $book->available_copies }} / {{ $book->total_copies }} {{ __('Available') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                            0 / {{ $book->total_copies }} {{ __('Borrowed Out') }}
                                        </span>
                                    @endif

                                    <!-- Reserved tag -->
                                    @if($book->active_reservations_count > 0)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ __('Reserved') }} ({{ $book->active_reservations_count }})
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <!-- Read PDF Button -->
                                    <a href="{{ route('books.read', $book) }}"
                                       target="_blank"
                                       class="p-1.5 text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg transition-colors"
                                       title="{{ __('Read Online (PDF)') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    </a>

                                    <!-- View Details (Eye) -->
                                    <button type="button"
                                            @click="openDetail({{ json_encode($book) }})"
                                            class="p-1.5 text-slate-500 hover:text-[#1E3A8A] hover:bg-slate-100 rounded-lg transition-colors"
                                            title="{{ __('View Details') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    @if($layoutIsAdmin)
                                        <!-- Edit -->
                                        <button type="button"
                                                @click="openEdit({{ json_encode($book) }})"
                                                class="p-1.5 text-slate-500 hover:text-[#6366F1] hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Edit Book') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </button>

                                        <!-- Delete -->
                                        <button type="button" 
                                                @click="confirmDelete({{ json_encode($book) }})"
                                                class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Delete Book') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-8 text-slate-400 text-sm">{{ __('No books found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4 books-pagination-wrapper">
        {{ $books->fragment('catalog-section')->links() }}
    </div>
    </div> <!-- End #books-catalog-container -->

    <!-- MODAL 1: VIEW BOOK DETAILS (មើលព័ត៌មានលម្អិត) -->
    <div x-show="detailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="detailModalOpen = false">
            <template x-if="selectedBook">
                <div>
                    <!-- Modal Header -->
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                        <div class="pr-6">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#6366F1]" x-text="selectedBook.category ? selectedBook.category.name : '{{ __('General') }}'"></span>
                            <h2 class="text-xl font-bold text-slate-900 mt-0.5" x-text="selectedBook.title"></h2>
                            <p class="text-xs text-slate-500 mt-0.5" x-text="selectedBook.author + ' (' + (selectedBook.published_year || 'N/A') + ')'"></p>
                        </div>
                        <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100">&times;</button>
                    </div>

                    <!-- Book Body -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-12 gap-6">
                        <!-- Left: Cover & Stock -->
                        <div class="sm:col-span-5 flex flex-col items-center">
                            <div class="w-full aspect-3/4 rounded-2xl overflow-hidden shadow-lg ring-1 ring-slate-200">
                                <img :src="selectedBook.cover_image || 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80'" 
                                     :alt="selectedBook.title" 
                                     class="w-full h-full object-cover">
                            </div>

                            <!-- Real-time Status Badges (មាន / បានខ្ចី / បានកក់) -->
                            <div class="w-full mt-4 space-y-2">
                                <!-- Status: Available (មាន) -->
                                <template x-if="selectedBook.available_copies > 0">
                                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-[#10B981] animate-pulse"></span>
                                            <span>{{ __('Available') }} ({{ __('In Stock') }})</span>
                                        </span>
                                        <span x-text="selectedBook.available_copies + ' ' + '{{ __('Copies in Library') }}'"></span>
                                    </div>
                                </template>
                                <!-- Status: Borrowed (បានខ្ចី) -->
                                <template x-if="selectedBook.available_copies <= 0">
                                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                                            <span>{{ __('Borrowed') }} ({{ __('Borrowed Out') }})</span>
                                        </span>
                                        <span>0 {{ __('Left') }}</span>
                                    </div>
                                </template>
                                <!-- Status: Reserved (បានកក់) -->
                                <template x-if="selectedBook.active_reservations_count > 0">
                                    <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold">
                                        <span class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>{{ __('Reserved') }}</span>
                                        </span>
                                        <span x-text="selectedBook.active_reservations_count + ' ' + '{{ __('Active Reservations') }}'"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Right: Details, Location, Description -->
                        <div class="sm:col-span-7 space-y-4">
                            <!-- Quick Meta Grid -->
                            <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                                <div>
                                    <span class="text-slate-400 block">{{ __('Shelf Location') }}</span>
                                    <span class="font-mono font-bold text-slate-800" x-text="'📍 ' + (selectedBook.location_shelf || 'N/A')"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">{{ __('ISBN') }}</span>
                                    <span class="font-mono font-bold text-slate-800" x-text="selectedBook.isbn || 'N/A'"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">{{ __('Total Copies') }}</span>
                                    <span class="font-bold text-slate-800" x-text="selectedBook.total_copies"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">{{ __('Currently Borrowed') }}</span>
                                    <span class="font-bold text-indigo-600" x-text="selectedBook.active_borrows_count || (selectedBook.total_copies - selectedBook.available_copies)"></span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">{{ __('Total Views') }}</span>
                                    <span class="font-mono font-bold text-sky-700 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span x-text="new Intl.NumberFormat().format(selectedBook.views_count || 0)"></span>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-slate-400 block">{{ __('Total Downloads') }}</span>
                                    <span class="font-mono font-bold text-emerald-700 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        <span x-text="new Intl.NumberFormat().format(selectedBook.downloads_count || 0)"></span>
                                    </span>
                                </div>
                                <template x-if="selectedBook.target_academic_year">
                                    <div class="col-span-2 pt-2 border-t border-slate-200/60 flex items-center justify-between">
                                        <span class="text-slate-400">{{ __('Target Academic Year') }}:</span>
                                        <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100 text-xs" x-text="'🎯 ឆ្នាំទី ' + selectedBook.target_academic_year"></span>
                                    </div>
                                </template>
                                <template x-if="selectedBook.recommended_major">
                                    <div class="col-span-2 flex items-center justify-between">
                                        <span class="text-slate-400">{{ __('Recommended Major') }}:</span>
                                        <span class="font-medium text-slate-800 text-xs" x-text="selectedBook.recommended_major"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Description -->
                            <div>
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">{{ __('Description') }}</h4>
                                <p class="text-xs text-slate-600 leading-relaxed max-h-36 overflow-y-auto" 
                                   x-text="selectedBook.description || '{{ __('No detailed synopsis or summary available for this catalog entry.') }}'"></p>
                            </div>

                            <!-- Active Borrowers Preview -->
                            <template x-if="selectedBook.active_borrows && selectedBook.active_borrows.length > 0">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                        <span>{{ __('Active Borrowers') }}</span>
                                        <span class="text-[11px] text-indigo-600 font-semibold" x-text="selectedBook.active_borrows.length + ' {{ __('Active') }}'"></span>
                                    </h4>
                                    <div class="space-y-2 max-h-32 overflow-y-auto divide-y divide-slate-100">
                                        <template x-for="borrow in selectedBook.active_borrows" :key="borrow.id">
                                            <div class="pt-2 flex items-center justify-between text-xs">
                                                <span class="font-medium text-slate-800" x-text="borrow.user ? borrow.user.name : 'Patron'"></span>
                                                <span class="text-slate-500 font-mono text-[11px]" x-text="'Due: ' + (borrow.due_date ? borrow.due_date.substring(0, 10) : '')"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-100">
                        <a :href="'/books/' + selectedBook.id" 
                           class="text-xs font-semibold text-[#1E3A8A] hover:underline flex items-center gap-1">
                            <span>{{ __('Full Details') }} &rarr;</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                                {{ __('Cancel') }}
                            </button>

                            <!-- Read Online PDF -->
                            <a :href="'/books/' + selectedBook.id + '/read'"
                               target="_blank"
                               class="px-4 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-[#6366F1] text-xs font-semibold flex items-center gap-1.5 transition-colors border border-indigo-200">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span>{{ __('Read Online (PDF)') }}</span>
                            </a>

                            <!-- Download PDF (if allowed) -->
                            <template x-if="selectedBook.allow_pdf_download && selectedBook.pdf_file">
                                <a :href="'/books/' + selectedBook.id + '/download'"
                                   class="px-4 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold flex items-center gap-1.5 transition-colors border border-emerald-200"
                                   title="{{ __('Download PDF') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>{{ __('Download PDF') }}</span>
                                </a>
                            </template>

                            @if($layoutIsAdmin)
                                <button type="button" @click="openEdit(selectedBook)" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    <span>{{ __('Edit Book') }}</span>
                                </button>
                                <template x-if="selectedBook.available_copies > 0">
                                    <a :href="'/borrows?book_id=' + selectedBook.id" 
                                       class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-semibold shadow-md flex items-center gap-1.5 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>{{ __('Issue Book') }}</span>
                                    </a>
                                </template>
                            @else
                                <template x-if="selectedBook.available_copies > 0">
                                    <div>
                                        <template x-if="approvedBookIds.includes(selectedBook.id)">
                                            <span class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 shadow-xs">
                                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                <span>{{ __('បានកក់ជោគជ័យ') }}</span>
                                            </span>
                                        </template>
                                        <template x-if="!approvedBookIds.includes(selectedBook.id) && pendingBookIds.includes(selectedBook.id)">
                                            <span class="px-4 py-2 rounded-xl bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold flex items-center gap-1.5 shadow-xs">
                                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                                <span>{{ __('រង់ចាំ Admin ទទួលការកក់') }}</span>
                                            </span>
                                        </template>
                                        <template x-if="!approvedBookIds.includes(selectedBook.id) && !pendingBookIds.includes(selectedBook.id)">
                                            <button type="button" 
                                                    @click.prevent.stop="quickReserve(selectedBook.id)"
                                                    :disabled="reservingId === selectedBook.id"
                                                    class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 active:scale-95 text-white text-xs font-semibold shadow-md flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-75">
                                                <template x-if="reservingId === selectedBook.id">
                                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                </template>
                                                <template x-if="reservingId !== selectedBook.id">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </template>
                                                <span x-text="reservingId === selectedBook.id ? '{{ __('កំពុងផ្ញើសារការកក់...') }}' : '{{ __('Reserve Book') }}'"></span>
                                            </button>
                                        </template>
                                    </div>
                                </template>
                            @endif
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 2: ADD BOOK (បន្ថែមសៀវភៅថ្មី) -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="addModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Add New Book to Collection') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Enter book metadata, shelf assignment and inventory copy count.') }}</p>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            @if($errors->any() && !old('_method'))
                <div class="mt-4 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                    <div class="font-bold mb-1 flex items-center gap-1.5 text-rose-900">
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <span>{{ __('សូមពិនិត្យព័ត៌មានដែលបានបញ្ចូល៖') }}</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Book Title') }} *</label>
                        <input type="text" name="title" required value="{{ old('title') }}" placeholder="{{ __('e.g. Clean Architecture') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Author') }} *</label>
                        <input type="text" name="author" required value="{{ old('author') }}" placeholder="{{ __('e.g. Robert C. Martin') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('ISBN') }}</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}" placeholder="978-0134494166" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Category') }}</label>
                        <select name="category_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="">-- {{ __('ជ្រើសរើសប្រភេទ / មហាវិទ្យាល័យ') }} --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ __($cat->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Shelf Location') }} *</label>
                        <input type="text" name="location_shelf" required placeholder="{{ __('e.g. Shelf CS-02') }}" value="{{ old('location_shelf', 'Shelf A-1') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Total Copies') }} *</label>
                        <input type="number" name="total_copies" required min="1" value="{{ old('total_copies', 5) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Published Year') }}</label>
                        <input type="number" name="published_year" value="{{ old('published_year', 2024) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>

                    <!-- Smart Academic Year & Major Recommendation (ការណែនាំតាមឆ្នាំសិក្សា) -->
                    <div class="sm:col-span-2 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
                        <span class="text-xs font-bold text-indigo-900 block flex items-center gap-1.5">
                            <span>🎯 {{ __('Smart Academic Recommendation (ការណែនាំតាមឆ្នាំសិក្សា)') }}</span>
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Target Academic Year (ឆ្នាំសិក្សាគោលដៅ)') }}</label>
                                <select name="target_academic_year" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white outline-none">
                                    <option value="" {{ old('target_academic_year') == '' ? 'selected' : '' }}>{{ __('All Academic Years / General') }}</option>
                                    <option value="1" {{ old('target_academic_year') == '1' ? 'selected' : '' }}>ឆ្នាំទី ១ (Year 1)</option>
                                    <option value="2" {{ old('target_academic_year') == '2' ? 'selected' : '' }}>ឆ្នាំទី ២ (Year 2)</option>
                                    <option value="3" {{ old('target_academic_year') == '3' ? 'selected' : '' }}>ឆ្នាំទី ៣ (Year 3)</option>
                                    <option value="4" {{ old('target_academic_year') == '4' ? 'selected' : '' }}>ឆ្នាំទី ៤ (Year 4)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Recommended Major / Dept') }}</label>
                                <input type="text" name="recommended_major" value="{{ old('recommended_major') }}" placeholder="{{ __('e.g. Computer Science, Business') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white outline-none">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cover Image Upload (រូបភាពគម្របមែនទែន) -->
                    <div class="sm:col-span-2">
                        <input type="hidden" name="pdf_cover_data" :value="pdfCoverData">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Book Cover Image') }}</label>
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
                                <div class="flex items-center gap-2 flex-wrap">
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 rounded-xl text-xs font-bold text-[#1E3A8A] cursor-pointer transition-all shadow-xs">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span>{{ __('Choose Cover Image') }}</span>
                                        <input type="file" name="cover_image_file" accept="image/*" class="hidden" @change="if ($event.target.files.length) { coverPreview = URL.createObjectURL($event.target.files[0]); coverFileName = $event.target.files[0].name; autoCoverExtracted = false; pdfCoverData = ''; }">
                                    </label>
                                    <template x-if="autoCoverExtracted">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                             {{ __('ស្រង់ពីទំព័រទី១ នៃ PDF') }}
                                        </span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1.5 truncate" x-text="coverFileName ? '📄 ' + coverFileName : (autoCoverExtracted ? '✓ {{ __('បានស្រង់យករូបគម្របទំព័រទី១ ពី File PDF ដោយស្វ័យប្រវត្តិ') }}' : '{{ __('Supports JPG, PNG, WEBP up to 5MB (or auto-extract from PDF)') }}')"></p>
                            </div>
                        </div>
                        <div class="mt-1.5">
                            <input type="url" name="cover_image" value="{{ old('cover_image') }}" placeholder="{{ __('Or enter direct URL') }} (https://images.unsplash.com/...)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 focus:ring-2 focus:ring-indigo-100 outline-none">
                        </div>
                    </div>

                    <!-- PDF Document Upload (ឯកសារ PDF មែនទែន) -->
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
                                <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-rose-300 hover:bg-rose-50/40 rounded-xl text-xs font-bold text-rose-600 cursor-pointer transition-all shadow-xs">
                                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span>{{ __('Choose PDF Document') }}</span>
                                    <input type="file" name="pdf_upload" accept="application/pdf" class="hidden" @change="handlePdfSelected($event, false)">
                                </label>
                                <p class="text-[11px] text-slate-500 mt-1.5 font-mono truncate" x-text="pdfFileName ? '📕 ' + pdfFileName : '{{ __('Supports PDF up to 1GB (System auto-extracts page 1 as cover)') }}'"></p>
                                <div x-show="isExtractingCover" class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-indigo-600 animate-pulse">
                                    <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    <span>{{ __('កំពុងស្រង់យករូបគម្របទំព័រទី១ ពី PDF ដោយស្វ័យប្រវត្តិ...') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-1.5">
                            <input type="url" name="pdf_file" value="{{ old('pdf_file') }}" placeholder="{{ __('Or enter direct URL') }} (https://.../document.pdf)" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                        </div>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-2 pt-1">
                        <input type="checkbox" id="add_pdf_dl" name="allow_pdf_download" value="1" {{ old('allow_pdf_download') ? 'checked' : '' }} class="rounded text-[#6366F1] focus:ring-indigo-200">
                        <label for="add_pdf_dl" class="text-xs text-slate-700 font-medium cursor-pointer">{{ __('Allow Member to Download/Print PDF (If unchecked, view-only in app)') }}</label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Description / Summary') }}</label>
                        <textarea name="description" rows="3" placeholder="{{ __('Short description of this book...') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">{{ old('description') }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">{{ __('Save Book') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: EDIT BOOK (កែប្រែព័ត៌មានសៀវភៅ) -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Edit Book Information') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Update book details, shelf position or inventory copies.') }}</p>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            <form :action="'/books/' + currentBook.id" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
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
                            @foreach($categories as $cat)
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
                        <input type="hidden" name="pdf_cover_data" :value="editPdfCoverData">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Book Cover Image') }}</label>
                        <div class="p-3.5 bg-slate-50/80 border border-dashed border-slate-300 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center gap-3.5 transition-all hover:bg-slate-50 hover:border-indigo-300">
                            <div class="w-16 h-20 rounded-xl bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-xs relative">
                                <template x-if="isEditExtractingCover">
                                    <div class="absolute inset-0 bg-indigo-50/90 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </div>
                                </template>
                                <template x-if="editCoverPreview">
                                    <img :src="editCoverPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!editCoverPreview && !isEditExtractingCover">
                                    <div class="text-center p-2 text-slate-300">
                                        <svg class="w-7 h-7 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <label class="inline-flex items-center gap-2 px-3.5 py-2 bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 rounded-xl text-xs font-bold text-[#1E3A8A] cursor-pointer transition-all shadow-xs">
                                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span>{{ __('Choose Cover Image') }}</span>
                                        <input type="file" name="cover_image_file" accept="image/*" class="hidden" @change="if ($event.target.files.length) { editCoverPreview = URL.createObjectURL($event.target.files[0]); editCoverFileName = $event.target.files[0].name; editAutoCoverExtracted = false; editPdfCoverData = ''; }">
                                    </label>
                                    <template x-if="editAutoCoverExtracted">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            ⚡ {{ __('ស្រង់ពីទំព័រទី១ នៃ PDF') }}
                                        </span>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1.5 truncate" x-text="editCoverFileName ? '📄 ' + editCoverFileName : (editAutoCoverExtracted ? '✓ {{ __('បានស្រង់យករូបគម្របទំព័រទី១ ពី File PDF') }}' : '{{ __('Keep current file or upload a new replacement') }}')"></p>
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
                                        <input type="file" name="pdf_upload" accept="application/pdf" class="hidden" @change="handlePdfSelected($event, true)">
                                    </label>
                                    <template x-if="currentBook.pdf_file">
                                        <button type="button" 
                                                @click="extractCurrentPdfCover()" 
                                                :disabled="isEditExtractingCover"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold border border-indigo-200 transition-all cursor-pointer">
                                            <span x-show="!isEditExtractingCover">⚡ {{ __('ស្រង់រូបគម្របពី PDF បច្ចុប្បន្ន') }}</span>
                                            <span x-show="isEditExtractingCover" class="animate-pulse flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                {{ __('កំពុងស្រង់...') }}
                                            </span>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1.5 font-mono truncate" x-text="editPdfFileName ? '📕 ' + editPdfFileName : (currentBook.pdf_file ? '✓ {{ __('Current PDF Document') }}' : '{{ __('Supports PDF up to 1GB (ប្រព័ន្ធស្រង់ទំព័រទី១ ជាគម្របស្វ័យប្រវត្តិ)') }}')"></p>
                                <div x-show="isEditExtractingCover" class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-indigo-600 animate-pulse">
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
                        <input type="checkbox" id="edit_pdf_dl" name="allow_pdf_download" value="1" x-model="currentBook.allow_pdf_download" class="rounded text-[#6366F1] focus:ring-indigo-200">
                        <label for="edit_pdf_dl" class="text-xs text-slate-700 font-medium cursor-pointer">{{ __('Allow Member to Download/Print PDF (If unchecked, view-only in app)') }}</label>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Description / Summary') }}</label>
                        <textarea name="description" rows="3" x-model="currentBook.description" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md shadow-blue-950/20">{{ __('Update Book') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: DELETE CONFIRMATION (លុបសៀវភៅ) -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center" @click.away="deleteModalOpen = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            
            <h3 class="text-lg font-bold text-slate-900 mb-1">{{ __('Delete Book Confirmation') }}</h3>
            <p class="text-xs text-slate-500 mb-6">
                {{ __('This action will permanently remove') }} <strong class="text-slate-800" x-text="'«' + bookToDelete.title + '»'"></strong> {{ __('from the library catalog.') }}
            </p>

            <form :action="'/books/' + bookToDelete.id" method="POST" class="flex items-center justify-center gap-3">
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

    <!-- FLOATING IN-PLACE TOAST ALERT (គ្មានការឡូតផ្ទាំងផ្សេង / No page reload) -->
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
                <h4 class="text-xs font-bold text-slate-900" x-text="toastType === 'success' ? '{{ __('Reservation Confirmed') }}' : '{{ __('Hold Notice') }}'"></h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium" x-text="toastMessage"></p>
            <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                <span>🔔</span>
                <span>{{ __('Librarian/Admin has been notified automatically.') }}</span>
            </p>
        </div>
        <button @click="showToast = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors">&times;</button>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let isCatalogFetching = false;

    // 1. Core Seamless In-Place Fetcher (Zero Reload, Zero Jerk)
    async function loadBooksCatalog(url, pushState = true, isPagination = false) {
        if (!url || isCatalogFetching) return;
        isCatalogFetching = true;

        const container = document.getElementById('books-catalog-container');
        const pillsTrack = document.getElementById('category-pills-track');
        const savedPillsScrollLeft = pillsTrack ? pillsTrack.scrollLeft : 0;

        // Visual subtle feedback: slight opacity without layout shift
        if (container) {
            container.style.opacity = '0.6';
            container.style.transition = 'opacity 0.15s ease';
            container.style.pointerEvents = 'none';
        }

        try {
            const res = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) {
                window.location.href = url;
                return;
            }

            const html = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.getElementById('books-catalog-container');

            if (newContainer && container) {
                // Safely clean up Alpine sub-tree if present
                if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
                    try { window.Alpine.destroyTree(container); } catch (e) {}
                }

                // In-place DOM update (smooth, ZERO page reload)
                container.innerHTML = newContainer.innerHTML;

                // Re-initialize Alpine on the new elements
                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                    try { window.Alpine.initTree(container); } catch (e) {}
                }

                // Restore horizontal scroll of pills track
                const updatedPillsTrack = document.getElementById('category-pills-track');
                if (updatedPillsTrack) {
                    updatedPillsTrack.scrollLeft = savedPillsScrollLeft;
                }

                // Update browser URL without reload
                if (pushState && window.history && window.history.pushState) {
                    window.history.pushState({ catalogUrl: url }, '', url);
                }

                // Only scroll up if it was pagination clicked from the bottom
                if (isPagination) {
                    const catalogHeading = document.getElementById('catalog-section');
                    if (catalogHeading) {
                        catalogHeading.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            } else {
                window.location.href = url;
            }
        } catch (e) {
            console.error('Seamless catalog fetch error:', e);
            window.location.href = url;
        } finally {
            if (container) {
                container.style.opacity = '1';
                container.style.pointerEvents = 'auto';
            }
            isCatalogFetching = false;
        }
    }

    // 2. Intercept clicks on category pills, sort dropdown, filter badges, and pagination
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        // Never intercept buttons opening modals, PDF downloads, or external links
        if (link.getAttribute('target') === '_blank' || 
            link.hasAttribute('download') || 
            link.getAttribute('href')?.startsWith('javascript:') ||
            link.getAttribute('href')?.startsWith('#modal')) {
            return;
        }

        const isPill = link.closest('#category-pills-track');
        const isSort = link.closest('#catalog-controls');
        const isBadge = link.closest('#active-filter-badges');
        const isPagination = link.closest('.books-pagination-wrapper') || link.closest('[aria-label="Pagination Navigation"]') || link.closest('.pagination');
        const hasCatalogHash = link.getAttribute('href') && link.getAttribute('href').includes('#catalog-section');

        if (isPill || isSort || isBadge || isPagination || hasCatalogHash) {
            const href = link.getAttribute('href');
            if (!href || href === '#') return;

            e.preventDefault();

            // Instant visual feedback for clicked category pill
            if (isPill) {
                const pillsTrack = link.closest('#category-pills-track');
                if (pillsTrack) {
                    const allPills = pillsTrack.querySelectorAll('a');
                    allPills.forEach(p => {
                        p.className = p.className
                            .replace('bg-[#dce4ea] border border-[#23677a] text-[#23677a] font-semibold active-category-pill shadow-2xs', 'bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-800 font-medium shadow-2xs')
                            .replace('active-category-pill', '');
                    });
                    link.className = link.className
                        .replace('bg-white hover:bg-slate-50 border border-slate-200/80 text-slate-800 font-medium shadow-2xs', 'active-category-pill bg-[#dce4ea] border border-[#23677a] text-[#23677a] font-semibold shadow-2xs');
                }
            }

            loadBooksCatalog(href, true, isPagination);
        }
    }, true);

    // 3. Intercept Filter Drawer Form Submission
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form && form.id === 'filter-drawer-form') {
            e.preventDefault();
            const formData = new FormData(form);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value && value !== 'all') {
                    params.set(key, value);
                }
            }
            const actionBase = form.getAttribute('action') ? form.getAttribute('action').split('#')[0] : '{{ route('books.index') }}';
            const finalUrl = actionBase + (params.toString() ? '?' + params.toString() : '');

            // Close filter drawer using Alpine root component
            const alpineRoot = document.querySelector('[x-data]');
            if (alpineRoot && alpineRoot._x_dataStack && alpineRoot._x_dataStack[0]) {
                alpineRoot._x_dataStack[0].filterDrawerOpen = false;
            }

            loadBooksCatalog(finalUrl, true, false);
        }
    }, true);

    // 4. Browser Back/Forward Navigation Support
    window.addEventListener('popstate', function() {
        if (window.location.pathname.includes('/books')) {
            loadBooksCatalog(window.location.href, false, false);
        }
    });

    // 5. Initial pill horizontal centering if active
    try {
        const pillsTrack = document.getElementById('category-pills-track');
        if (pillsTrack) {
            const activePill = pillsTrack.querySelector('.active-category-pill');
            if (activePill && !activePill.textContent.includes('{{ __('ទាំងអស់') }}')) {
                const trackRect = pillsTrack.getBoundingClientRect();
                const pillRect = activePill.getBoundingClientRect();
                if (pillRect.left < trackRect.left || pillRect.right > trackRect.right) {
                    activePill.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'instant' });
                }
            }
        }
    } catch(e) {}
});
</script>
@endpush
