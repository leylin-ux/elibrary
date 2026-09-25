@extends('layouts.app')

@section('title', $member->name . ' - ' . __('Member Details'))

@section('content')
<div class="space-y-6" x-data="{
    editModalOpen: false,
    deleteModalOpen: false,
    loanModalOpen: false,
    currentMember: {
        id: '{{ $member->id }}',
        name: '{{ addslashes($member->name) }}',
        email: '{{ addslashes($member->email) }}',
        card_id: '{{ $member->card_id }}',
        member_type: '{{ $member->member_type }}',
        academic_year: '{{ $member->academic_year ?? 1 }}',
        major: '{{ addslashes($member->major ?? '') }}',
        status: '{{ $member->status }}',
        phone: '{{ $member->phone }}',
        photo: '{{ $member->photo }}',
        notes: '{{ addslashes(str_replace(["\r", "\n"], [' ', ' '], $member->notes ?? '')) }}'
    }
}">

    <!-- Top Header & Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('members.index') }}" 
               class="p-2 bg-white rounded-xl border border-slate-200 text-slate-600 hover:text-[#1E3A8A] hover:border-blue-200 transition-colors shadow-xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-0.5">
                    <a href="{{ route('members.index') }}" class="hover:text-[#1E3A8A] transition-colors">{{ __('Members') }}</a>
                    <span>/</span>
                    <span class="text-slate-400 font-mono">{{ $member->card_id ?? 'No Card' }}</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-[#1E3A8A] tracking-tight">{{ $member->name }}</h1>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Edit Profile Button -->
            <button @click="editModalOpen = true" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-xs">
                <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                <span>{{ __('Edit Member') }}</span>
            </button>

            <!-- Issue Book Loan Button -->
            <a href="{{ route('borrows.index', ['user_id' => $member->id]) }}" 
               class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold rounded-xl transition-all shadow-md shadow-blue-950/20">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>{{ __('Issue Book') }}</span>
            </a>

            <!-- Toggle Status Button -->
            <form action="{{ route('members.toggle-status', $member) }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="px-3 py-2 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 {{ $member->status === 'Active' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60' : ($member->status === 'Temporary' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60' : 'text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/50') }}"
                        title="{{ __('Click to cycle status') }}">
                    <span class="w-2 h-2 rounded-full {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}"></span>
                    <span>{{ $member->status === 'Active' ? __('Active') : ($member->status === 'Temporary' ? __('Temporary') : __('Suspended')) }}</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Grid (Left 4 cols, Right 8 cols) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Profile Card, Notes & Lifetime Stats -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Member Profile Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6 text-center relative overflow-hidden">
                <div class="absolute top-0 inset-x-0 h-1.5 {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}"></div>

                <div class="relative inline-block mx-auto mt-2">
                    <img src="{{ $member->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=200&auto=format&fit=crop&q=80' }}" 
                         alt="{{ $member->name }}" 
                         class="w-24 h-24 rounded-3xl object-cover ring-4 {{ $member->status === 'Active' ? 'ring-emerald-100' : ($member->status === 'Temporary' ? 'ring-amber-100' : 'ring-red-100') }} shadow-md">
                    
                    <span class="absolute bottom-1 right-1 w-5 h-5 rounded-full ring-2 ring-white flex items-center justify-center {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}">
                        <span class="w-2 h-2 rounded-full bg-white"></span>
                    </span>
                </div>

                <h2 class="text-lg font-bold text-slate-900 mt-3">{{ $member->name }}</h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ $member->email }}</p>

                <!-- Status & Member Type Pills -->
                <div class="flex items-center justify-center gap-2 mt-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-[#6366F1] border border-indigo-100">
                        {{ __($member->member_type) }}
                    </span>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $member->status === 'Active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($member->status === 'Temporary' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-red-50 text-red-700 border border-red-200/50') }}">
                        {{ $member->status === 'Active' ? __('Active') : ($member->status === 'Temporary' ? __('Temporary') : __('Suspended')) }}
                    </span>
                </div>

                <!-- Contact & Card Info -->
                <div class="mt-5 pt-4 border-t border-slate-100 space-y-2.5 text-xs text-left">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Card ID') }}:</span>
                        <span class="font-mono font-bold text-slate-800 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                            {{ $member->card_id ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Phone') }}:</span>
                        <span class="font-medium text-slate-700">{{ $member->phone ?? __('Not provided') }}</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Registered Date') }}:</span>
                        <span class="font-medium text-slate-600">{{ $member->created_at ? $member->created_at->format('M d, Y') : 'N/A' }}</span>
                    </div>

                    @if($member->isStudent())
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                            <span class="text-slate-400">{{ __('Academic Year') }}:</span>
                            <span class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-lg border border-indigo-100 text-[11px]">
                                🎯 {{ $member->academic_year_label }}
                            </span>
                        </div>
                        @if($member->major)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Major / Dept') }}:</span>
                                <span class="font-medium text-slate-700">{{ $member->major }}</span>
                            </div>
                        @endif
                    @elseif($member->isTeacher() && $member->major)
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                            <span class="text-slate-400">{{ __('Department') }}:</span>
                            <span class="font-medium text-slate-700">{{ $member->major }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-slate-400">{{ __('Borrow Quota') }}:</span>
                        <span class="font-bold text-slate-800">{{ $member->maxAllowedLoans() }} {{ __('books max') }}</span>
                    </div>
                </div>
            </div>

            <!-- External Member Subscription Card (for non-student patrons) -->
            <!-- Fines & Borrowing Policy Status Card -->
            <div class="bg-white rounded-3xl border {{ $totalUnpaidFines > 0 ? 'border-rose-200 bg-gradient-to-br from-white to-rose-50/30' : 'border-emerald-200 bg-gradient-to-br from-white to-emerald-50/20' }} shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b {{ $totalUnpaidFines > 0 ? 'border-rose-100' : 'border-emerald-100' }}">
                    <h3 class="font-bold text-slate-800 uppercase tracking-wider text-xs flex items-center gap-2">
                        <span>{{ $totalUnpaidFines > 0 ? '⚠️' : '🛡️' }}</span>
                        <span>{{ __('Fines & Policy Status') }}</span>
                    </h3>
                    @if($totalUnpaidFines > 0)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                            🛑 {{ __('Borrowing Blocked') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                            ✓ {{ __('Eligible to Borrow') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('Unpaid Fines Total') }}:</span>
                        <span class="font-bold font-mono text-sm {{ $totalUnpaidFines > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                            ${{ number_format($totalUnpaidFines, 2) }}
                            @if($totalUnpaidFines > 0)
                                <span class="text-[10px] font-sans block text-right text-rose-500">៛{{ number_format($totalUnpaidFines * 4000) }}</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-slate-500">{{ __('Total Settled Fines') }}:</span>
                        <span class="font-bold font-mono text-slate-700">${{ number_format($totalPaidFines, 2) }}</span>
                    </div>

                    @if($totalWaivedFines > 0)
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">{{ __('Waived Fines') }}:</span>
                            <span class="font-bold font-mono text-amber-600">${{ number_format($totalWaivedFines, 2) }}</span>
                        </div>
                    @endif
                </div>

                @if($totalUnpaidFines > 0)
                    <div class="p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-[11px] text-rose-800 leading-tight">
                        ⛔ {{ __('សមាជិកមានប្រាក់ពិន័យជំពាក់ (សងយឺត ឬបាត់សៀវភៅ)។ ប្រព័ន្ធផ្អាកការខ្ចីសៀវភៅថ្មីដោយស្វ័យប្រវត្ត។') }}
                    </div>
                @else
                    <div class="p-2 rounded-xl bg-emerald-50 text-[11px] text-emerald-800">
                        ✓ {{ __('គណនីស្អាតស្អំ គ្មានប្រាក់ពិន័យជំពាក់។ អាចខ្ចីសៀវភៅបានតាមកូតាកំណត់។') }}
                    </div>
                @endif
            </div>

            <!-- Member Notes Card (ព័ត៌មានលម្អិតសមាជិក៖ Note) -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-5 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 uppercase tracking-wider text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <span>{{ __('Member Notes') }}</span>
                    </h3>
                    <button @click="editModalOpen = true" class="text-[11px] text-[#6366F1] hover:underline font-semibold">
                        {{ __('Edit') }}
                    </button>
                </div>

                @if(!empty($member->notes))
                    <div class="p-3.5 rounded-2xl bg-amber-50/50 border border-amber-100 text-xs text-slate-700 leading-relaxed italic">
                        "{{ $member->notes }}"
                    </div>
                @else
                    <div class="py-4 text-center text-xs text-slate-400 italic">
                        {{ __('No administrative notes recorded for this member.') }}
                    </div>
                @endif
            </div>

            <!-- Borrowing Statistics Summary Card -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-5 space-y-3">
                <h3 class="font-bold text-slate-800 uppercase tracking-wider text-xs pb-2 border-b border-slate-100">
                    {{ __('Borrowing Statistics') }}
                </h3>

                <div class="grid grid-cols-3 gap-2 text-center">
                    <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100">
                        <span class="block text-[10px] uppercase font-bold text-blue-600 tracking-wider">{{ __('Total Borrowed') }}</span>
                        <span class="text-xl font-black text-[#1E3A8A] mt-0.5 block">{{ $member->total_borrows_count }}</span>
                        <span class="text-[10px] text-slate-400">{{ __('times') }}</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <span class="block text-[10px] uppercase font-bold text-indigo-600 tracking-wider">{{ __('Active Borrows') }}</span>
                        <span class="text-xl font-black text-[#6366F1] mt-0.5 block">{{ $member->active_borrows_count }}</span>
                        <span class="text-[10px] text-slate-400">{{ __('books') }}</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <span class="block text-[10px] uppercase font-bold text-emerald-600 tracking-wider">{{ __('Completed Returns') }}</span>
                        <span class="text-xl font-black text-emerald-700 mt-0.5 block">{{ $member->completedBorrows->count() }}</span>
                        <span class="text-[10px] text-slate-400">{{ __('books') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Active Loans Report & Past Circulation History -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 1. ACTIVE BORROWED BOOKS REPORT (របាយការណ៍សៀវភៅដែលកំពុងខ្ចី) -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-50 text-[#6366F1] flex items-center justify-center font-bold text-xs">
                            {{ $member->active_borrows_count }}
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('Active Borrowed Books Report') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Currently unreturned books with loan dates, due dates, and fine tracking.') }}</p>
                        </div>
                    </div>

                    <a href="{{ route('borrows.index', ['user_id' => $member->id]) }}" 
                       class="text-xs font-semibold text-[#1E3A8A] hover:underline flex items-center gap-1">
                        <span>+ {{ __('Issue Book') }}</span>
                    </a>
                </div>

                @if($member->activeBorrows->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($member->activeBorrows as $loan)
                            @php
                                $dueDate = \Carbon\Carbon::parse($loan->due_date);
                                $isOverdue = $loan->status === 'Overdue' || \Carbon\Carbon::today()->gt($dueDate);
                                $daysDiff = \Carbon\Carbon::today()->diffInDays($dueDate, false);
                            @endphp
                            <div class="p-4 rounded-2xl border {{ $isOverdue ? 'bg-rose-50/30 border-rose-200' : 'bg-slate-50/60 border-slate-100' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <!-- Book Info -->
                                <div class="flex items-center gap-3.5">
                                    <img src="{{ $loan->book?->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                                         alt="{{ $loan->book?->title ?? 'Book' }}" 
                                         class="w-12 h-16 rounded-xl object-cover ring-1 ring-slate-200 shadow-xs shrink-0">
                                    <div>
                                        @if($loan->book)
                                            <a href="{{ route('books.show', $loan->book) }}" class="font-bold text-slate-800 text-sm hover:text-[#1E3A8A] transition-colors line-clamp-1">
                                                {{ $loan->book->title }}
                                            </a>
                                            <p class="text-xs text-slate-500 mt-0.5">{{ $loan->book->author }}</p>
                                            <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-400 font-mono">
                                                <span>📍 {{ $loan->book->location_shelf }}</span>
                                                <span>&bull;</span>
                                                <span>ISBN: {{ $loan->book->isbn ?? 'N/A' }}</span>
                                            </div>
                                        @else
                                            <p class="font-bold text-slate-500 text-sm">{{ __('Book Deleted / Unavailable') }}</p>
                                            <span class="text-[11px] text-slate-400 font-mono">ID #{{ $loan->book_id }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Loan Schedule & Return Action -->
                                <div class="flex flex-wrap sm:flex-nowrap items-center gap-4 text-xs">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Loan Date') }}</span>
                                        <span class="font-semibold text-slate-700">{{ \Carbon\Carbon::parse($loan->borrow_date)->format('M d, Y') }}</span>
                                    </div>

                                    <div>
                                        <span class="text-slate-400 block text-[10px] uppercase font-bold">{{ __('Due Date') }}</span>
                                        <span class="font-bold {{ $isOverdue ? 'text-rose-600' : 'text-slate-800' }}">
                                            {{ $dueDate->format('M d, Y') }}
                                        </span>
                                    </div>

                                    <!-- Status Badge -->
                                    <div>
                                        @if($isOverdue)
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-rose-100 text-rose-700 border border-rose-200 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600 animate-pulse"></span>
                                                <span>{{ __('Overdue') }} ({{ abs($daysDiff) }}d)</span>
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-indigo-50 text-[#6366F1] border border-indigo-200">
                                                {{ __('Borrowed') }} ({{ $daysDiff }}d left)
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Quick Return Form -->
                                    <form action="{{ route('borrows.return', $loan->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="px-3.5 py-2 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white font-semibold text-xs transition-colors shadow-xs">
                                            {{ __('Return Book') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        {{ __('No active loans currently.') }}
                    </div>
                @endif
            </div>

            <!-- 2. PAST BORROWING & RETURN HISTORY (ប្រវត្តិនៃការខ្ចីសងសៀវភៅពីមុន) -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('Past Borrowing & Return History') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Complete historical circulation records and return timestamps for this member.') }}</p>
                        </div>
                    </div>
                </div>

                @if($member->completedBorrows->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                                    <th class="py-3 px-4">{{ __('Book Title') }}</th>
                                    <th class="py-3 px-4">{{ __('Loan Date') }}</th>
                                    <th class="py-3 px-4">{{ __('Return Date') }}</th>
                                    <th class="py-3 px-4">{{ __('Fine ($)') }}</th>
                                    <th class="py-3 px-4">{{ __('Status') }}</th>
                                    <th class="py-3 px-4">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($member->completedBorrows as $past)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-4">
                                            @if($past->book)
                                                <a href="{{ route('books.show', $past->book) }}" class="font-bold text-slate-800 hover:text-[#1E3A8A] flex items-center gap-2">
                                                    <span>{{ $past->book->title }}</span>
                                                </a>
                                                <span class="text-[11px] text-slate-400 font-mono">{{ $past->book->author }}</span>
                                            @else
                                                <span class="font-bold text-slate-500">{{ __('Book Deleted / Unavailable') }}</span>
                                                <span class="text-[11px] text-slate-400 font-mono block">ID #{{ $past->book_id }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">{{ \Carbon\Carbon::parse($past->borrow_date)->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 text-emerald-600 font-semibold">{{ \Carbon\Carbon::parse($past->return_date)->format('M d, Y') }}</td>
                                        <td class="py-3 px-4 font-mono font-bold {{ $past->fine_amount > 0 ? 'text-rose-600' : 'text-slate-500' }}">
                                            ${{ number_format($past->fine_amount, 2) }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                {{ __('Returned') }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 italic max-w-xs truncate">
                                            {{ $past->notes ?: '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        {{ __('No past completed loans.') }}
                    </div>
                @endif
            </div>

            <!-- 3. RESERVATIONS BY THIS MEMBER -->
            @if($member->reservations->isNotEmpty())
                <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ __('Book Reservations') }}</span>
                    </h3>

                    <div class="divide-y divide-slate-100">
                        @foreach($member->reservations as $res)
                            <div class="py-3 flex items-center justify-between text-xs">
                                <div>
                                    <p class="font-bold text-slate-800">{{ $res->book?->title ?? __('Book Deleted / Unavailable') }}</p>
                                    <p class="text-slate-400">{{ __('Date Placed') }}: {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $res->status === 'Approved' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                        {{ __($res->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 4. FINES & PENALTIES HISTORY (ប្រវត្តិប្រាក់ពិន័យសងយឺត និងសៀវភៅបាត់) -->
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                            💳
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">{{ __('Fines & Penalties History (ប្រវត្តិប្រាក់ពិន័យ និងថ្លៃសងសៀវភៅ)') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Late return fines ($0.50/day), lost book replacement fees, and fine settlement receipts.') }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($totalUnpaidFines > 0)
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                🛑 {{ __('Unpaid') }}: ${{ number_format($totalUnpaidFines, 2) }}
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                ✓ {{ __('All Settled') }}
                            </span>
                        @endif
                    </div>
                </div>

                @if($finesHistory->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="text-slate-400 font-bold uppercase border-b border-slate-100">
                                    <th class="py-3 px-4">{{ __('Book Title') }}</th>
                                    <th class="py-3 px-4">{{ __('Penalty Type') }}</th>
                                    <th class="py-3 px-4">{{ __('Amount') }}</th>
                                    <th class="py-3 px-4">{{ __('Status') }}</th>
                                    <th class="py-3 px-4">{{ __('Dates') }}</th>
                                    <th class="py-3 px-4 text-right">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($finesHistory as $fine)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3 px-4 font-bold text-slate-800">
                                            @if($fine->book)
                                                <a href="{{ route('books.show', $fine->book) }}" class="hover:text-[#1E3A8A] flex items-center gap-1.5">
                                                    <span>📖</span>
                                                    <span class="line-clamp-1">{{ $fine->book->title }}</span>
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic">{{ __('Book Record Removed') }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="px-2 py-0.5 rounded-md text-[11px] font-bold {{ $fine->isLost() ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($fine->isDamaged() ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                                {{ $fine->fine_type_label }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold {{ $fine->hasUnpaidFine() ? 'text-rose-600' : 'text-slate-700' }}">
                                            ${{ number_format($fine->fine_amount, 2) }}
                                            <span class="block text-[10px] text-slate-400 font-sans">៛{{ number_format($fine->fine_amount * 4000) }}</span>
                                        </td>
                                        <td class="py-3 px-4">
                                            @if($fine->fine_waived)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="{{ $fine->fine_waived_reason }}">
                                                    🎁 {{ __('Waived') }}
                                                </span>
                                            @elseif($fine->fine_paid)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    ✓ {{ __('Paid / Settled') }}
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 animate-pulse">
                                                    🛑 {{ __('Unpaid') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            <div>{{ __('Due') }}: {{ \Carbon\Carbon::parse($fine->due_date)->format('M d, Y') }}</div>
                                            @if($fine->return_date)
                                                <div class="text-[10px] text-slate-400">{{ __('Returned') }}: {{ \Carbon\Carbon::parse($fine->return_date)->format('M d, Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if($fine->hasUnpaidFine())
                                                <form action="{{ route('borrows.settle-fine', $fine) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to settle this fine of $:amount?', ['amount' => number_format($fine->fine_amount, 2)]) }}');">
                                                    @csrf
                                                    <button type="submit" class="px-3 py-1 bg-[#1E3A8A] hover:bg-blue-900 text-white rounded-lg font-semibold text-xs shadow-xs transition-colors">
                                                        💳 {{ __('Settle Fine') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="py-8 text-center bg-slate-50/60 rounded-2xl border border-dashed border-slate-200 text-slate-400 text-xs">
                        {{ __('No overdue fines or lost book replacement charges recorded for this member.') }}
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- MODAL: EDIT MEMBER PROFILE & NOTES -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Edit Member Profile') }}</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            <form action="{{ route('members.update', $member) }}" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="redirect_to" value="show">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required x-model="currentMember.name" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Email Address') }} *</label>
                        <input type="email" name="email" required x-model="currentMember.email" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Card ID / Student ID') }} *</label>
                        <input type="text" name="card_id" required x-model="currentMember.card_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Member Type') }} *</label>
                        <select name="member_type" x-model="currentMember.member_type" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="Student">{{ __('Student') }}</option>
                            <option value="Teacher">{{ __('Teacher') }}</option>
                            <option value="General">{{ __('General') }}</option>
                            <option value="Staff">{{ __('Staff') }}</option>
                        </select>
                    </div>

                    <!-- Conditional Student: Academic Year & Major -->
                    <div x-show="currentMember.member_type === 'Student'" class="sm:col-span-2 p-3.5 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
                        <span class="text-xs font-bold text-indigo-900 block">🎯 {{ __('Student Academic Information') }}</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Academic Year (ឆ្នាំសិក្សា)') }}</label>
                                <select name="academic_year" x-model="currentMember.academic_year" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                    <option value="1">ឆ្នាំទី ១ (Year 1)</option>
                                    <option value="2">ឆ្នាំទី ២ (Year 2)</option>
                                    <option value="3">ឆ្នាំទី ៣ (Year 3)</option>
                                    <option value="4">ឆ្នាំទី ៤ (Year 4)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Major / Department') }}</label>
                                <input type="text" name="major" x-model="currentMember.major" placeholder="{{ __('e.g. Computer Science') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Major -->
                    <div x-show="currentMember.member_type === 'Teacher'" class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Faculty / Department') }}</label>
                        <input type="text" name="major" x-model="currentMember.major" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" x-model="currentMember.phone" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Status') }} *</label>
                        <select name="status" x-model="currentMember.status" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="Active">{{ __('Active') }}</option>
                            <option value="Temporary">{{ __('Temporary') }}</option>
                            <option value="Suspended">{{ __('Suspended') }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Photo URL') }}</label>
                        <input type="url" name="photo" x-model="currentMember.photo" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Member Notes') }}</label>
                        <textarea name="notes" rows="3" x-model="currentMember.notes" placeholder="{{ __('e.g. Thesis student, allowed extra loan duration') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md shadow-blue-950/20">{{ __('Update Member') }}</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
