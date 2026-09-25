@props([
    'books' => null,
    'featuredBooks' => collect(),
    'latestBooks' => collect(),
    'trendingBooks' => collect(),
    'recommendedBooks' => collect(),
    'alpineClick' => null,
])

@php
    // បង្ហាញសៀវភៅទាំងអស់ដែលមាននៅក្នុងបណ្ណាល័យរបស់យើង (All books in the library)
    if (!empty($books) && (is_countable($books) ? count($books) > 0 : true)) {
        $displayBooks = $books;
    } else {
        $displayBooks = \App\Models\Book::with(['category', 'activeBorrows', 'activeReservations'])
            ->orderBy('id', 'desc')
            ->get();
    }
@endphp

<div x-data="{
    isPaused: false,
    speed: 0.95,
    scrollPos: 0,
    animId: null,
    
    init() {
        this.$nextTick(() => {
            const el = this.$refs.slider;
            if (el) {
                this.scrollPos = el.scrollLeft || 0;
                this.startContinuousRun();
            }
        });
    },
    
    startContinuousRun() {
        if (this.animId) cancelAnimationFrame(this.animId);
        const loop = () => {
            if (!this.isPaused) {
                const el = this.$refs.slider;
                const trackA = this.$refs.trackA;
                if (el && trackA) {
                    const trackWidth = trackA.offsetWidth;
                    if (trackWidth > 50) {
                        this.scrollPos += this.speed;
                        if (this.scrollPos >= trackWidth) {
                            this.scrollPos -= trackWidth;
                        }
                        el.scrollLeft = this.scrollPos;
                    }
                }
            }
            this.animId = requestAnimationFrame(loop);
        };
        this.animId = requestAnimationFrame(loop);
    },
    
    onScroll() {
        if (this.isPaused) {
            const el = this.$refs.slider;
            const trackA = this.$refs.trackA;
            if (!el || !trackA) return;
            const trackWidth = trackA.offsetWidth;
            if (trackWidth > 50) {
                if (el.scrollLeft >= trackWidth) {
                    el.scrollLeft -= trackWidth;
                } else if (el.scrollLeft <= 0) {
                    el.scrollLeft += trackWidth;
                }
                this.scrollPos = el.scrollLeft;
            }
        }
    },
    
    slideLeft() {
        const el = this.$refs.slider;
        const trackA = this.$refs.trackA;
        if (!el || !trackA) return;
        this.isPaused = true;
        const trackWidth = trackA.offsetWidth;
        if (el.scrollLeft <= 280) {
            el.scrollLeft += trackWidth;
        }
        let target = el.scrollLeft - 280;
        el.scrollTo({ left: target, behavior: 'smooth' });
        this.scrollPos = target;
        setTimeout(() => { this.isPaused = false; }, 450);
    },
    
    slideRight() {
        const el = this.$refs.slider;
        const trackA = this.$refs.trackA;
        if (!el || !trackA) return;
        this.isPaused = true;
        const trackWidth = trackA.offsetWidth;
        let target = el.scrollLeft + 280;
        if (target >= trackWidth) {
            target -= trackWidth;
            el.scrollLeft -= trackWidth;
        }
        el.scrollTo({ left: target, behavior: 'smooth' });
        this.scrollPos = target;
        setTimeout(() => { this.isPaused = false; }, 450);
    }
}" 
class="w-full mb-8 font-khmer select-none"
@mouseenter="isPaused = true" 
@mouseleave="isPaused = false"
@touchstart.passive="isPaused = true"
@touchend.passive="isPaused = false">

    <!-- ========================================================================= -->
    <!-- BOOKSHELF STAGE CONTAINER (Clean Rounded Box without Top Tabs) -->
    <!-- ========================================================================= -->
    <div class="relative bg-gradient-to-b from-[#edf4fa] via-[#e5eff8] to-[#d6e4f0] border border-[#b8ccdd] rounded-3xl shadow-sm overflow-hidden pt-6 pb-2">

        <!-- Navigation Arrow: Left (<) -->
        <button type="button" 
                @click.stop="slideLeft()"
                class="absolute left-2 sm:left-4 top-[45%] -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0B4B75] hover:bg-[#073655] active:scale-95 text-white flex items-center justify-center shadow-lg transition-all cursor-pointer ring-2 ring-white/60 group"
                title="Previous">
            <svg class="w-5 h-5 text-white group-hover:-translate-x-0.5 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- Navigation Arrow: Right (>) -->
        <button type="button" 
                @click.stop="slideRight()"
                class="absolute right-2 sm:right-4 top-[45%] -translate-y-1/2 z-30 w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#0B4B75] hover:bg-[#073655] active:scale-95 text-white flex items-center justify-center shadow-lg transition-all cursor-pointer ring-2 ring-white/60 group"
                title="Next">
            <svg class="w-5 h-5 text-white group-hover:translate-x-0.5 transition-transform" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
            </svg>
        </button>

        <!-- Subtle edge vignettes for seamless infinite entry & exit -->
        <div class="pointer-events-none absolute left-0 top-0 bottom-6 w-8 sm:w-12 bg-gradient-to-r from-[#edf4fa]/90 to-transparent z-20"></div>
        <div class="pointer-events-none absolute right-0 top-0 bottom-6 w-8 sm:w-12 bg-gradient-to-l from-[#d6e4f0]/90 to-transparent z-20"></div>

        <!-- ===================================================================== -->
        <!-- BOOKS TRACK SLIDER (Infinite continuous forward run) -->
        <!-- ===================================================================== -->
        <div x-ref="slider" 
             @scroll.passive="onScroll()"
             class="overflow-x-auto no-scrollbar flex items-end pl-6 sm:pl-8 pt-2 pb-3">
            @if($displayBooks->isNotEmpty())
                <!-- Track 1 -->
                <div x-ref="trackA" class="flex items-end gap-6 sm:gap-8 shrink-0 pr-6 sm:pr-8">
                    @foreach($displayBooks as $book)
                        <div class="group relative shrink-0 cursor-pointer flex flex-col items-center"
                             @if($alpineClick) @click="{{ $alpineClick }}({{ json_encode($book) }})" @else onclick="window.location='{{ route('books.show', $book) }}'" @endif>
                            <!-- Book Cover Standing Upright -->
                            <div class="relative w-[130px] sm:w-[155px] md:w-[170px] aspect-[2/3] rounded-md overflow-hidden shadow-[0_14px_28px_-4px_rgba(15,35,60,0.38),0_6px_12px_-2px_rgba(15,35,60,0.22)] ring-1 ring-black/10 transition-all duration-300 transform group-hover:scale-105 group-hover:-translate-y-2.5 group-hover:shadow-[0_22px_36px_-4px_rgba(15,35,60,0.5)] bg-slate-200">
                                <img src="{{ $book->cover_image ? asset(ltrim($book->cover_image, '/')) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                                     alt="{{ $book->title }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                                
                                <!-- Left Spine Edge Highlight -->
                                <div class="absolute inset-y-0 left-0 w-1 bg-white/25"></div>
                            </div>

                            <!-- Hover Details Tooltip -->
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute -top-8 bg-slate-900/90 text-white text-[11px] font-medium px-2.5 py-1 rounded-md shadow-md pointer-events-none whitespace-nowrap max-w-[200px] truncate z-40">
                                {{ $book->title }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Track 2 (Identical set for seamless infinite forward looping) -->
                <div x-ref="trackB" class="flex items-end gap-6 sm:gap-8 shrink-0 pr-6 sm:pr-8" aria-hidden="true">
                    @foreach($displayBooks as $book)
                        <div class="group relative shrink-0 cursor-pointer flex flex-col items-center"
                             @if($alpineClick) @click="{{ $alpineClick }}({{ json_encode($book) }})" @else onclick="window.location='{{ route('books.show', $book) }}'" @endif>
                            <!-- Book Cover Standing Upright -->
                            <div class="relative w-[130px] sm:w-[155px] md:w-[170px] aspect-[2/3] rounded-md overflow-hidden shadow-[0_14px_28px_-4px_rgba(15,35,60,0.38),0_6px_12px_-2px_rgba(15,35,60,0.22)] ring-1 ring-black/10 transition-all duration-300 transform group-hover:scale-105 group-hover:-translate-y-2.5 group-hover:shadow-[0_22px_36px_-4px_rgba(15,35,60,0.5)] bg-slate-200">
                                <img src="{{ $book->cover_image ? asset(ltrim($book->cover_image, '/')) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                                     alt="{{ $book->title }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover">
                                
                                <!-- Left Spine Edge Highlight -->
                                <div class="absolute inset-y-0 left-0 w-1 bg-white/25"></div>
                            </div>

                            <!-- Hover Details Tooltip -->
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute -top-8 bg-slate-900/90 text-white text-[11px] font-medium px-2.5 py-1 rounded-md shadow-md pointer-events-none whitespace-nowrap max-w-[200px] truncate z-40">
                                {{ $book->title }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-12 w-full text-center text-slate-400 text-xs">{{ __('No books available.') }}</div>
            @endif
        </div>

        <!-- ===================================================================== -->
        <!-- 3D BOOKSHELF BASE LEDGE (Physical shelf line under books) -->
        <!-- ===================================================================== -->
        <div class="w-full relative pointer-events-none mt-1">
            <!-- Shelf top highlight line -->
            <div class="h-1.5 w-full bg-gradient-to-r from-[#b5cadc] via-[#e8f2fa] to-[#b5cadc] border-t border-white/90 shadow-xs"></div>
            <!-- Shelf front face (thick shelf look) -->
            <div class="h-4 w-full bg-gradient-to-b from-[#96b4cb] to-[#7998b0] border-b border-[#63829b] shadow-sm flex items-center justify-between px-4">
                <div class="h-full w-full bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
            </div>
            <!-- Shelf bottom bevel drop shadow -->
            <div class="h-3.5 w-full bg-gradient-to-b from-[#5c778e]/35 to-transparent"></div>
        </div>
    </div>
</div>
