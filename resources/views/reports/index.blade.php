@extends('layouts.app')

@section('title', __('Library Reports & Analytics'))

@push('styles')
<style>
    @media print {
        aside, nav, header, .no-print, .print-hide, button:not(.print-show), form.search-form {
            display: none !important;
        }
        body, main, .print-container {
            background: white !important;
            padding: 0 !important;
            margin: 0 !important;
            font-size: 11pt;
            color: #000 !important;
        }
        .print-header {
            display: block !important;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #000;
        }
        .tab-content-area {
            display: block !important;
        }
        .table-responsive {
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }
        th, td {
            border: 1px solid #ddd !important;
            padding: 6px 10px !important;
            font-size: 10pt !important;
        }
        th {
            background-color: #f3f4f6 !important;
            color: #000 !important;
        }
        .shadow-xs, .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl {
            box-shadow: none !important;
        }
        .rounded-2xl, .rounded-xl, .rounded-3xl {
            border-radius: 4px !important;
        }
    }
    .print-header {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $activeTab }}',
    exportDropdownOpen: false,
    printReport() {
        window.print();
    }
}">

    <!-- Printable Official Header (Visible ONLY on print) -->
    <div class="print-header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-[#1E3A8A] text-white flex items-center justify-center font-bold text-xl rounded">
                    EL
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-black">{{ config('app.name', 'E-Library') }} - {{ __('Library Reports & Analytics') }}</h1>
                    <p class="text-xs text-gray-600">{{ __('Comprehensive audit reports, borrowing frequency, and inventory analytics.') }}</p>
                </div>
            </div>
            <div class="text-right text-xs text-gray-500">
                <p>{{ __('Date') }}: {{ now()->format('Y-m-d H:i') }}</p>
                <p>{{ __('Librarian') }}: {{ auth()->user()->name ?? 'Administrator' }}</p>
            </div>
        </div>
    </div>

    <!-- Page Title & Top Actions Bar (Screen) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print-hide">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#1E3A8A] to-[#6366F1] text-white flex items-center justify-center shadow-md shadow-indigo-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">{{ __('Library Reports & Analytics') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500">{{ __('Comprehensive audit reports, borrowing frequency, and inventory analytics.') }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center flex-wrap gap-2.5">
            <!-- Print Report Button -->
            <button @click="printReport()" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>{{ __('Print Report') }}</span>
            </button>

            <!-- Export CSV Dropdown -->
            <div class="relative" @click.away="exportDropdownOpen = false">
                <button @click="exportDropdownOpen = !exportDropdownOpen"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold hover:bg-blue-900 shadow-md shadow-blue-900/20 transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    <span>{{ __('Export CSV') }}</span>
                    <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                <div x-show="exportDropdownOpen" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 text-xs"
                     x-cloak>
                    <a href="{{ route('reports.export', ['type' => 'monthly_stats', 'year' => $selectedYear]) }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#6366F1]"></span>
                        {{ __('Export Monthly Stats (CSV)') }}
                    </a>
                    <a href="{{ route('reports.export', 'top_books') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span>
                        {{ __('Export Top Books (CSV)') }}
                    </a>
                    <a href="{{ route('reports.export', 'top_members') }}" class="flex items-center gap-2.5 px-4 py-2 text-slate-700 hover:bg-slate-50 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                        {{ __('Export Top Members (CSV)') }}
                    </a>
                    <a href="{{ route('reports.export', ['type' => 'monthly_fines', 'year' => $selectedYear]) }}" class="flex items-center gap-2.5 px-4 py-2 text-amber-700 hover:bg-amber-50 font-medium">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ __('Export Fines & Waived Fees (CSV)') }}
                    </a>
                    <a href="{{ route('reports.export', ['type' => 'overdue_lost']) }}" class="flex items-center gap-2.5 px-4 py-2 text-red-600 hover:bg-red-50 font-medium">
                        <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                        {{ __('Export Overdue & Lost Audit (CSV)') }}
                    </a>
                    @if($selectedMember)
                        <a href="{{ route('reports.export', ['type' => 'member_history', 'member_id' => $selectedMember->id]) }}" class="flex items-center gap-2.5 px-4 py-2 text-indigo-700 hover:bg-indigo-50 font-semibold border-t border-slate-100 mt-1">
                            <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                            {{ __('Export Member History (CSV)') }} ({{ $selectedMember->name }})
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Executive Quick Stats Bar (5 Key Performance Indicators) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5 sm:gap-4 print-hide">
        <!-- 1. Total Loans -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Loans') }}</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#1E3A8A] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($totalBorrowsCount) }}</span>
                <span class="text-[11px] font-medium text-slate-400">{{ __('loans') }}</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('All-time circulation') }}</p>
        </div>

        <!-- 2. Active Loans -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Loans') }}</span>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-[#6366F1] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-[#6366F1]">{{ number_format($activeBorrowsCount) }}</span>
                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">{{ __('In Hand') }}</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Currently with patrons') }}</p>
        </div>

        <!-- 3. Overdue Items -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-red-100 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-red-500 uppercase tracking-wider">{{ __('Overdue Items') }}</span>
                <div class="w-8 h-8 rounded-lg bg-red-50 text-[#EF4444] flex items-center justify-center animate-pulse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-[#EF4444]">{{ number_format($overdueBorrowsCount) }}</span>
                @if($overdueBorrowsCount > 0)
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-100 text-[#EF4444] animate-pulse">{{ __('Action Required') }}</span>
                @endif
            </div>
            <p class="text-[11px] text-red-500/80 mt-0.5">${{ number_format($totalEstimatedFines, 2) }} {{ __('Accrued fines') }}</p>
        </div>

        <!-- 4. Total Returned -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Returned') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-[#10B981] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-[#10B981]">{{ number_format($returnedBorrowsCount) }}</span>
                <span class="text-[11px] font-medium text-slate-400">{{ $totalBorrowsCount > 0 ? round(($returnedBorrowsCount / $totalBorrowsCount) * 100) : 0 }}%</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Returned successfully') }}</p>
        </div>

        <!-- 5. Active Patrons -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Active Patrons') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-[#F59E0B] flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-2.5 flex items-baseline gap-2">
                <span class="text-2xl sm:text-3xl font-extrabold text-slate-800">{{ number_format($totalMembersCount) }}</span>
                <span class="text-[11px] font-medium text-slate-400">{{ __('Members') }}</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Students & Staff') }}</p>
        </div>
    </div>

    <!-- Modern Tabbed Navigation for the 6 Core Features (Screen Only) -->
    <div class="bg-white rounded-2xl p-1.5 border border-slate-100 shadow-xs flex items-center flex-wrap gap-1 print-hide">
        <!-- Tab 1: Monthly Statistics & Trends -->
        <button @click="activeTab = 'overview'" 
                :class="activeTab === 'overview' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
            <span>1. {{ __('Circulation (Day, Month, Year)') }}</span>
        </button>

        <!-- Tab 2: Top Borrowed Books -->
        <button @click="activeTab = 'top_books'" 
                :class="activeTab === 'top_books' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <span>2. {{ __('Top Books') }}</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeTab === 'top_books' ? 'bg-blue-800 text-white' : 'bg-slate-100 text-slate-600'">{{ $topBooks->count() }}</span>
        </button>

        <!-- Tab 3: Most Active Members -->
        <button @click="activeTab = 'top_members'" 
                :class="activeTab === 'top_members' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            <span>3. {{ __('Top Members') }}</span>
        </button>

        <!-- Tab 4: Fines & Waived Fees Report -->
        <button @click="activeTab = 'fines'" 
                :class="activeTab === 'fines' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-amber-800 hover:bg-amber-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <span class="text-sm">🎁</span>
            <span>4. {{ __('Fines & Waived Fees') }}</span>
        </button>

        <!-- Tab 5: Overdue & Lost Books Audit -->
        <button @click="activeTab = 'overdue'" 
                :class="activeTab === 'overdue' ? 'bg-red-600 text-white shadow-xs' : 'text-slate-600 hover:text-red-700 hover:bg-red-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>5. {{ __('Overdue & Lost Books') }}</span>
            @if($overdueBorrows->count() > 0)
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeTab === 'overdue' ? 'bg-red-800 text-white' : 'bg-red-100 text-red-700'">{{ $overdueBorrows->count() }}</span>
            @endif
        </button>

        <!-- Tab 6: Member History Lookup -->
        <button @click="activeTab = 'member_history'" 
                :class="activeTab === 'member_history' ? 'bg-[#6366F1] text-white shadow-xs' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50'"
                class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <span>6. {{ __('Member History') }}</span>
            @if($selectedMember)
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
            @endif
        </button>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 1: CIRCULATION STATISTICS & TRENDS (ស្ថិតិការខ្ចីប្រចាំថ្ងៃ ខែ ឆ្នាំ) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <!-- Period Selection & Summary Bar -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2 flex-wrap">
                        <span>📊 {{ __('Borrow & Return Comparison') }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-50 text-[#1E3A8A] font-semibold font-mono">
                            @if($circulationPeriod === 'daily')
                                {{ sprintf('%02d', $selectedMonth) }}/{{ $selectedYear }} ({{ __('Day') }})
                            @elseif($circulationPeriod === 'yearly')
                                {{ __('Year') }} ({{ __('All Years') }})
                            @else
                                {{ $selectedYear }} ({{ __('Month') }})
                            @endif
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($circulationPeriod === 'daily')
                            {{ __('Daily book loan and return volume') }}
                        @elseif($circulationPeriod === 'yearly')
                            {{ __('Yearly book loan and return volume') }}
                        @else
                            {{ __('Monthly book loan and return volume') }}
                        @endif
                    </p>
                </div>

                <!-- Period Filter Form (Day, Month, Year Switcher & Dropdowns) -->
                <form action="{{ route('reports.index') }}" method="GET" class="flex items-center flex-wrap gap-2.5 print-hide" id="reportPeriodForm">
                    <input type="hidden" name="tab" value="overview">
                    <input type="hidden" name="circulation_period" id="circulationPeriodInput" value="{{ $circulationPeriod }}">

                    <!-- Period Switcher Buttons: Day, Month, Year -->
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200/80 text-xs font-semibold shadow-2xs">
                        <button type="button" 
                                onclick="document.getElementById('circulationPeriodInput').value='daily'; document.getElementById('reportPeriodForm').submit();"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer {{ $circulationPeriod === 'daily' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>{{ __('Day') }}</span>
                        </button>
                        <button type="button" 
                                onclick="document.getElementById('circulationPeriodInput').value='monthly'; document.getElementById('reportPeriodForm').submit();"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer {{ $circulationPeriod === 'monthly' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>{{ __('Month') }}</span>
                        </button>
                        <button type="button" 
                                onclick="document.getElementById('circulationPeriodInput').value='yearly'; document.getElementById('reportPeriodForm').submit();"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer {{ $circulationPeriod === 'yearly' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span>{{ __('Year') }}</span>
                        </button>
                    </div>

                    @if($circulationPeriod === 'daily')
                        <!-- Month Picker -->
                        <div class="flex items-center gap-1.5">
                            <label for="month-select" class="text-xs font-semibold text-slate-500">{{ __('Month') }}:</label>
                            <select id="month-select" name="month" onchange="this.form.submit()"
                                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                        {{ app()->getLocale() === 'km' ? ($khmerMonthNames[$m] ?? $m) : date('M', mktime(0,0,0,$m,1)) }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    @endif

                    @if($circulationPeriod !== 'yearly')
                        <!-- Year Picker -->
                        <div class="flex items-center gap-1.5">
                            <label for="year-select" class="text-xs font-semibold text-slate-500">{{ __('Year') }}:</label>
                            <select id="year-select" name="year" onchange="this.form.submit()"
                                    class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                                @foreach($availableYears as $y)
                                    <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </form>
            </div>

            <!-- KPI Cards for the Selected Period -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100/60">
                    <p class="text-xs font-medium text-slate-500">
                        {{ $circulationPeriod === 'daily' ? __('Total Loans (This Month)') : ($circulationPeriod === 'yearly' ? __('Total Loans (All Years)') : __('Total Loans (This Year)')) }}
                    </p>
                    <p class="text-2xl font-bold text-[#1E3A8A] mt-1">{{ number_format($totalPeriodBorrows) }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('borrow transactions') }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100/60">
                    <p class="text-xs font-medium text-slate-500">
                        {{ $circulationPeriod === 'daily' ? __('Total Returns (This Month)') : ($circulationPeriod === 'yearly' ? __('Total Returns (All Years)') : __('Total Returns (This Year)')) }}
                    </p>
                    <p class="text-2xl font-bold text-[#10B981] mt-1">{{ number_format($totalPeriodReturns) }}</p>
                    <p class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $overallReturnRate }}% {{ __('Return Rate') }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100/60">
                    <p class="text-xs font-medium text-slate-500">
                        {{ $circulationPeriod === 'daily' ? __('Peak Day') : ($circulationPeriod === 'yearly' ? __('Peak Year') : __('Peak Month')) }}
                    </p>
                    <p class="text-2xl font-bold text-[#6366F1] mt-1 truncate">{{ $peakLabel ?? '-' }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ number_format($maxBorrows) }} {{ __('books') }}</p>
                </div>
                <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-100/60">
                    <p class="text-xs font-medium text-slate-500">
                        {{ $circulationPeriod === 'daily' ? __('Avg. Loans / Day') : ($circulationPeriod === 'yearly' ? __('Avg. Loans / Year') : __('Avg. Loans / Month')) }}
                    </p>
                    <p class="text-2xl font-bold text-[#F59E0B] mt-1">{{ $avgBorrows }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('books') }}</p>
                </div>
            </div>

            <!-- ApexCharts Visual Chart Container -->
            <div class="mt-6">
                <div id="monthlyCirculationChart" class="w-full"></div>
            </div>
        </div>

        <!-- Detailed Breakdown Table -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-800">
                        @if($circulationPeriod === 'daily')
                            {{ __('Daily Breakdown Table') }} ({{ sprintf('%02d', $selectedMonth) }}/{{ $selectedYear }})
                        @elseif($circulationPeriod === 'yearly')
                            {{ __('Yearly Breakdown Table (All Years)') }}
                        @else
                            {{ __('Monthly Breakdown Table') }} ({{ $selectedYear }})
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500">{{ __('Detailed loan circulation, returns, and overdue metrics.') }}</p>
                </div>
                <a href="{{ route('reports.export', ['type' => 'circulation_stats', 'circulation_period' => $circulationPeriod, 'year' => $selectedYear, 'month' => $selectedMonth]) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-[#1E3A8A] bg-blue-50 hover:bg-blue-100 transition-colors print-hide">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    {{ __('Export CSV') }}
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">
                                {{ $circulationPeriod === 'daily' ? __('Day') : ($circulationPeriod === 'yearly' ? __('Year') : __('Month')) }}
                            </th>
                            <th class="py-3 px-4 text-center">{{ __('Borrows') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Returns') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Overdue') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Return Rate') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @foreach($breakdownData as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4 font-semibold text-slate-800 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $row['borrows'] > 0 ? 'bg-[#1E3A8A]' : 'bg-slate-300' }}"></span>
                                    {{ $row['label'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-700">
                                    {{ $row['borrows'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-[#10B981]">
                                    {{ $row['returns'] }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['overdue'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-50 text-[#EF4444]">
                                            {{ $row['overdue'] }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">0</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-16 bg-slate-100 rounded-full h-1.5 hidden sm:block overflow-hidden">
                                            <div class="bg-[#10B981] h-1.5 rounded-full" style="width: {{ min(100, $row['return_rate']) }}%"></div>
                                        </div>
                                        <span class="font-medium text-slate-600 text-xs">{{ $row['return_rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($row['borrows'] == 0 && $row['returns'] == 0)
                                        <span class="text-slate-400 text-xs">{{ __('Inactive') }}</span>
                                    @elseif($row['return_rate'] >= 80)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#10B981]">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            {{ __('Optimal') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-600">
                                            {{ __('Active Circulation') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold text-xs sm:text-sm text-slate-800 border-t-2 border-slate-200">
                            <td class="py-3 px-4">{{ __('Total') }}</td>
                            <td class="py-3 px-4 text-center text-[#1E3A8A]">{{ number_format($totalPeriodBorrows) }}</td>
                            <td class="py-3 px-4 text-center text-[#10B981]">{{ number_format($totalPeriodReturns) }}</td>
                            <td class="py-3 px-4 text-center text-[#EF4444]">{{ number_format($totalPeriodOverdue) }}</td>
                            <td class="py-3 px-4 text-center text-slate-700">{{ $overallReturnRate }}%</td>
                            <td class="py-3 px-4 text-right text-slate-500">{{ count($breakdownData) }} {{ __('periods') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 2: TOP BORROWED BOOKS (សៀវភៅដែលមានអ្នកខ្ចីច្រើនជាងគេ) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'top_books'" class="space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>📚 {{ __('Top Borrowed Books') }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold">{{ __('Ranked by Popularity') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Books with highest borrowing frequency across all patrons.') }}</p>
                </div>
                <a href="{{ route('reports.export', 'top_books') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors print-hide">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Export Top Books (CSV)') }}</span>
                </a>
            </div>

            <!-- Top Books Ranked Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4 text-center w-14">{{ __('Rank') }}</th>
                            <th class="py-3 px-4">{{ __('Book Title & Author') }}</th>
                            <th class="py-3 px-4">{{ __('Category') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Times Borrowed') }}</th>
                            <th class="py-3 px-4">{{ __('Popularity') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Stock Status') }}</th>
                            <th class="py-3 px-4 text-right print-hide">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse($topBooks as $index => $book)
                            @php
                                $popularityPercent = $maxBookBorrows > 0 ? round(($book->borrows_count / $maxBookBorrows) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <!-- Rank Badge -->
                                <td class="py-4 px-4 text-center">
                                    @if($index === 0)
                                        <span class="w-7 h-7 rounded-full bg-amber-400 text-white font-extrabold flex items-center justify-center text-xs shadow-md shadow-amber-400/30 mx-auto">
                                            🥇
                                        </span>
                                    @elseif($index === 1)
                                        <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-800 font-extrabold flex items-center justify-center text-xs shadow-md mx-auto">
                                            🥈
                                        </span>
                                    @elseif($index === 2)
                                        <span class="w-7 h-7 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center text-xs shadow-md mx-auto">
                                            🥉
                                        </span>
                                    @else
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs mx-auto">
                                            {{ $index + 1 }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Book Thumbnail & Title -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $book->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                                             alt="{{ $book->title }}" 
                                             class="w-10 h-14 object-cover rounded-lg shadow-xs ring-1 ring-slate-200 shrink-0">
                                        <div>
                                            <a href="{{ route('books.show', $book) }}" class="font-bold text-slate-800 hover:text-[#1E3A8A] transition-colors line-clamp-1">
                                                {{ $book->title }}
                                            </a>
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $book->author }}</p>
                                            <span class="text-[10px] text-slate-400 font-mono">{{ $book->location_shelf }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ $book->category->name ?? __('General') }}
                                    </span>
                                </td>

                                <!-- Times Borrowed -->
                                <td class="py-4 px-4 text-center">
                                    <span class="text-base font-extrabold text-[#1E3A8A]">
                                        {{ number_format($book->borrows_count) }}
                                    </span>
                                    <span class="text-xs text-slate-400 block">{{ __('times') }}</span>
                                </td>

                                <!-- Popularity Meter -->
                                <td class="py-4 px-4">
                                    <div class="w-32 sm:w-40">
                                        <div class="flex items-center justify-between text-[11px] mb-1">
                                            <span class="font-bold text-slate-600">{{ $popularityPercent }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                            <div class="bg-gradient-to-r from-[#6366F1] to-[#1E3A8A] h-2 rounded-full" style="width: {{ $popularityPercent }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Stock Status -->
                                <td class="py-4 px-4 text-center">
                                    @if($book->available_copies > 0)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            {{ $book->available_copies }} / {{ $book->total_copies }} {{ __('Available') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                            {{ __('Borrowed Out') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Action -->
                                <td class="py-4 px-4 text-right print-hide">
                                    <a href="{{ route('books.show', $book) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-[#6366F1] hover:text-indigo-800 transition-colors">
                                        <span>{{ __('Details') }}</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">{{ __('No borrowing data recorded yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 3: MOST ACTIVE MEMBERS (សមាជិកដែលសកម្មបំផុត) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'top_members'" class="space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>👥 {{ __('Most Active Members') }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold">{{ __('Patron Leaderboard') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Patrons with highest volume of book borrows and library circulation.') }}</p>
                </div>
                <a href="{{ route('reports.export', 'top_members') }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors print-hide">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Export Top Members (CSV)') }}</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4 text-center w-14">{{ __('Rank') }}</th>
                            <th class="py-3 px-4">{{ __('Patron Name & Card') }}</th>
                            <th class="py-3 px-4">{{ __('Member Type') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Total Loans') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Active Loans') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Total Returned') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                            <th class="py-3 px-4 text-right print-hide">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse($topMembers as $index => $patron)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- Leaderboard Rank -->
                                <td class="py-4 px-4 text-center">
                                    @if($index === 0)
                                        <span class="w-7 h-7 rounded-full bg-amber-400 text-white font-extrabold flex items-center justify-center text-xs shadow-md shadow-amber-400/30 mx-auto">🥇</span>
                                    @elseif($index === 1)
                                        <span class="w-7 h-7 rounded-full bg-slate-300 text-slate-800 font-extrabold flex items-center justify-center text-xs shadow-md mx-auto">🥈</span>
                                    @elseif($index === 2)
                                        <span class="w-7 h-7 rounded-full bg-amber-700 text-white font-extrabold flex items-center justify-center text-xs shadow-md mx-auto">🥉</span>
                                    @else
                                        <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold flex items-center justify-center text-xs mx-auto">{{ $index + 1 }}</span>
                                    @endif
                                </td>

                                <!-- Avatar & Name -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $patron->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                             alt="{{ $patron->name }}" 
                                             class="w-10 h-10 rounded-full object-cover ring-2 ring-slate-100 shrink-0">
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $patron->name }}</p>
                                            <p class="text-xs text-slate-400 font-mono">{{ $patron->card_id ?? 'No Card ID' }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Member Type -->
                                <td class="py-4 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                        {{ __($patron->member_type ?? 'Student') }}
                                    </span>
                                </td>

                                <!-- Total Borrows -->
                                <td class="py-4 px-4 text-center">
                                    <span class="text-base font-extrabold text-[#1E3A8A]">
                                        {{ number_format($patron->total_borrows_count ?? 0) }}
                                    </span>
                                    <span class="text-xs text-slate-400 block">{{ __('loans') }}</span>
                                </td>

                                <!-- Active Borrows -->
                                <td class="py-4 px-4 text-center font-semibold text-[#6366F1]">
                                    {{ number_format($patron->active_borrows_count ?? 0) }}
                                </td>

                                <!-- Returned -->
                                <td class="py-4 px-4 text-center font-semibold text-[#10B981]">
                                    {{ number_format($patron->completed_borrows_count ?? 0) }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-4 px-4 text-center">
                                    @if($patron->status === 'Active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                            {{ __($patron->status) }}
                                        </span>
                                    @endif
                                </td>

                                <!-- One-Click Access to Feature 5: Member History -->
                                <td class="py-4 px-4 text-right print-hide">
                                    <a href="{{ route('reports.index', ['tab' => 'member_history', 'member_id' => $patron->id]) }}" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-[#6366F1] hover:bg-[#6366F1] hover:text-white text-xs font-semibold transition-all shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        <span>{{ __('View Full History') }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 text-sm">{{ __('No patrons found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 4: FINES & WAIVED FEES REPORT (របាយការណ៍ប្រាក់ពិន័យ និងលើកលែង) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'fines'" class="space-y-6">
        <!-- 3 KPIs Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <!-- Collected Fines -->
            <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Fines Collected') }}</span>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        💵
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-black text-slate-800">${{ number_format($totalCollectedFinesYear, 2) }}</span>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">៛{{ number_format($totalCollectedFinesYear * 4000) }} ({{ $selectedYear }})</p>
                </div>
            </div>

            <!-- Waived Fines -->
            <div class="bg-white rounded-3xl p-5 border border-amber-100 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-amber-700 uppercase tracking-wider">{{ __('Total Fines Waived') }}</span>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold">
                        🎁
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-black text-amber-700">${{ number_format($totalWaivedFinesYear, 2) }}</span>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">៛{{ number_format($totalWaivedFinesYear * 4000) }} ({{ __('Manager Exemptions') }})</p>
                </div>
            </div>

            <!-- Pending Fines -->
            <div class="bg-white rounded-3xl p-5 border border-rose-100 shadow-xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">{{ __('Pending Overdue Fines') }}</span>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold">
                        ⚠️
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-2xl sm:text-3xl font-black text-rose-600">${{ number_format($totalPendingFinesYear, 2) }}</span>
                    <p class="text-xs text-slate-500 font-mono mt-0.5">៛{{ number_format($totalPendingFinesYear * 4000) }} ({{ __('Unsettled Accounts') }})</p>
                </div>
            </div>
        </div>

        <!-- Monthly Breakdown Table Card -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>🎁 {{ __('Monthly Fines & Waived Fees Breakdown') }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 font-bold font-mono">{{ $selectedYear }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Tracking library fine revenues collected vs. special exemptions approved by Manager.') }}</p>
                </div>

                <a href="{{ route('reports.export', ['type' => 'monthly_fines', 'year' => $selectedYear]) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200 transition-colors print-hide">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Export Fines Report (CSV)') }}</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">{{ __('Month') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Collected (USD)') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Collected (KHR)') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Waived (USD)') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Waived Cases') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Pending Unpaid (USD)') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($monthlyFinesData as $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ __($row['month']) }}</td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-emerald-600">
                                    ${{ number_format($row['collected'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-500">
                                    ៛{{ number_format($row['collected_khr']) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-amber-700">
                                    ${{ number_format($row['waived'], 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($row['waived_count'] > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            🎁 {{ $row['waived_count'] }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-300">0</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right font-mono font-semibold {{ $row['pending'] > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    ${{ number_format($row['pending'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-bold border-t-2 border-slate-200 text-sm">
                            <td class="py-3 px-4 text-slate-800">{{ __('Full Year Total') }}</td>
                            <td class="py-3 px-4 text-right font-mono text-emerald-600">${{ number_format($totalCollectedFinesYear, 2) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-xs text-slate-500">៛{{ number_format($totalCollectedFinesYear * 4000) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-amber-700">${{ number_format($totalWaivedFinesYear, 2) }}</td>
                            <td class="py-3 px-4 text-center font-mono">{{ array_sum(array_column($monthlyFinesData, 'waived_count')) }}</td>
                            <td class="py-3 px-4 text-right font-mono text-rose-600">${{ number_format($totalPendingFinesYear, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 5: OVERDUE & LOST BOOKS REPORT (របាយការណ៍សៀវភៅហួសកំណត់ និងបាត់បង់) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'overdue'" class="space-y-6">
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <!-- Overdue Header & Alert -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>⚠️ {{ __('Overdue Books') }}</span>
                        @if($overdueBorrows->count() > 0)
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-red-100 text-[#EF4444] font-bold">{{ $overdueBorrows->count() }} {{ __('Overdue Items') }}</span>
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Auditing active book loans that have exceeded the permitted due date.') }}</p>
                </div>
                <div class="flex items-center gap-2.5 print-hide">
                    <a href="{{ route('reports.export', 'overdue') }}" 
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>{{ __('Export Overdue List (CSV)') }}</span>
                    </a>
                </div>
            </div>

            <!-- Overdue Callout Card -->
            @if($overdueBorrows->count() > 0)
                <div class="mb-6 p-4 rounded-2xl bg-red-50/70 border border-red-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-red-800">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold">{{ __('Total Accumulated Fines') }}: ${{ number_format($totalEstimatedFines, 2) }}</p>
                            <p class="text-xs text-red-600">{{ __('Calculated at $0.50/day') }}. {{ __('Patrons with active overdue books are restricted from issuing new loans.') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Overdue Loans Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">{{ __('Member') }}</th>
                            <th class="py-3 px-4">{{ __('Book Title') }}</th>
                            <th class="py-3 px-4">{{ __('Borrow Date') }}</th>
                            <th class="py-3 px-4">{{ __('Due Date') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Days Overdue') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Accumulated Fine') }}</th>
                            <th class="py-3 px-4 text-right print-hide">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                        @forelse($overdueBorrows as $item)
                            <tr class="hover:bg-red-50/20 transition-colors">
                                <!-- Borrower Info -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $item->user->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                             alt="{{ $item->user->name ?? 'N/A' }}" 
                                             class="w-9 h-9 rounded-full object-cover ring-2 ring-red-200 shrink-0">
                                        <div>
                                            <a href="{{ route('reports.index', ['tab' => 'member_history', 'member_id' => $item->user_id]) }}" 
                                               class="font-bold text-slate-800 hover:text-[#1E3A8A]">
                                                {{ $item->user->name ?? __('Unknown') }}
                                            </a>
                                            <p class="text-[11px] text-slate-400 font-mono">{{ $item->user->card_id ?? 'No Card' }}</p>
                                            <p class="text-[10px] text-slate-500 font-mono">{{ $item->user->phone ?? $item->user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Book Info -->
                                <td class="py-4 px-4">
                                    <p class="font-bold text-slate-800 line-clamp-1">{{ $item->book->title ?? __('Unknown Book') }}</p>
                                    <p class="text-xs text-slate-400">{{ $item->book->author ?? '' }}</p>
                                    <span class="text-[10px] text-slate-400 font-mono">{{ $item->book->location_shelf ?? 'Shelf N/A' }}</span>
                                </td>

                                <!-- Borrow Date -->
                                <td class="py-4 px-4 text-slate-600 font-mono text-xs">
                                    {{ $item->borrow_date ? $item->borrow_date->format('M d, Y') : '-' }}
                                </td>

                                <!-- Due Date -->
                                <td class="py-4 px-4 font-mono text-xs font-bold text-red-600">
                                    {{ $item->due_date ? $item->due_date->format('M d, Y') : '-' }}
                                </td>

                                <!-- Days Overdue -->
                                <td class="py-4 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-extrabold bg-red-100 text-[#EF4444]">
                                        +{{ $item->days_overdue }} {{ __('days late') }}
                                    </span>
                                </td>

                                <!-- Calculated Fine -->
                                <td class="py-4 px-4 text-center font-bold text-red-600 text-sm">
                                    ${{ number_format($item->calculated_fine, 2) }}
                                </td>

                                <!-- Shortcut Actions -->
                                <td class="py-4 px-4 text-right print-hide">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- Return Book Shortcut -->
                                        <form action="{{ route('borrows.return', $item) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    onclick="return confirm('{{ __('Confirm book return and fee settlement?') }}')"
                                                    class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors">
                                                {{ __('Return Book') }}
                                            </button>
                                        </form>

                                        <!-- History Link -->
                                        <a href="{{ route('reports.index', ['tab' => 'member_history', 'member_id' => $item->user_id]) }}"
                                           class="px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                                            {{ __('History') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-[#10B981] flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <p class="font-bold text-slate-700">{{ __('No overdue loans found. Great job!') }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ __('All members returned their books on time.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Zero-Stock & Potential Lost Books Audit Card (Manager Inventory Audit) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>📦 {{ __('Zero Available Stock & Inventory Audit') }}</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold">{{ $lostBooks->count() }} {{ __('Titles at 0 Copies') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Books with zero shelf availability. Compare total catalog copies against active loans to identify lost or missing items.') }}</p>
                </div>

                <a href="{{ route('reports.export', ['type' => 'overdue_lost']) }}" 
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors print-hide">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>{{ __('Export Overdue & Lost Audit (CSV)') }}</span>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3 px-4">{{ __('Book Title & Location') }}</th>
                            <th class="py-3 px-4">{{ __('Category') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Catalog Total') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Active Loans') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Available on Shelf') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Inventory Audit Status') }}</th>
                            <th class="py-3 px-4 text-right print-hide">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse($lostBooks as $lostBook)
                            @php
                                $missingCopies = max(0, $lostBook->total_copies - ($lostBook->active_loans_count ?? 0));
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $lostBook->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=100&auto=format&fit=crop&q=80' }}" 
                                             class="w-9 h-12 rounded-lg object-cover ring-1 ring-slate-200">
                                        <div>
                                            <p class="font-bold text-slate-800 text-xs sm:text-sm">{{ $lostBook->title }}</p>
                                            <span class="text-[11px] text-slate-400 font-mono">📍 {{ $lostBook->location_shelf }} &bull; ISBN: {{ $lostBook->isbn ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs font-semibold text-slate-600">
                                    {{ $lostBook->category->name ?? __('General') }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                    {{ $lostBook->total_copies }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-[#6366F1]">
                                    {{ $lostBook->active_loans_count ?? 0 }}
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold text-rose-600">
                                    0
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($missingCopies > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            ⚠️ {{ __('Missing: :count copies', ['count' => $missingCopies]) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            📚 {{ __('All copies on active loan') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right print-hide">
                                    <a href="{{ route('books.show', $lostBook) }}" class="text-xs font-semibold text-[#1E3A8A] hover:underline">
                                        {{ __('Catalog Details') }} &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                    {{ __('All catalog books have available stock on shelf.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================================================================= -->
    <!-- TAB 6: MEMBER HISTORY LOOKUP BY FULL NAME (ប្រវត្តសមាជិក៖ បញ្ចូលឈ្មោះពេញ) -->
    <!-- ========================================================================================= -->
    <div x-show="activeTab === 'member_history'" class="space-y-6">
        <!-- Search Input Bar & Quick Test Chips -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs print-hide">
            <div class="max-w-2xl">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>🔍 {{ __('Search Member by Full Name') }}</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Enter patron full name or card ID to audit their entire borrowing record, dates, and active loans.') }}</p>
                
                <!-- Search Form -->
                <form action="{{ route('reports.index') }}" method="GET" class="mt-4 flex items-center gap-2">
                    <input type="hidden" name="tab" value="member_history">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" 
                               name="search_member" 
                               value="{{ $searchMemberName }}" 
                               placeholder="{{ __('Enter full name or Card ID...') }} (e.g. Vannak Keo, Sreymom Pich)" 
                               class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] transition-all">
                    </div>
                    <button type="submit" 
                            class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-sm font-semibold hover:bg-blue-900 shadow-md shadow-blue-900/20 transition-all shrink-0">
                        {{ __('Search') }}
                    </button>
                    @if(!empty($searchMemberName) || $selectedMember)
                        <a href="{{ route('reports.index', ['tab' => 'member_history']) }}" 
                           class="px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-sm font-semibold transition-all shrink-0">
                            {{ __('Clear') }}
                        </a>
                    @endif
                </form>

                <!-- Quick Suggestions Chips (One-click instant testing) -->
                @if($quickMembers->isNotEmpty())
                    <div class="mt-3.5 flex items-center flex-wrap gap-2 text-xs">
                        <span class="text-slate-400 font-medium">{{ __('Quick Suggestions:') }}</span>
                        @foreach($quickMembers as $quick)
                            <a href="{{ route('reports.index', ['tab' => 'member_history', 'member_id' => $quick->id]) }}" 
                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 hover:bg-indigo-50 hover:text-[#6366F1] text-slate-700 font-medium transition-all {{ $selectedMember && $selectedMember->id == $quick->id ? 'ring-2 ring-[#6366F1] bg-indigo-50 text-[#6366F1]' : '' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>{{ $quick->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">({{ $quick->card_id }})</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Multiple Search Matches Selection (If search query matched >1 members) -->
            @if($matchedMembers->isNotEmpty())
                <div class="mt-6 pt-6 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">{{ __('Multiple members matched') }} ({{ $matchedMembers->count() }}):</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        @foreach($matchedMembers as $match)
                            <a href="{{ route('reports.index', ['tab' => 'member_history', 'member_id' => $match->id]) }}" 
                               class="flex items-center gap-3 p-3 rounded-2xl border border-slate-200 hover:border-[#6366F1] hover:bg-indigo-50/30 transition-all group">
                                <img src="{{ $match->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                     alt="{{ $match->name }}" 
                                     class="w-10 h-10 rounded-full object-cover">
                                <div>
                                    <p class="font-bold text-slate-800 group-hover:text-[#1E3A8A] text-sm">{{ $match->name }}</p>
                                    <p class="text-xs text-slate-400 font-mono">{{ $match->card_id }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        @if($selectedMember)
            <!-- Member Profile Card -->
            <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100">
                    <div class="flex items-center gap-4">
                        <img src="{{ $selectedMember->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                             alt="{{ $selectedMember->name }}" 
                             class="w-16 h-16 rounded-2xl object-cover ring-2 ring-indigo-200 shadow-md">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-xl font-bold text-slate-800">{{ $selectedMember->name }}</h3>
                                @if($selectedMember->status === 'Active')
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                        {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-50 text-[#EF4444] border border-red-200/50">
                                        {{ __($selectedMember->status) }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 mt-1">
                                <span class="font-mono">💳 {{ $selectedMember->card_id ?? 'No Card' }}</span>
                                <span>🎓 {{ __($selectedMember->member_type ?? 'Student') }}</span>
                                <span>📞 {{ $selectedMember->phone ?? '-' }}</span>
                                <span>✉️ {{ $selectedMember->email }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 print-hide">
                        <a href="{{ route('reports.export', ['type' => 'member_history', 'member_id' => $selectedMember->id]) }}" 
                           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>{{ __('Export Member History (CSV)') }}</span>
                        </a>
                    </div>
                </div>

                <!-- Patron Loan Summary KPIs -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                    <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-100">
                        <p class="text-xs font-medium text-slate-500">{{ __('Total Borrow Count') }}</p>
                        <p class="text-2xl font-extrabold text-[#1E3A8A] mt-1">{{ $selectedMember->borrows->count() }}</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('All-time loans') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                        <p class="text-xs font-medium text-slate-500">{{ __('Currently Active') }}</p>
                        <p class="text-2xl font-extrabold text-[#6366F1] mt-1">
                            {{ $selectedMember->borrows->whereIn('status', ['Borrowed', 'Overdue'])->count() }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Books with member') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                        <p class="text-xs font-medium text-slate-500">{{ __('Total Returned') }}</p>
                        <p class="text-2xl font-extrabold text-[#10B981] mt-1">
                            {{ $selectedMember->borrows->where('status', 'Returned')->count() }}
                        </p>
                        <p class="text-[11px] text-emerald-600 mt-0.5">{{ __('Completed loans') }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-red-50/50 border border-red-100">
                        <p class="text-xs font-medium text-slate-500">{{ __('Unpaid Fines') }}</p>
                        <p class="text-2xl font-extrabold text-[#EF4444] mt-1">
                            ${{ number_format($selectedMember->unpaidFinesTotal(), 2) }}
                        </p>
                        <p class="text-[11px] text-red-500 mt-0.5">{{ __('Pending late fees') }}</p>
                    </div>
                </div>

                <!-- Detailed Circulation History Table -->
                <div class="mt-8">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span>📖 {{ __('Borrowing History & Books Audit') }}</span>
                            <span class="text-xs font-mono px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $selectedMember->borrows->count() }} {{ __('records') }}</span>
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4 w-10">#</th>
                                    <th class="py-3 px-4">{{ __('Book Title & Author') }}</th>
                                    <th class="py-3 px-4">{{ __('Category') }}</th>
                                    <th class="py-3 px-4">{{ __('Borrow Date') }}</th>
                                    <th class="py-3 px-4">{{ __('Due Date') }}</th>
                                    <th class="py-3 px-4">{{ __('Return Date') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                                    <th class="py-3 px-4 text-center">{{ __('Fine ($)') }}</th>
                                    <th class="py-3 px-4">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                                @forelse($selectedMember->borrows as $bIndex => $loan)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="py-3.5 px-4 text-slate-400 font-mono text-xs">{{ $bIndex + 1 }}</td>
                                        
                                        <!-- Book Details -->
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <img src="{{ $loan->book->cover_image ?? 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=150&auto=format&fit=crop&q=80' }}" 
                                                     alt="{{ $loan->book->title ?? '' }}" 
                                                     class="w-8 h-11 object-cover rounded shadow-2xs shrink-0">
                                                <div>
                                                    <p class="font-bold text-slate-800 line-clamp-1">{{ $loan->book->title ?? __('Unknown Book') }}</p>
                                                    <p class="text-xs text-slate-400">{{ $loan->book->author ?? '' }}</p>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Category -->
                                        <td class="py-3.5 px-4 text-xs text-slate-600">
                                            <span class="inline-block px-2 py-0.5 rounded-full bg-slate-100 font-medium">
                                                {{ $loan->book->category->name ?? __('General') }}
                                            </span>
                                        </td>

                                        <!-- Borrow Date -->
                                        <td class="py-3.5 px-4 font-mono text-xs text-slate-600">
                                            {{ $loan->borrow_date ? $loan->borrow_date->format('M d, Y') : '-' }}
                                        </td>

                                        <!-- Due Date -->
                                        <td class="py-3.5 px-4 font-mono text-xs font-semibold {{ $loan->status === 'Overdue' ? 'text-red-600' : 'text-slate-600' }}">
                                            {{ $loan->due_date ? $loan->due_date->format('M d, Y') : '-' }}
                                        </td>

                                        <!-- Actual Return Date -->
                                        <td class="py-3.5 px-4 font-mono text-xs">
                                            @if($loan->return_date)
                                                <span class="text-[#10B981] font-semibold">{{ $loan->return_date->format('M d, Y') }}</span>
                                            @else
                                                <span class="text-amber-600 font-medium italic">{{ __('Not Returned Yet') }}</span>
                                            @endif
                                        </td>

                                        <!-- Status Badge -->
                                        <td class="py-3.5 px-4 text-center">
                                            @if($loan->status === 'Borrowed')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-[#3B82F6] border border-blue-200/50">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#3B82F6]"></span>
                                                    {{ __('Borrowed') }}
                                                </span>
                                            @elseif($loan->status === 'Overdue')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50 animate-pulse">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                                    {{ __('Overdue') }}
                                                </span>
                                            @elseif($loan->status === 'Returned')
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                                    {{ __('Returned') }}
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Fine Amount -->
                                        <td class="py-3.5 px-4 text-center font-mono text-xs">
                                            @if($loan->fine_amount > 0)
                                                <span class="font-bold {{ $loan->fine_paid ? 'text-slate-600 line-through' : 'text-red-600' }}">
                                                    ${{ number_format($loan->fine_amount, 2) }}
                                                </span>
                                                <span class="text-[10px] block text-slate-400">
                                                    {{ $loan->fine_paid ? __('Paid') : __('Unpaid') }}
                                                </span>
                                            @else
                                                <span class="text-slate-400">$0.00</span>
                                            @endif
                                        </td>

                                        <!-- Notes -->
                                        <td class="py-3.5 px-4 text-xs text-slate-500 max-w-xs truncate">
                                            {{ $loan->notes ?? '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-8 text-center text-slate-400 text-sm">
                                            {{ __('No loans found for this member.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State Prompt -->
            <div class="bg-white rounded-3xl p-12 text-center border border-slate-100 shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-[#6366F1] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">{{ __('Select a member to view record') }}</h3>
                <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1">
                    {{ __('Search for a member above or pick from quick suggestions to view complete borrowing history, dates, and borrowed books.') }}
                </p>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Render 12-Month ApexChart if container exists
        const chartEl = document.querySelector("#monthlyCirculationChart");
        if (chartEl && window.ApexCharts) {
            const chartOptions = {
                series: [
                    {
                        name: '{{ __("Borrows") }}',
                        data: {!! json_encode($breakdownBorrows ?? $monthlyBorrows) !!}
                    },
                    {
                        name: '{{ __("Returns") }}',
                        data: {!! json_encode($breakdownReturns ?? $monthlyReturns) !!}
                    }
                ],
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: false,
                            zoomin: false,
                            zoomout: false,
                            pan: false,
                            reset: false
                        }
                    },
                    fontFamily: 'inherit'
                },
                colors: ['#1E3A8A', '#10B981'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 90, 100]
                    }
                },
                xaxis: {
                    categories: {!! json_encode($breakdownLabels ?? $monthLabels) !!},
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#64748b',
                            fontSize: '11px'
                        },
                        formatter: val => Math.round(val)
                    }
                },
                grid: {
                    strokeDashArray: 4,
                    borderColor: '#f1f5f9'
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '12px',
                    fontWeight: 600,
                    markers: {
                        radius: 12
                    }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: val => val + " " + "{{ __('books') }}"
                    }
                }
            };

            const chart = new ApexCharts(chartEl, chartOptions);
            chart.render();
        }
    });
</script>
@endpush
