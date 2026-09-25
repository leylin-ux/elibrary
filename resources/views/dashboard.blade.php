@extends('layouts.app')

@section('title', __('Dashboard Overview'))

@section('content')
<div class="space-y-8">
    <!-- Header Greeting & Quick Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-[#1E3A8A]">
                {{ __('Welcome to Dashboard') }}, {{ auth()->user()->name ?? __('Administrator') }}! 👋
            </h1>
            <p class="text-sm text-slate-500 mt-1">{{ __('Here is the real-time activity and circulation performance of your library today.') }}</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs sm:text-sm font-semibold hover:bg-slate-50 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>{{ __('New Book') }}</span>
            </a>
            <a href="{{ route('borrows.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold hover:bg-blue-900 shadow-md shadow-blue-950/20 transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                <span>{{ __('Issue Book') }}</span>
            </a>
        </div>
    </div>

    <!-- 4 Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- 1. Total Books -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Total Books') }}</span>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-800">{{ number_format($totalBooks) }}</span>
                <span class="text-xs font-semibold text-emerald-600 flex items-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                    +8.4%
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ __('Across 5 academic categories') }}</p>
        </div>

        <!-- 2. Total Members -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Total Members') }}</span>
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-[#6366F1] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-800">{{ number_format($totalMembers) }}</span>
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#10B981]/15 text-[#10B981]">
                    {{ __('Active') }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ __('Students, Lecturers & Staff') }}</p>
        </div>

        <!-- 3. Borrowed Books -->
        <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ __('Borrowed Books') }}</span>
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#3B82F6] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-slate-800">{{ number_format($borrowedCount) }}</span>
                <span class="text-xs font-medium text-slate-500">{{ __('In circulation') }}</span>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ __('Normal loan period (14 days)') }}</p>
        </div>

        <!-- 4. Overdue Books -->
        <div class="bg-white rounded-2xl p-5 border border-red-100 shadow-xs hover:shadow-md transition-shadow group">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-red-500 uppercase tracking-wider">{{ __('Overdue Alert') }}</span>
                <div class="w-11 h-11 rounded-xl bg-red-50 text-[#EF4444] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-4 flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-[#EF4444]">{{ number_format($overdueCount) }}</span>
                <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-red-100 text-[#EF4444]">
                    {{ __('Action Required') }}
                </span>
            </div>
            <p class="text-xs text-slate-400 mt-1">{{ __('Fine accumulates daily') }}</p>
        </div>
    </div>

    <!-- Pending Reservations Notification Banner (Step 2 of System Workflow) -->
    @if(isset($pendingReservationsCount) && $pendingReservationsCount > 0)
        <div class="p-5 rounded-3xl bg-gradient-to-r from-amber-500 via-amber-600 to-orange-500 text-white shadow-lg shadow-amber-500/20 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-2xl shrink-0">
                    🔔
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-white text-amber-900 tracking-wider">
                            {{ __('Action Required') }}
                        </span>
                        <h3 class="text-base font-bold">{{ __('New Book Hold Requests Awaiting Review') }}</h3>
                    </div>
                    <p class="text-xs text-amber-100 mt-0.5">
                        {{ __('There are :count member hold requests pending. Pull copies to the hold shelf and approve pickup deadlines (24-48h).', ['count' => $pendingReservationsCount]) }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('reservations.index', ['status' => 'Pending']) }}" 
                   class="px-4 py-2.5 rounded-xl bg-white text-amber-900 hover:bg-amber-50 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                    <span>{{ __('Review & Approve Holds') }}</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Circulation Growth Overview (Daily, Weekly, Monthly, Yearly) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Daily Growth -->
        <div class="bg-gradient-to-br from-blue-500/10 via-white to-white rounded-2xl p-4 sm:p-5 border border-blue-100 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Daily Circulation Growth') }}</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($todayBorrows) }} <span class="text-xs font-medium text-slate-400 font-sans">{{ __('loans today') }}</span></span>
                    @if($dailyGrowth >= 0)
                        <span class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            +{{ $dailyGrowth }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            {{ $dailyGrowth }}%
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">{{ __('vs. :count loans yesterday', ['count' => $yesterdayBorrows]) }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-blue-600/10 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>

        <!-- 2. Weekly Growth -->
        <div class="bg-gradient-to-br from-indigo-500/10 via-white to-white rounded-2xl p-4 sm:p-5 border border-indigo-100 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Weekly Circulation Growth') }}</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($thisWeekBorrows) }} <span class="text-xs font-medium text-slate-400 font-sans">{{ __('loans this week') }}</span></span>
                    @if($weeklyGrowth >= 0)
                        <span class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            +{{ $weeklyGrowth }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            {{ $weeklyGrowth }}%
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">{{ __('vs. :count loans last week', ['count' => $lastWeekBorrows]) }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-indigo-600/10 text-indigo-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </div>

        <!-- 3. Monthly Growth -->
        <div class="bg-gradient-to-br from-emerald-500/10 via-white to-white rounded-2xl p-4 sm:p-5 border border-emerald-100 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Monthly Circulation Growth') }}</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($thisMonthBorrows) }} <span class="text-xs font-medium text-slate-400 font-sans">{{ __('loans this month') }}</span></span>
                    @if($monthlyGrowth >= 0)
                        <span class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            +{{ $monthlyGrowth }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            {{ $monthlyGrowth }}%
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">{{ __('vs. :count loans last month', ['count' => $lastMonthBorrows]) }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-emerald-600/10 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
        </div>

        <!-- 4. Yearly Growth -->
        <div class="bg-gradient-to-br from-amber-500/10 via-white to-white rounded-2xl p-4 sm:p-5 border border-amber-100 shadow-xs flex items-center justify-between">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ __('Yearly Circulation Growth') }}</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <span class="text-2xl font-black text-slate-800">{{ number_format($thisYearBorrows) }} <span class="text-xs font-medium text-slate-400 font-sans">{{ __('loans this year') }}</span></span>
                    @if($yearlyGrowth >= 0)
                        <span class="inline-flex items-center text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path></svg>
                            +{{ $yearlyGrowth }}%
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-200/60">
                            <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                            {{ $yearlyGrowth }}%
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">{{ __('vs. :count loans last year', ['count' => $lastYearBorrows]) }}</p>
            </div>
            <div class="w-11 h-11 rounded-2xl bg-amber-600/10 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
        </div>
    </div>

    <!-- Charts Section (Line Chart & Donut Chart) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Borrow Trends Line Chart with Day / Month / Year Switcher -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs lg:col-span-2"
             x-data="circulationTrendsWidget()">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-800">{{ __('Borrow & Return Trends') }}</h3>
                        <span class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200/50" x-text="periodLabels[period]"></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-1" x-text="periodSubtitles[period]"></p>
                </div>

                <div class="flex items-center flex-wrap gap-2.5">
                    <!-- Period Filter Switcher: Day, Month, Year -->
                    <div class="inline-flex p-1 bg-slate-100/90 rounded-xl border border-slate-200/80 text-xs font-semibold shadow-2xs">
                        <button type="button" 
                                @click="setPeriod('daily')" 
                                :class="period === 'daily' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>{{ __('Day') }}</span>
                        </button>
                        <button type="button" 
                                @click="setPeriod('monthly')" 
                                :class="period === 'monthly' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            <span>{{ __('Month') }}</span>
                        </button>
                        <button type="button" 
                                @click="setPeriod('yearly')" 
                                :class="period === 'yearly' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60'"
                                class="px-3 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            <span>{{ __('Year') }}</span>
                        </button>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-3 text-xs pl-2 border-l border-slate-200">
                        <span class="flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="w-3 h-3 rounded-full bg-[#6366F1]"></span> {{ __('Borrowed') }}
                        </span>
                        <span class="flex items-center gap-1.5 font-medium text-slate-600">
                            <span class="w-3 h-3 rounded-full bg-[#10B981]"></span> {{ __('Returned') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Period Summary Statistics Bar -->
            <div class="grid grid-cols-3 gap-3 mb-4 p-3 bg-slate-50/70 rounded-xl border border-slate-100 text-center">
                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">{{ __('Total Loans in Period') }}</span>
                    <span class="text-base font-extrabold text-[#6366F1]" x-text="currentSummary.borrowed"></span>
                </div>
                <div class="border-x border-slate-200/60">
                    <span class="text-[11px] text-slate-400 block font-medium">{{ __('Total Returned in Period') }}</span>
                    <span class="text-base font-extrabold text-[#10B981]" x-text="currentSummary.returned"></span>
                </div>
                <div>
                    <span class="text-[11px] text-slate-400 block font-medium">{{ __('Return Rate') }}</span>
                    <span class="text-base font-extrabold text-slate-800" x-text="currentSummary.rate + '%'"></span>
                </div>
            </div>

            <div id="borrowTrendsChart" class="w-full h-72"></div>
        </div>

        <!-- Book Categories Donut Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-800">{{ __('Categories Distribution') }}</h3>
                <p class="text-xs text-slate-400">{{ __('Collection breakdown by subject') }}</p>
            </div>
            <div id="categoryDonutChart" class="w-full h-72 flex items-center justify-center"></div>
        </div>
    </div>

    <!-- Circulation Feeds & Manager Activity Logs Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Recent Feeds Table (2 Cols) -->
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col justify-between">
            <div>
                <div class="px-6 py-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">{{ __('Recent Circulation Feeds') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ __('Real-time status of recently borrowed and returned books') }}</p>
                    </div>
                    <a href="{{ route('borrows.index') }}" class="text-xs font-semibold text-[#6366F1] hover:text-indigo-700 flex items-center gap-1">
                        {{ __('View All Activity') }} &rarr;
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6">{{ __('Member') }}</th>
                                <th class="py-3.5 px-6">{{ __('Book Title') }}</th>
                                <th class="py-3.5 px-6">{{ __('Borrow Date') }}</th>
                                <th class="py-3.5 px-6">{{ __('Status') }}</th>
                                <th class="py-3.5 px-6 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @forelse($recentBorrows as $borrow)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $borrow->user?->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                                 alt="{{ $borrow->user?->name ?? 'User' }}" 
                                                 class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200">
                                            <div>
                                                <p class="font-semibold text-slate-800 leading-tight">{{ $borrow->user?->name ?? __('Unknown Patron') }}</p>
                                                <p class="text-[11px] text-slate-400 font-mono">{{ $borrow->user?->card_id ?? __('No Card') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <p class="font-medium text-slate-800 truncate max-w-xs">{{ $borrow->book?->title ?? __('Book Deleted / Unavailable') }}</p>
                                        <span class="text-xs text-slate-400">{{ $borrow->book?->location_shelf ?? 'N/A' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-slate-600 text-xs">
                                        {{ $borrow->borrow_date ? $borrow->borrow_date->format('M d, Y') : '-' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($borrow->status === 'Borrowed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-[#3B82F6] border border-blue-200/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#3B82F6]"></span>
                                                {{ __('Borrowed') }}
                                            </span>
                                        @elseif($borrow->status === 'Overdue')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50 animate-pulse">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                                {{ __('Overdue') }}
                                            </span>
                                        @elseif($borrow->status === 'Returned')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                                {{ __('Returned') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-[#F59E0B] border border-amber-200/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></span>
                                                {{ __($borrow->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        @if($borrow->status !== 'Returned')
                                            <form action="{{ route('borrows.return', $borrow) }}" method="POST" class="inline return-book-form">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-3 py-1.5 text-xs font-semibold text-[#1E3A8A] hover:text-white hover:bg-[#1E3A8A] rounded-lg border border-blue-200 transition-all cursor-pointer">
                                                    {{ __('Return') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400 font-medium">{{ __('Done') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-slate-400 text-sm">{{ __('No recent transactions found.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Manager & Staff Activity Logs (1 Col) -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-800">{{ __('Activity Logs') }}</h3>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60 uppercase">{{ __('Audit Trail') }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ __('Real-time staff & manager actions') }}</p>
                </div>
            </div>

            <div class="p-4 space-y-3.5 overflow-y-auto max-h-[480px]">
                @forelse($recentActivityLogs as $log)
                    <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100/80 hover:bg-slate-50 transition-colors flex items-start gap-3">
                        <!-- Action Icon -->
                        <div class="w-8 h-8 rounded-lg shrink-0 flex items-center justify-center text-xs mt-0.5
                            @if(str_contains($log->action, 'WAIVE')) bg-emerald-100 text-emerald-700
                            @elseif(str_contains($log->action, 'CANCEL') || str_contains($log->action, 'DELETE') || str_contains($log->action, 'BLOCK') || str_contains($log->action, 'SUSPEND')) bg-rose-100 text-rose-700
                            @elseif(str_contains($log->action, 'POLICY') || str_contains($log->action, 'ROLE')) bg-purple-100 text-purple-700
                            @elseif(str_contains($log->action, 'RETURN') || str_contains($log->action, 'ACTIVATE')) bg-teal-100 text-teal-700
                            @else bg-blue-100 text-blue-700 @endif">
                            @if(str_contains($log->action, 'WAIVE'))
                                🎁
                            @elseif(str_contains($log->action, 'CANCEL'))
                                🚫
                            @elseif(str_contains($log->action, 'DELETE'))
                                🗑️
                            @elseif(str_contains($log->action, 'SUSPEND') || str_contains($log->action, 'BLOCK'))
                                ⚠️
                            @elseif(str_contains($log->action, 'POLICY') || str_contains($log->action, 'ROLE'))
                                ⚙️
                            @elseif(str_contains($log->action, 'RETURN') || str_contains($log->action, 'ACTIVATE'))
                                ✅
                            @else
                                📋
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-slate-800 truncate">{{ $log->user->name ?? __('System') }}</span>
                                    @if($log->user && $log->user->isSuperAdmin())
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-purple-100 text-purple-800">ADMIN</span>
                                    @elseif($log->user && $log->user->isManager())
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-blue-100 text-blue-800">MANAGER</span>
                                    @endif
                                </div>
                                <span class="text-[10px] text-slate-400 shrink-0">{{ $log->created_at->diffForHumans(null, true) }}</span>
                            </div>

                            <p class="text-xs text-slate-600 mt-1 leading-snug line-clamp-2">
                                {{ $log->description }}
                            </p>

                            <div class="mt-1.5 flex items-center justify-between text-[10px] text-slate-400">
                                <span class="font-mono bg-white px-1.5 py-0.5 rounded border border-slate-200/60">{{ $log->action }}</span>
                                @if($log->ip_address)
                                    <span class="font-mono text-[9px] text-slate-400">{{ $log->ip_address }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-slate-400 text-xs">
                        {{ __('No activity recorded yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const circulationDatasets = {
        daily: {
            categories: {!! json_encode($dailyCategories) !!},
            borrowed: {!! json_encode($dailyBorrows) !!},
            returned: {!! json_encode($dailyReturns) !!},
            label: '{{ __("Daily") }}',
            subtitle: '{{ __("Daily book loan and return volume (Last 14 days)") }}',
            totalBorrowed: {{ array_sum($dailyBorrows) }},
            totalReturned: {{ array_sum($dailyReturns) }},
            rate: {{ array_sum($dailyBorrows) > 0 ? round((array_sum($dailyReturns) / array_sum($dailyBorrows)) * 100, 1) : 0 }}
        },
        monthly: {
            categories: {!! json_encode($monthlyCategories) !!},
            borrowed: {!! json_encode($monthlyBorrows) !!},
            returned: {!! json_encode($monthlyReturns) !!},
            label: '{{ __("Monthly") }}',
            subtitle: '{{ __("Monthly book loan and return volume (Last 6 months)") }}',
            totalBorrowed: {{ array_sum($monthlyBorrows) }},
            totalReturned: {{ array_sum($monthlyReturns) }},
            rate: {{ array_sum($monthlyBorrows) > 0 ? round((array_sum($monthlyReturns) / array_sum($monthlyBorrows)) * 100, 1) : 0 }}
        },
        yearly: {
            categories: {!! json_encode($yearlyCategories) !!},
            borrowed: {!! json_encode($yearlyBorrows) !!},
            returned: {!! json_encode($yearlyReturns) !!},
            label: '{{ __("Yearly") }}',
            subtitle: '{{ __("Yearly book loan and return volume (Last 5 years)") }}',
            totalBorrowed: {{ array_sum($yearlyBorrows) }},
            totalReturned: {{ array_sum($yearlyReturns) }},
            rate: {{ array_sum($yearlyBorrows) > 0 ? round((array_sum($yearlyReturns) / array_sum($yearlyBorrows)) * 100, 1) : 0 }}
        }
    };

    let borrowLineChartInstance = null;

    window.circulationTrendsWidget = function() {
        return {
            period: 'monthly',
            periodLabels: {
                daily: circulationDatasets.daily.label,
                monthly: circulationDatasets.monthly.label,
                yearly: circulationDatasets.yearly.label
            },
            periodSubtitles: {
                daily: circulationDatasets.daily.subtitle,
                monthly: circulationDatasets.monthly.subtitle,
                yearly: circulationDatasets.yearly.subtitle
            },
            currentSummary: {
                borrowed: circulationDatasets.monthly.totalBorrowed,
                returned: circulationDatasets.monthly.totalReturned,
                rate: circulationDatasets.monthly.rate
            },
            setPeriod(p) {
                this.period = p;
                this.currentSummary = {
                    borrowed: circulationDatasets[p].totalBorrowed,
                    returned: circulationDatasets[p].totalReturned,
                    rate: circulationDatasets[p].rate
                };
                if (borrowLineChartInstance) {
                    borrowLineChartInstance.updateOptions({
                        xaxis: {
                            categories: circulationDatasets[p].categories
                        }
                    });
                    borrowLineChartInstance.updateSeries([
                        { name: '{{ __("Borrowed") }}', data: circulationDatasets[p].borrowed },
                        { name: '{{ __("Returned") }}', data: circulationDatasets[p].returned }
                    ]);
                }
            }
        };
    };

    document.addEventListener('DOMContentLoaded', function () {
        // Line Chart (Borrow & Return Trends)
        const lineOptions = {
            series: [{
                name: '{{ __("Borrowed") }}',
                data: circulationDatasets.monthly.borrowed
            }, {
                name: '{{ __("Returned") }}',
                data: circulationDatasets.monthly.returned
            }],
            chart: {
                height: 280,
                type: 'area',
                toolbar: { show: false },
                fontFamily: 'inherit'
            },
            colors: ['#6366F1', '#10B981'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 2.5
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.25,
                    opacityTo: 0.02,
                    stops: [0, 90, 100]
                }
            },
            xaxis: {
                categories: circulationDatasets.monthly.categories,
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            grid: {
                strokeDashArray: 4,
                borderColor: '#f1f5f9'
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: val => val + " " + "{{ __('books') }}"
                }
            }
        };

        const chartEl = document.querySelector("#borrowTrendsChart");
        if (chartEl) {
            borrowLineChartInstance = new ApexCharts(chartEl, lineOptions);
            borrowLineChartInstance.render();
        }

        // Donut Chart (Categories)
        const donutOptions = {
            series: {!! json_encode($categoryCounts) !!},
            labels: {!! json_encode(array_map(fn($l) => __($l), $categoryLabels)) !!},
            chart: {
                type: 'donut',
                height: 280,
                fontFamily: 'inherit'
            },
            colors: ['#1E3A8A', '#6366F1', '#10B981', '#F59E0B', '#3B82F6'],
            legend: {
                position: 'bottom',
                fontSize: '11px'
            },
            dataLabels: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: '{{ __("Total Books") }}',
                                fontSize: '12px',
                                color: '#64748b',
                                formatter: () => '{{ $totalBooks }}'
                            }
                        }
                    }
                }
            }
        };

        const donutChartEl = document.querySelector("#categoryDonutChart");
        if (donutChartEl) {
            const donutChart = new ApexCharts(donutChartEl, donutOptions);
            donutChart.render();
        }
    });
</script>
@endpush
