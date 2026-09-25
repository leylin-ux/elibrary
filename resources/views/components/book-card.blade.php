@props(['book'])

<div class="bg-white rounded-2xl p-3 border border-slate-100 shadow-2xs hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between group cursor-pointer"
     @if(isset($alpineClick)) @click="{{ $alpineClick }}" @else onclick="window.location='{{ route('books.show', $book) }}'" @endif>
    <div>
        <!-- Book Cover with ambient backdrop matching screenshot -->
        <div class="relative aspect-[3/4.2] rounded-xl overflow-hidden bg-gradient-to-b from-sky-100/50 via-slate-50 to-indigo-50/30 p-2 mb-2.5 flex items-center justify-center shadow-inner">
            <img src="{{ $book->cover_image ? asset(ltrim($book->cover_image, '/')) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80' }}" 
                 alt="{{ $book->title }}" 
                 loading="lazy"
                 class="w-full h-full object-contain rounded-lg shadow-sm group-hover:scale-[1.03] transition-transform duration-300">
            
            <!-- Academic Year Pill on top left -->
            @if(!empty($book->target_academic_year))
                <div class="absolute top-2 left-2">
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-600/90 backdrop-blur-xs text-white shadow-xs">
                        🎯 {{ __('ឆ្នាំទី :year', ['year' => $book->target_academic_year]) }}
                    </span>
                </div>
            @endif

            <!-- Subtle status pill on top right -->
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

            <!-- Quick Read Online Badge if PDF exists -->
            @if($book->pdf_file)
                <div class="absolute bottom-2 left-2">
                    <span class="px-2 py-0.5 rounded-md bg-slate-900/75 backdrop-blur-xs text-white text-[9px] font-semibold flex items-center gap-1">
                        <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                        PDF
                    </span>
                </div>
            @endif
        </div>

        <!-- Book Title (2 lines clamp) -->
        <h3 class="font-bold text-slate-800 text-[13px] line-clamp-2 leading-snug min-h-[38px] group-hover:text-[#1E3A8A] transition-colors" 
            title="{{ $book->title }}">
            {{ $book->title }}
        </h3>

        <!-- Author with 'by' prefix -->
        <p class="text-[11px] text-slate-400 mt-1 truncate" title="{{ $book->author }}">
            by <span class="text-slate-500 font-medium">{{ $book->author }}</span>
        </p>
    </div>

    <!-- Bottom Stats: Views and Downloads / Borrows -->
    <div class="flex items-center gap-3.5 text-[11px] text-slate-400 mt-2.5 pt-2 border-t border-slate-100/70">
        <!-- Views Count (Eye) -->
        <span class="flex items-center gap-1 hover:text-slate-600 transition-colors" title="{{ __('Total Views') }}: {{ number_format($book->views_count ?? 0) }}">
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="font-medium font-mono text-[10px]" id="book-card-views-{{ $book->id }}">{{ number_format($book->views_count ?? 0) }}</span>
        </span>

        <!-- Downloads / Loans Count (Download Tray) -->
        <span class="flex items-center gap-1 hover:text-slate-600 transition-colors" title="{{ __('Total Downloads / Loans') }}: {{ number_format($book->downloads_count ?? 0) }}">
            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            <span class="font-medium font-mono text-[10px]">{{ number_format($book->downloads_count ?? 0) }}</span>
        </span>
    </div>
</div>
