@extends('layouts.app')

@section('title', $isAdmin ? __('Circulation Desk') : __('My Borrowed Books'))

@section('content')
<div class="space-y-6" x-data="{ 
    issueModalOpen: false,
    returnModalOpen: false,
    waiveModalOpen: false,
    selectedBorrow: null,
    waiveBorrow: null,
    waiveReason: '',
    patronQuery: '',
    patronResult: null,
    patronLoading: false,
    patronError: '',
    selectedUserId: '{{ request('user_id') }}',
    selectedBookId: '{{ request('book_id') }}',
    reservationId: '',
    returnCondition: 'Good',
    penaltyFee: 0,
    returnFinePaid: true,
    returnNotes: '',

    openReturnModal(borrow) {
        this.selectedBorrow = borrow;
        this.returnCondition = borrow.book_condition || 'Good';
        this.penaltyFee = (this.returnCondition === 'Lost') ? 15.00 : (this.returnCondition === 'Damaged' ? 5.00 : 0.00);
        this.returnFinePaid = true;
        this.returnNotes = '';
        this.returnModalOpen = true;
    },

    openWaiveModal(borrow) {
        this.waiveBorrow = borrow;
        this.waiveReason = '';
        this.waiveModalOpen = true;
    },

    async checkPatron() {
        if (!this.patronQuery.trim()) return;
        this.patronLoading = true;
        this.patronError = '';
        this.patronResult = null;

        try {
            const res = await fetch('/borrows/check-patron?query=' + encodeURIComponent(this.patronQuery.trim()), {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (res.ok && data.success) {
                this.patronResult = data.patron;
                this.selectedUserId = data.patron.id;
                if (data.reservation) {
                    this.reservationId = data.reservation.id;
                    this.selectedBookId = data.reservation.book_id;
                }
            } else {
                this.patronError = data.message || '{{ __('Patron not found.') }}';
            }
        } catch (e) {
            this.patronError = '{{ __('Error verifying patron policy.') }}';
        } finally {
            this.patronLoading = false;
        }
    }
}">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">
                {{ $isAdmin ? __('Circulation Management') : __('My Borrowed Books & History') }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $isAdmin ? __('Pre-flight policy check, book issuing desk, returns inspection, and fine settlements.') : __('View active loans, due dates, returned items, and late payment statuses.') }}
            </p>
        </div>

        @if($isAdmin)
            <div class="flex items-center gap-2.5">
                <button @click="issueModalOpen = true" 
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-950/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>{{ __('Issue Book Loan') }}</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Quick Stat Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center font-bold text-sm">
                📚
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium block">{{ __('Total Loans') }}</span>
                <span class="text-lg font-black text-slate-800">{{ $stats['total'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-[#6366F1] flex items-center justify-center font-bold text-sm">
                📖
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium block">{{ __('Currently Borrowed') }}</span>
                <span class="text-lg font-black text-indigo-600">{{ $stats['borrowed'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm">
                ⚠️
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium block">{{ __('Overdue Loans') }}</span>
                <span class="text-lg font-black text-rose-600">{{ $stats['overdue'] }}</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
                ✅
            </div>
            <div>
                <span class="text-xs text-slate-400 font-medium block">{{ __('Returned') }}</span>
                <span class="text-lg font-black text-emerald-600">{{ $stats['returned'] }}</span>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('borrows.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $isAdmin ? __('Search patron or book title...') : __('Search book title or ISBN...') }}" class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#6366F1] outline-none">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="Borrowed" {{ request('status') === 'Borrowed' ? 'selected' : '' }}>{{ __('Borrowed') }}</option>
                <option value="Overdue" {{ request('status') === 'Overdue' ? 'selected' : '' }}>{{ __('Overdue') }}</option>
                <option value="Returned" {{ request('status') === 'Returned' ? 'selected' : '' }}>{{ __('Returned') }}</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-[#1E3A8A] text-white text-xs font-semibold rounded-xl hover:bg-blue-900 transition-colors">{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'status', 'book_id', 'user_id']))
                <a href="{{ route('borrows.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">{{ __('Clear') }}</a>
            @endif
        </form>

        <span class="text-xs text-slate-400">{{ __('Showing') }} {{ $borrows->count() }} {{ __('of') }} {{ $borrows->total() }} {{ __('records') }}</span>
    </div>

    <!-- Circulation Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        @if($isAdmin)
                            <th class="py-3.5 px-6">{{ __('Patron / Member') }}</th>
                        @endif
                        <th class="py-3.5 px-6">{{ __('Book Details') }}</th>
                        <th class="py-3.5 px-6">{{ __('Issue Date') }}</th>
                        <th class="py-3.5 px-6">{{ __('Due Date') }}</th>
                        <th class="py-3.5 px-6">{{ __('Status') }}</th>
                        <th class="py-3.5 px-6">{{ __('Late Fine') }}</th>
                        <th class="py-3.5 px-6 text-right">{{ __('Desk Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($borrows as $borrow)
                        @php
                            $isOverdue = $borrow->status === 'Overdue' || ($borrow->status === 'Borrowed' && \Carbon\Carbon::today()->greaterThan(\Carbon\Carbon::parse($borrow->due_date)->startOfDay()));
                            $daysLate = $isOverdue ? (int) abs(\Carbon\Carbon::parse($borrow->due_date)->startOfDay()->diffInDays(\Carbon\Carbon::today())) : 0;
                            $estimatedFine = $isOverdue ? ($daysLate * 0.50) : $borrow->fine_amount;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            @if($isAdmin)
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $borrow->user?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80' }}" 
                                             class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200">
                                        <div>
                                            <p class="font-bold text-slate-800 text-xs sm:text-sm">{{ $borrow->user?->name ?? __('Unknown Patron') }}</p>
                                            <span class="text-[11px] font-mono text-slate-400">{{ $borrow->user?->card_id ?? __('No Card') }} &bull; {{ __($borrow->user?->member_type ?? 'Member') }}</span>
                                        </div>
                                    </div>
                                </td>
                            @endif

                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $borrow->book?->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100&auto=format&fit=crop&q=80' }}" 
                                         class="w-8 h-10 rounded-lg object-cover shadow-xs">
                                    <div>
                                        @if($borrow->book)
                                            <a href="{{ route('books.show', $borrow->book) }}" class="font-semibold text-slate-800 hover:text-[#1E3A8A] text-xs sm:text-sm block line-clamp-1">
                                                {{ $borrow->book->title }}
                                            </a>
                                            <span class="text-[11px] text-slate-400 font-mono">📍 {{ $borrow->book->location_shelf }} &bull; ISBN: {{ $borrow->book->isbn ?? 'N/A' }}</span>
                                        @else
                                            <span class="font-semibold text-slate-500 text-xs sm:text-sm block">
                                                {{ __('Book Deleted / Unavailable') }}
                                            </span>
                                            <span class="text-[11px] text-slate-400 font-mono">ID #{{ $borrow->book_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-6 text-xs text-slate-600 font-mono">
                                {{ \Carbon\Carbon::parse($borrow->borrow_date)->format('M d, Y') }}
                            </td>

                            <td class="py-3.5 px-6 text-xs font-mono">
                                <span class="{{ $isOverdue ? 'text-rose-600 font-bold' : 'text-slate-700' }}">
                                    {{ \Carbon\Carbon::parse($borrow->due_date)->format('M d, Y') }}
                                </span>
                                @if($isOverdue && $borrow->status !== 'Returned')
                                    <span class="block text-[10px] text-rose-500 font-bold">({{ $daysLate }} {{ __('days late') }})</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($borrow->status === 'Returned')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] shrink-0"></span>
                                        <span>{{ __('Returned') }}</span>
                                    </span>
                                @elseif($borrow->status === 'Lost')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 border border-rose-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-600 shrink-0"></span>
                                        <span>🔴 {{ __('Lost (បាត់សៀវភៅ)') }}</span>
                                    </span>
                                @elseif($isOverdue)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-[#EF4444] border border-rose-200/50 animate-pulse whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444] shrink-0"></span>
                                        <span>{{ __('Overdue') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#3B82F6] border border-blue-200/50 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#3B82F6] shrink-0"></span>
                                        <span>{{ __('On Loan') }}</span>
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-6 text-xs font-mono">
                                @if($borrow->fine_waived)
                                    <div>
                                        <span class="inline-flex items-center gap-1 text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md text-[10px] font-bold border border-emerald-200/60">
                                            🎁 {{ __('Waived') }}
                                        </span>
                                        <p class="text-[10px] text-slate-400 mt-0.5 max-w-[120px] truncate" title="{{ $borrow->fine_waived_reason }}">
                                            {{ $borrow->fine_waived_reason }}
                                        </p>
                                    </div>
                                @elseif($estimatedFine > 0)
                                    <div>
                                        <span class="text-rose-600 font-bold">${{ number_format($estimatedFine, 2) }}</span>
                                        <span class="text-[10px] text-slate-500 block font-sans font-medium">{{ $borrow->fine_type_label }}</span>
                                        @if($borrow->fine_paid)
                                            <span class="text-[10px] text-emerald-600 font-semibold block font-sans">({{ __('Settled') }})</span>
                                        @else
                                            <span class="text-[10px] text-rose-500 block font-sans font-bold">({{ __('Unpaid') }} &bull; ៛{{ number_format($estimatedFine * 4000) }})</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 font-bold">$0.00</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-6 text-right">
                                @if($isAdmin)
                                    <div class="flex items-center justify-end gap-1.5 ml-auto">
                                        @if($borrow->status !== 'Returned' && $borrow->status !== 'Lost')
                                            <button type="button" 
                                                    @click="openReturnModal({{ json_encode($borrow) }})" 
                                                    class="px-3 py-1.5 text-xs font-semibold text-white bg-[#10B981] hover:bg-emerald-600 rounded-xl shadow-xs transition-colors flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                <span>{{ __('Return') }}</span>
                                            </button>
                                        @elseif($borrow->status === 'Lost')
                                            <span class="text-xs text-rose-600 font-semibold bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200/60">{{ __('Lost Item') }}</span>
                                        @else
                                            <span class="text-xs text-slate-400 font-medium">{{ __('Completed') }}</span>
                                        @endif

                                        @if($borrow->hasUnpaidFine())
                                            <form action="{{ route('borrows.settle-fine', $borrow) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to settle the fine of $:amount for :patron?', ['amount' => number_format($borrow->fine_amount, 2), 'patron' => $borrow->user?->name]) }}');">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-2.5 py-1.5 text-xs font-semibold text-white bg-[#1E3A8A] hover:bg-blue-900 rounded-xl shadow-xs transition-colors flex items-center gap-1"
                                                        title="{{ __('Settle Fine (ទូទាត់ប្រាក់ពិន័យ)') }}">
                                                    <span>💳</span>
                                                    <span>{{ __('Settle') }}</span>
                                                </button>
                                            </form>
                                        @endif

                                        @if(($isOverdue || $borrow->fine_amount > 0) && !$borrow->fine_waived && !$borrow->fine_paid && $layoutUser?->canWaiveFines())
                                            <button type="button" 
                                                    @click="openWaiveModal({{ json_encode($borrow) }})" 
                                                    class="px-2.5 py-1.5 text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-xl shadow-2xs transition-colors flex items-center gap-1"
                                                    title="{{ __('Waive Fine (Manager Decision)') }}">
                                                <span>🎁</span>
                                                <span class="hidden sm:inline">{{ __('Waive') }}</span>
                                            </button>
                                        @endif
                                    </div>
                                @else
                                    <!-- Member self-view -->
                                    @if($borrow->status === 'Lost')
                                        <span class="text-xs text-rose-600 font-semibold bg-rose-50 px-2.5 py-1 rounded-lg">
                                            🔴 {{ __('Lost Item') }}
                                        </span>
                                    @elseif($borrow->status !== 'Returned')
                                        <span class="text-xs text-indigo-600 font-semibold bg-indigo-50 px-2.5 py-1 rounded-lg">
                                            {{ __('Active Loan') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2.5 py-1 rounded-lg">
                                            {{ __('Returned on') }} {{ \Carbon\Carbon::parse($borrow->return_date)->format('M d') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center py-12 text-slate-400 text-sm">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                {{ __('No circulation loan records found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $borrows->links() }}
    </div>

    @if($isAdmin)
        <!-- MODAL 1: ISSUE BOOK LOAN WITH PRE-FLIGHT VERIFICATION -->
        <div x-show="issueModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
            <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[92vh] overflow-y-auto" @click.away="issueModalOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ __('Issue Book Loan (Circulation Desk)') }}</h3>
                        <p class="text-xs text-slate-500">{{ __('Verify patron borrowing policy, scan hold codes, and issue copies.') }}</p>
                    </div>
                    <button @click="issueModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">&times;</button>
                </div>

                <!-- Patron Quick Search & Pre-Flight Verification Box -->
                <div class="mt-4 p-4 rounded-2xl bg-blue-50/70 border border-blue-100">
                    <label class="block text-xs font-bold text-[#1E3A8A] uppercase mb-1.5">
                        🔍 {{ __('Pre-Flight Policy Check (Scan Member Card or Hold Code)') }}
                    </label>
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="patronQuery" 
                               @keydown.enter.prevent="checkPatron()"
                               placeholder="{{ __('Enter Card ID (e.g. CARD-1001) or Hold Code (e.g. RES-XXXXXX)...') }}" 
                               class="flex-1 px-3.5 py-2 text-xs bg-white border border-blue-200 rounded-xl outline-none focus:ring-2 focus:ring-blue-300 font-mono">
                        <button type="button" 
                                @click="checkPatron()"
                                :disabled="patronLoading"
                                class="px-4 py-2 bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors shrink-0">
                            <span x-show="!patronLoading">{{ __('Verify') }}</span>
                            <span x-show="patronLoading" x-cloak>{{ __('Checking...') }}</span>
                        </button>
                    </div>

                    <!-- Pre-flight Status Feedback -->
                    <template x-if="patronError">
                        <p class="mt-2 text-xs text-rose-600 font-medium" x-text="patronError"></p>
                    </template>

                    <template x-if="patronResult">
                        <div class="mt-3 p-3 bg-white rounded-xl border border-blue-100 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800" x-text="patronResult.name + ' (' + patronResult.card_id + ')'"></span>
                                <span :class="patronResult.allowed ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'" class="px-2 py-0.5 rounded-md font-bold text-[10px]" x-text="patronResult.allowed ? '{{ __('ELIGIBLE TO BORROW') }}' : '{{ __('BORROWING BLOCKED') }}'"></span>
                            </div>
                            <div class="grid grid-cols-3 gap-2 pt-1.5 border-t border-slate-100 text-slate-500 text-[11px]">
                                <div>{{ __('Active Loans') }}: <strong class="text-slate-800" x-text="patronResult.active_count + ' / ' + patronResult.max_limit"></strong></div>
                                <div>{{ __('Overdue') }}: <strong :class="patronResult.has_overdue ? 'text-rose-600' : 'text-slate-800'" x-text="patronResult.has_overdue ? '{{ __('Yes') }}' : '{{ __('None') }}'"></strong></div>
                                <div>{{ __('Unpaid Fines') }}: <strong :class="patronResult.unpaid_fines > 0 ? 'text-rose-600' : 'text-slate-800'" x-text="'$' + patronResult.unpaid_fines.toFixed(2)"></strong></div>
                            </div>
                            <template x-if="!patronResult.allowed">
                                <p class="text-rose-600 font-semibold pt-1 border-t border-rose-100" x-text="patronResult.reason"></p>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Loan Issue Form -->
                <form action="{{ route('borrows.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="reservation_id" x-model="reservationId">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Select Member') }} *</label>
                        <select name="user_id" x-model="selectedUserId" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="">{{ __('-- Choose Member --') }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->card_id }}) - {{ __($user->member_type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Select Available Book') }} *</label>
                        <select name="book_id" x-model="selectedBookId" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="">{{ __('-- Choose Book --') }}</option>
                            @foreach($books as $b)
                                <option value="{{ $b->id }}">{{ $b->title }} ({{ $b->available_copies }} {{ __('Available') }}) - [{{ $b->location_shelf }}]</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Borrow Date') }} *</label>
                            <input type="date" name="borrow_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Due Date (14 Days)') }} *</label>
                            <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Notes / Desk Remarks') }}</label>
                        <textarea name="notes" rows="2" placeholder="{{ __('Issued at circulation desk; special approval or reference course...') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" @click="issueModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">{{ __('Issue Book Now') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: RETURN & FINE SETTLEMENT MODAL -->
        <div x-show="returnModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative" @click.away="returnModalOpen = false">
                <template x-if="selectedBorrow">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ __('Process Book Return') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('Inspect book condition, calculate overdue fees, and settle patron account.') }}</p>
                            </div>
                            <button @click="returnModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">&times;</button>
                        </div>

                        <!-- Loan Summary Info -->
                        <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Patron') }}:</span>
                                <strong class="text-slate-800" x-text="selectedBorrow.user ? selectedBorrow.user.name : ''"></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Book') }}:</span>
                                <strong class="text-slate-800" x-text="selectedBorrow.book ? selectedBorrow.book.title : ''"></strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">{{ __('Due Date') }}:</span>
                                <span class="font-mono text-slate-700" x-text="selectedBorrow.due_date ? selectedBorrow.due_date.substring(0, 10) : ''"></span>
                            </div>
                        </div>

                        <form :action="'/borrows/' + selectedBorrow.id + '/return'" method="POST" class="mt-5 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Book Condition Inspection') }} *</label>
                                <select name="book_condition" 
                                        x-model="returnCondition" 
                                        @change="if(returnCondition === 'Lost') penaltyFee = 15.00; else if(returnCondition === 'Damaged') penaltyFee = 5.00; else penaltyFee = 0.00;"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                                    <option value="Good">🟢 {{ __('Good (Normal wear, ready for reshelving)') }}</option>
                                    <option value="Fair">🟡 {{ __('Fair (Minor marks or creases)') }}</option>
                                    <option value="Damaged">🟠 {{ __('Damaged (Torn pages, water damage - requires repair fee)') }}</option>
                                    <option value="Lost">🔴 {{ __('Lost (បាត់បង់សៀវភៅ - គិតថ្លៃសងសៀវភៅថ្មី)') }}</option>
                                </select>
                            </div>

                            <!-- Penalty Fee Input for Lost or Damaged -->
                            <div x-show="returnCondition === 'Lost' || returnCondition === 'Damaged'" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-xs space-y-2" x-cloak>
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-rose-900" x-text="returnCondition === 'Lost' ? '{{ __('Lost Book Replacement Fee (ថ្លៃសងសៀវភៅបាត់)') }}' : '{{ __('Damage Repair Fee (ថ្លៃជួសជុលសៀវភៅខូច)') }}'"></span>
                                    <div class="flex items-center gap-1">
                                        <span class="text-rose-700 font-bold">$</span>
                                        <input type="number" step="0.50" min="0" name="penalty_fee" x-model="penaltyFee" class="w-20 px-2 py-1 rounded-lg border border-rose-300 text-right font-bold text-rose-900 text-xs bg-white">
                                    </div>
                                </div>
                                <p class="text-[11px] text-rose-700" x-show="returnCondition === 'Lost'">
                                    ⚠️ {{ __('សៀវភៅនឹងត្រូវកាត់ចេញពីបញ្ជីស្តុក (total copies ត្រូវកាត់បន្ថយ 1)។ សមាជិកដែលជំពាក់ប្រាក់ពិន័យមិនអាចខ្ចីសៀវភៅថ្មីបានទេ។') }}
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-amber-900">{{ __('Late Fee ($0.50/day)') }}:</span>
                                    <span class="font-bold text-amber-900 font-mono text-sm" x-text="'$' + (selectedBorrow.fine_amount ? parseFloat(selectedBorrow.fine_amount).toFixed(2) : '0.00')"></span>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-amber-200/60" x-show="parseFloat(penaltyFee) > 0">
                                    <span class="font-semibold text-amber-900">{{ __('Total Fee Payable') }}:</span>
                                    <span class="font-bold text-rose-600 font-mono text-sm" x-text="'$' + ((parseFloat(selectedBorrow.fine_amount || 0) + parseFloat(penaltyFee || 0)).toFixed(2))"></span>
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer pt-1 border-t border-amber-200/60">
                                    <input type="checkbox" name="fine_paid" value="1" x-model="returnFinePaid" class="rounded text-emerald-600 focus:ring-emerald-200">
                                    <span class="text-amber-900 font-medium">{{ __('Confirm Fine Settle / Paid in Cash (ទូទាត់ប្រាក់ពិន័យភ្លាមៗ)') }}</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Inspection & Return Notes') }}</label>
                                <textarea name="return_notes" x-model="returnNotes" rows="2" placeholder="{{ __('Inspected at front desk, returned clean condition...') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                <button type="button" @click="returnModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                                <button type="submit" 
                                        :class="returnCondition === 'Lost' ? 'bg-rose-600 hover:bg-rose-700' : 'bg-[#10B981] hover:bg-emerald-600'"
                                        class="px-5 py-2.5 rounded-xl text-white text-xs font-semibold shadow-md"
                                        x-text="returnCondition === 'Lost' ? '{{ __('Confirm Lost & Assess Fee') }}' : '{{ __('Confirm Return & Restock') }}'"></button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        <!-- MODAL 3: WAIVE FINE DECISION (Manager Exclusive) -->
        @if($layoutUser?->canWaiveFines())
            <div x-show="waiveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative" @click.away="waiveModalOpen = false">
                    <template x-if="waiveBorrow">
                        <div>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-lg">
                                        🎁
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-slate-900">{{ __('Waive Overdue Fine') }}</h3>
                                        <p class="text-xs text-slate-500">{{ __('Manager decision for special exemptions with mandatory logged reason.') }}</p>
                                    </div>
                                </div>
                                <button @click="waiveModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">&times;</button>
                            </div>

                            <!-- Summary Card -->
                            <div class="mt-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/70 text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">{{ __('Patron') }}:</span>
                                    <strong class="text-slate-800" x-text="waiveBorrow.user ? waiveBorrow.user.name + ' (' + (waiveBorrow.user.card_id || 'No Card') + ')' : ''"></strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-600">{{ __('Book') }}:</span>
                                    <strong class="text-slate-800 max-w-[240px] truncate" x-text="waiveBorrow.book ? waiveBorrow.book.title : ''"></strong>
                                </div>
                                <div class="flex items-center justify-between pt-1 border-t border-amber-200/60">
                                    <span class="text-slate-600">{{ __('Overdue Fine Amount') }}:</span>
                                    <span class="font-mono font-black text-rose-600 text-sm" x-text="'$' + (waiveBorrow.fine_amount ? parseFloat(waiveBorrow.fine_amount).toFixed(2) : '0.00') + ' (៛' + Math.round((waiveBorrow.fine_amount || 0) * 4000).toLocaleString() + ')'"></span>
                                </div>
                            </div>

                            <form :action="'/borrows/' + waiveBorrow.id + '/waive-fine'" method="POST" class="mt-5 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                        {{ __('Reason for Waiving Fine') }} *
                                    </label>
                                    <input type="text" 
                                           name="reason" 
                                           x-model="waiveReason" 
                                           required 
                                           placeholder="{{ __('e.g., Medical certificate provided, family emergency, academic exception...') }}" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-amber-200 outline-none bg-white">

                                    <!-- Quick Reason Chips -->
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <button type="button" 
                                                @click="waiveReason = '{{ __('Medical emergency with official doctor certificate') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            🏥 {{ __('Medical Emergency') }}
                                        </button>
                                        <button type="button" 
                                                @click="waiveReason = '{{ __('Official university administrative delay or system error') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            🏛️ {{ __('Administrative / System Delay') }}
                                        </button>
                                        <button type="button" 
                                                @click="waiveReason = '{{ __('Academic thesis research deadline extension approved by Dean') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            🎓 {{ __('Academic Thesis Extension') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-[11px] text-blue-800 flex items-start gap-2">
                                    <span class="text-sm">ℹ️</span>
                                    <p>{{ __('Waiving this fine will immediately unblock the patron account and will be permanently recorded in the Manager audit trail.') }}</p>
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                                    <button type="button" @click="waiveModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-md flex items-center gap-1.5">
                                        <span>🎁</span>
                                        <span>{{ __('Confirm Fine Exemption') }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
