@extends('layouts.app')

@section('title', __('Dashboard') . ' - ' . $user->name)

@section('content')
<div class="space-y-6">

    <!-- Top Interactive Auto-Sliding Bookshelf Carousel (បង្ហាញសៀវភៅទាំងអស់ដែលមានក្នុងបណ្ណាល័យ) -->
    <x-bookshelf-carousel :books="$allLibraryBooks" />

    <!-- Personal Metrics Grid (4 Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Currently Borrowed -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ __('Currently Borrowed') }}</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-black text-slate-800">{{ $myBorrowsCount }}</span>
                    <span class="text-xs text-slate-400 font-medium">/ {{ $quotaLimit }} {{ __('books limit') }}</span>
                </div>
                <span class="text-[11px] text-emerald-600 font-semibold mt-1 block">
                    {{ $remainingQuota }} {{ __('more allowed') }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-[#6366F1] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        </div>

        <!-- Card 2: Overdue Alert -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ __('Overdue Alert') }}</span>
                <span class="text-2xl font-black {{ $myOverdueCount > 0 ? 'text-rose-600' : 'text-slate-800' }} mt-1 block">
                    {{ $myOverdueCount }}
                </span>
                <span class="text-[11px] {{ $myOverdueCount > 0 ? 'text-rose-600 font-bold' : 'text-slate-400' }} mt-1 block">
                    {{ $myOverdueCount > 0 ? __('Action Required') : __('All returns on schedule') }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl {{ $myOverdueCount > 0 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-400' }} flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>

        <!-- Card 3: Unpaid Fines -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ __('Fine ($)') }}</span>
                <span class="text-2xl font-black {{ $myUnpaidFines > 0 ? 'text-amber-600' : 'text-emerald-700' }} mt-1 block">
                    ${{ number_format($myUnpaidFines, 2) }}
                </span>
                <span class="text-[11px] {{ $myUnpaidFines > 0 ? 'text-amber-600 font-semibold' : 'text-slate-400' }} mt-1 block">
                    {{ $myUnpaidFines > 0 ? __('Unpaid balance at desk') : __('Good standing') }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
        </div>

        <!-- Card 4: Active Holds / Reservations -->
        <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">{{ __('Reservations') }}</span>
                <span class="text-2xl font-black text-[#1E3A8A] mt-1 block">
                    {{ $myReservations->count() }}
                </span>
                <span class="text-[11px] text-blue-600 font-semibold mt-1 block">
                    {{ __('Active hold requests') }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>

    <!-- UNPAID FINES / BORROWING POLICY ALERT BANNER -->
    @if($myUnpaidFines > 0)
        <div class="rounded-3xl p-5 bg-rose-50/90 border border-rose-200 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start sm:items-center gap-3.5">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white shadow-md shadow-rose-600/20 flex items-center justify-center text-xl shrink-0">
                        ⚠️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700">
                                🛑 {{ __('Borrowing Temporarily Blocked (ផ្អាកការខ្ចីសៀវភៅបណ្ដោះអាសន្ន)') }}
                            </span>
                            <span class="text-xs font-mono font-bold text-rose-700">${{ number_format($myUnpaidFines, 2) }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mt-1">
                            {{ __('លោកអ្នកមានប្រាក់ពិន័យជំពាក់ (សងសៀវភៅយឺត ឬបាត់បង់សៀវភៅ) ចំនួន $:amount', ['amount' => number_format($myUnpaidFines, 2)]) }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('ដើម្បីអាចខ្ចីសៀវភៅថ្មីបានបន្ត សូមអញ្ជើញមកទូទាត់ប្រាក់ពិន័យនៅបញ្ជរបណ្ណាល័យផ្ទាល់។') }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('borrows.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 text-white hover:bg-rose-700 shadow-sm transition-colors">
                        {{ __('ពិនិត្យប្រវត្តិខ្ចីសង') }} &rarr;
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- SMART ACADEMIC YEAR RECOMMENDATIONS (ការណែនាំសៀវភៅតាមឆ្នាំសិក្សាឆ្លាតវៃសម្រាប់និស្សិត) -->
    @if($user->isStudent() && $academicYearBooks->isNotEmpty())
        <x-book-showcase-section 
            :title="__('សៀវភៅណែនាំសម្រាប់និស្សិត :year', ['year' => $user->academic_year_label])" 
            icon="🎯" 
            iconBg="bg-indigo-50 text-indigo-600 border-indigo-100/70"
            :books="$academicYearBooks" 
            :viewAllUrl="route('books.index', ['academic_year' => $user->academic_year])" />
    @endif

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

    <!-- Active Loans Section (សៀវភៅដែលខ្ញុំកំពុងខ្ចី) -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('Currently Borrowed Books') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Physical books in your custody. Please return before due date.') }}</p>
            </div>
            <a href="{{ route('borrows.index') }}" class="text-xs font-semibold text-[#6366F1] hover:underline">
                {{ __('View Circulation History') }} &rarr;
            </a>
        </div>

        @if($activeBorrows->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($activeBorrows as $loan)
                    @php
                        $dueDate = \Carbon\Carbon::parse($loan->due_date);
                        $isOverdue = $loan->status === 'Overdue' || \Carbon\Carbon::today()->gt($dueDate);
                        $daysDiff = \Carbon\Carbon::today()->diffInDays($dueDate, false);
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $isOverdue ? 'bg-rose-50/40 border-rose-200' : 'bg-slate-50/70 border-slate-200/60' }} flex items-start gap-4">
                        <img src="{{ $loan->book?->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                             alt="{{ $loan->book?->title ?? 'Book' }}" 
                             class="w-16 h-22 rounded-xl object-cover ring-1 ring-slate-200 shadow-xs shrink-0">
                        <div class="flex-1 min-w-0">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#6366F1]">
                                {{ $loan->book?->category ? __($loan->book->category->name) : __('General') }}
                            </span>
                            <h3 class="font-bold text-slate-900 text-sm truncate mt-0.5" title="{{ $loan->book?->title ?? '' }}">
                                {{ $loan->book?->title ?? __('Book Deleted / Unavailable') }}
                            </h3>
                            <p class="text-xs text-slate-500 truncate">{{ $loan->book?->author ?? '' }}</p>
                            
                            <div class="mt-2.5 pt-2 border-t border-slate-200/60 flex flex-wrap items-center justify-between gap-2 text-xs">
                                <div>
                                    <span class="text-[10px] text-slate-400 block uppercase font-bold">{{ __('Due Date') }}</span>
                                    <span class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-800' }}">
                                        {{ $dueDate->format('M d, Y') }}
                                    </span>
                                </div>

                                <div>
                                    @if($isOverdue)
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                            <span>{{ __('Overdue by :days days', ['days' => abs($daysDiff)]) }}</span>
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-[#6366F1] border border-indigo-200">
                                            {{ __('Due in :days days', ['days' => $daysDiff]) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <p class="font-semibold text-slate-700 mb-1">{{ __('No books currently borrowed.') }}</p>
                <p class="text-slate-400 mb-3">{{ __('Search our collection and place a hold or visit the library.') }}</p>
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold shadow-xs">
                    <span>{{ __('Find Books') }}</span> &rarr;
                </a>
            </div>
        @endif
    </div>

    <!-- Active Reservations (ការកក់ទុករបស់ខ្ញុំ) -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('My Book Reservations') }}</h2>
                <p class="text-xs text-slate-400">{{ __('Hold requests placed online. Please present your reservation code at the counter.') }}</p>
            </div>
            <a href="{{ route('reservations.index') }}" class="text-xs font-semibold text-[#6366F1] hover:underline">
                {{ __('View All Holds') }} &rarr;
            </a>
        </div>

        @if($myReservations->isNotEmpty())
            <div class="divide-y divide-slate-100">
                @foreach($myReservations as $res)
                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-16 rounded-xl bg-slate-100 overflow-hidden ring-1 ring-slate-200 shrink-0">
                                <img src="{{ $res->book?->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                                     alt="{{ $res->book?->title ?? 'Book' }}" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="font-mono text-xs font-bold text-[#1E3A8A] bg-blue-50 px-2 py-0.5 rounded border border-blue-200">
                                        {{ $res->reservation_code ?? 'RES-' . $res->id }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $res->status === 'Fulfilled' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($res->status === 'Approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                        {{ $res->status === 'Fulfilled' ? __('បានទទួល') : __($res->status) }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-sm">{{ $res->book?->title ?? __('Book Deleted / Unavailable') }}</h3>
                                <p class="text-xs text-slate-500">{{ $res->book?->author ?? '' }}</p>
                                
                                @if($res->status === 'Approved' && $res->pickup_deadline)
                                    <p class="text-xs font-bold text-emerald-600 mt-1 flex items-center gap-1">
                                        <span>⏰ {{ __('Pickup deadline:') }} {{ $res->pickup_deadline->format('M d, Y H:i') }}</span>
                                    </p>
                                @else
                                    <p class="text-[11px] text-slate-400 mt-1">
                                        {{ __('Date Placed:') }} {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Cancel Hold Button -->
                        <div>
                            <form action="{{ route('reservations.cancel', $res) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to cancel this reservation?') }}')">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-500 hover:text-rose-600 hover:border-rose-200 text-xs font-semibold transition-colors">
                                    {{ __('Cancel Hold') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                {{ __('No active reservations at the moment.') }}
            </div>
        @endif
    </div>

</div>
@endsection
