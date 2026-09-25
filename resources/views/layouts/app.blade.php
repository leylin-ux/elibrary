<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-[#F8FAFC]">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'E-Library') }} - @yield('title', 'Smart Management System')</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts: Inter / Plus Jakarta Sans & Kantumruy Pro for Khmer -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kantumruy+Pro:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] {
            display: none !important;
        }
        body {
            font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif;
        }
        .custom-sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 9999px;
        }
        .custom-sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.4);
        }
    </style>
    @stack('styles')
</head>
<body class="h-full antialiased text-slate-800 bg-[#F8FAFC]" x-data="{ mobileSidebarOpen: false }">
@php
    $layoutUser = auth()->user() ?? \App\Models\User::where('role', 'admin')->first();
    $layoutIsAdmin = $layoutUser ? $layoutUser->isAdmin() : true;
@endphp
    <div class="min-h-screen flex">
        <!-- Backdrop for mobile -->
        <div x-show="mobileSidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-40 lg:hidden"
             @click="mobileSidebarOpen = false"
             x-cloak>
        </div>

        <!-- Sidebar (#1E3A8A) -->
        <aside :class="mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-72 h-screen flex-shrink-0 bg-[#1E3A8A] text-white flex flex-col justify-between transition-transform duration-300 ease-in-out shadow-2xl lg:shadow-none lg:sticky lg:top-0">
            
            <!-- Top brand header -->
            <div class="flex-shrink-0 flex items-center justify-between px-6 py-5 border-b border-blue-800/60">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 group">
                    <div class="w-12 h-12 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-lg shadow-blue-950/30 group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="E-Library Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-tight text-white">
                            E-Library
                        </span>
                        @if($layoutUser?->isSuperAdmin())
                            <span class="text-xs text-blue-200 block font-normal">{{ __('Super Administrator') }}</span>
                        @elseif($layoutUser?->isManager())
                            <span class="text-xs text-blue-200 block font-normal">{{ __('Library Manager (ប្រធានបណ្ណាល័យ)') }}</span>
                        @endif
                    </div>
                </a>
                <button @click="mobileSidebarOpen = false" class="lg:hidden text-blue-200 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation links (Scrollable area) -->
            <nav class="flex-1 min-h-0 px-4 py-3 space-y-1.5 overflow-y-auto custom-sidebar-scroll">
                <p class="px-3 text-[11px] font-semibold text-blue-300 uppercase tracking-wider mb-2">
                    {{ $layoutIsAdmin ? __('Administration & Operations') : __('Student Services') }}
                </p>
                
                @if($layoutIsAdmin)
                    <!-- Admin: Global Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>{{ __('Dashboard Overview') }}</span>
                    </a>

                    <!-- Admin: Books Management -->
                    <a href="{{ route('books.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('books.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>{{ __('Books Management') }}</span>
                    </a>


                    <!-- Admin: Members Management -->
                    <a href="{{ route('members.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('members.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span>{{ __('Members') }}</span>
                    </a>

                    <!-- Admin: Circulation Desk -->
                    <a href="{{ route('borrows.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('borrows.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span>{{ __('Circulation Desk') }}</span>
                    </a>

                    <!-- Admin: Reservations Desk -->
                    <a href="{{ route('reservations.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('reservations.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ __('Reservations') }}</span>
                    </a>

                    <p class="px-3 text-[11px] font-semibold text-blue-300 uppercase tracking-wider pt-3 mb-2">{{ __('Management') }}</p>

                    <!-- Admin: Reports & Analytics -->
                    <a href="{{ route('reports.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('reports.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <span>{{ __('Reports & Analytics') }}</span>
                    </a>

                    <!-- Admin: System Settings -->
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>{{ __('System Settings') }}</span>
                    </a>

                    @if($layoutUser?->isSuperAdmin())
                        <!-- Super Admin: Data Backup & Recovery to Drive D -->
                        <a href="{{ route('backups.index') }}" 
                           class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('backups.*') ? 'bg-[#6366F1] text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <span class="flex-1">{{ __('Data Backup & Recovery') }}</span>
                        </a>
                    @endif
                @else
                    <!-- Student / Member: My Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('dashboard') ? 'bg-[#10B981] text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        <span>{{ __('My Dashboard') }}</span>
                    </a>

                    <!-- Student / Member: Catalog & Read PDF -->
                    <a href="{{ route('books.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('books.*') ? 'bg-[#10B981] text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>{{ __('Browse Library Catalog & E-Books') }}</span>
                    </a>


                    <!-- Student / Member: My Active Loans & History -->
                    <a href="{{ route('borrows.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('borrows.*') ? 'bg-[#10B981] text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        <span>{{ __('My Loans & History') }}</span>
                    </a>

                    <!-- Student / Member: My Reservations -->
                    <a href="{{ route('reservations.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('reservations.*') ? 'bg-[#10B981] text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ __('My Reservations') }}</span>
                    </a>

                    <p class="px-3 text-[11px] font-semibold text-blue-300 uppercase tracking-wider pt-3 mb-2">{{ __('Patron Services') }}</p>

                    <!-- Student / Member: My Patron Profile & Rules -->
                    <a href="{{ route('settings.index') }}" 
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('settings.*') ? 'bg-[#10B981] text-white shadow-lg shadow-emerald-600/30 font-semibold' : 'text-blue-100 hover:bg-blue-800/50 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>{{ __('My Patron Profile & Settings') }}</span>
                    </a>
                @endif
            </nav>

            <!-- Bottom User Profile & Logout (#1E3A8A footer - permanently pinned) -->
            <div class="flex-shrink-0 p-4 border-t border-blue-800/60 bg-blue-950/60">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ $layoutUser?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80' }}" 
                             alt="Avatar" 
                             class="w-10 h-10 rounded-xl object-cover ring-2 ring-indigo-400/40">
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-semibold text-white truncate">{{ $layoutUser?->name ?? __('Administrator') }}</h4>
                            <span class="text-xs text-blue-300 capitalize flex items-center gap-1">
                                @if($layoutUser?->isSuperAdmin())
                                    <span>👑</span> {{ __('Super Admin') }}
                                @elseif($layoutUser?->isManager())
                                    <span>👔</span> {{ __('ប្រធានគ្រប់គ្រង (Manager)') }}
                                @else
                                    <span>🎓</span> {{ __($layoutUser?->member_type ?? 'Member') }} &bull; <span class="font-mono text-[10px] text-emerald-300 font-bold">{{ $layoutUser?->card_id ?? 'STU-ID' }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Logout button -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                title="{{ __('Sign Out') }}"
                                class="p-2 text-blue-200 hover:text-red-400 hover:bg-blue-900/60 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>


        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 bg-[#F8FAFC]">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-4 sm:px-8 py-3.5 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger button -->
                    <button @click="mobileSidebarOpen = true" class="lg:hidden p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Mobile Search Button -->
                    <a href="{{ route('books.index') }}" class="sm:hidden p-2 text-slate-500 hover:text-slate-800 rounded-lg hover:bg-slate-100" title="{{ __('Search books...') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </a>

                    <!-- Quick Search Bar (Books Search with Live Autocomplete Dropdown & Direct Submit) -->
                    <div class="relative hidden sm:block w-72 lg:w-84"
                         x-data="{
                             query: '{{ request('search') }}',
                             results: [],
                             loading: false,
                             open: false,
                             fetchResults() {
                                 const q = this.query.trim();
                                 if (q.length < 1) {
                                     this.results = [];
                                     this.open = false;
                                     return;
                                 }
                                 this.loading = true;
                                 this.open = true;
                                 fetch('{{ route('books.quick-search') }}?q=' + encodeURIComponent(q))
                                     .then(res => res.json())
                                     .then(data => {
                                         this.results = data;
                                         this.loading = false;
                                     })
                                     .catch(() => {
                                         this.loading = false;
                                     });
                             },
                             clear() {
                                 this.query = '';
                                 this.results = [];
                                 this.open = false;
                                 this.$refs.topSearchInput.focus();
                             }
                         }"
                         @click.away="open = false"
                         @keydown.window.prevent.cmd.k="$refs.topSearchInput.focus()"
                         @keydown.window.prevent.ctrl.k="$refs.topSearchInput.focus()">

                        <form action="{{ route('books.index') }}" method="GET" class="relative m-0">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search"
                                   x-ref="topSearchInput"
                                   x-model="query"
                                   @input.debounce.250ms="fetchResults()"
                                   @focus="if(query.trim().length >= 1) open = true"
                                   @keydown.escape="open = false"
                                   placeholder="{{ __('Search books...') }}" 
                                   autocomplete="off"
                                   class="w-full pl-9 pr-9 py-2 text-xs lg:text-sm bg-slate-100/80 border border-slate-200 rounded-xl focus:bg-white focus:border-[#6366F1] focus:ring-2 focus:ring-indigo-100 transition-all outline-none">
                            
                            <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center">
                                <button type="button" 
                                        x-show="query.length > 0" 
                                        @click="clear()" 
                                        class="text-slate-400 hover:text-slate-600 p-0.5 rounded-full" 
                                        title="{{ __('Clear') }}" 
                                        x-cloak>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </form>

                        <!-- Live Autocomplete Dropdown -->
                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-1"
                             class="absolute left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 overflow-hidden"
                             x-cloak>
                            
                            <!-- Loading state -->
                            <div x-show="loading" class="px-4 py-3 text-xs text-slate-400 flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-[#6366F1]" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ __('Searching...') }}</span>
                            </div>

                            <!-- Results List -->
                            <template x-if="!loading && results.length > 0">
                                <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
                                    <template x-for="item in results" :key="item.id">
                                        <a :href="item.url" 
                                           class="flex items-center gap-3 px-3.5 py-2.5 hover:bg-slate-50 transition-colors group">
                                            <img :src="item.cover_url" 
                                                 class="w-8 h-11 object-cover rounded-md shadow-2xs shrink-0 border border-slate-100">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-xs font-bold text-slate-800 truncate group-hover:text-[#6366F1] transition-colors" x-text="item.title"></h4>
                                                <p class="text-[11px] text-slate-400 truncate" x-text="item.author"></p>
                                                <div class="flex items-center gap-2 mt-0.5">
                                                    <span class="text-[9px] px-1.5 py-0.2 rounded font-medium" 
                                                          :class="item.available ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'" 
                                                          x-text="item.available ? '{{ __('Available') }}' : '{{ __('Borrowed Out') }}'"></span>
                                                    <span x-show="item.category" class="text-[9px] text-slate-400 truncate" x-text="item.category"></span>
                                                </div>
                                            </div>
                                            <svg class="w-4 h-4 text-slate-300 group-hover:text-[#6366F1] transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- No results state -->
                            <template x-if="!loading && results.length === 0 && query.trim().length > 0">
                                <div class="px-4 py-4 text-center">
                                    <p class="text-xs text-slate-500 font-medium">{{ __('No books found.') }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Press Enter to search catalog') }}</p>
                                </div>
                            </template>

                            <!-- Bottom View All in Catalog Link -->
                            <div class="pt-1.5 mt-1 border-t border-slate-100 px-3">
                                <a :href="'{{ route('books.index') }}?search=' + encodeURIComponent(query)"
                                   class="w-full py-1.5 px-2 rounded-lg bg-indigo-50/70 hover:bg-indigo-100/70 text-[#6366F1] text-[11px] font-semibold flex items-center justify-between transition-colors">
                                    <span>{{ __('View all matching books in catalog') }}</span>
                                    <span>↵ Enter</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right topbar actions -->
                <div class="flex items-center gap-2 sm:gap-3">

                    <!-- Language Switcher (🇰🇭 ខ្មែរ / 🇬🇧 English) -->
                    <div class="relative" x-data="{ langOpen: false }">
                        <button @click="langOpen = !langOpen" 
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-semibold transition-colors">
                            @if(app()->getLocale() === 'km')
                                <span class="text-sm">🇰🇭</span>
                                <span>ខ្មែរ</span>
                            @else
                                <span class="text-sm">🇬🇧</span>
                                <span>English</span>
                            @endif
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="langOpen" 
                             @click.away="langOpen = false" 
                             class="absolute right-0 mt-2 w-36 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 overflow-hidden"
                             x-cloak>
                            <a href="{{ route('lang.switch', 'km') }}" 
                               class="flex items-center justify-between px-3.5 py-2 text-xs font-medium hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'km' ? 'text-[#6366F1] font-bold bg-indigo-50/50' : 'text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <span class="text-base">🇰🇭</span> ភាសាខ្មែរ
                                </span>
                                @if(app()->getLocale() === 'km')
                                    <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </a>
                            <a href="{{ route('lang.switch', 'en') }}" 
                               class="flex items-center justify-between px-3.5 py-2 text-xs font-medium hover:bg-slate-50 transition-colors {{ app()->getLocale() === 'en' ? 'text-[#6366F1] font-bold bg-indigo-50/50' : 'text-slate-700' }}">
                                <span class="flex items-center gap-2">
                                    <span class="text-base">🇬🇧</span> English
                                </span>
                                @if(app()->getLocale() === 'en')
                                    <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- Date badge -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <span>{{ now()->format('D, d M Y') }}</span>
                    </div>

                    <!-- Notification icon (សារដំណឹង & ការកក់) -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="relative p-2 text-slate-600 hover:text-slate-900 rounded-xl hover:bg-slate-100 transition-colors"
                                title="{{ __('Notifications & Messages') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            <!-- Badge red with dynamic count -->
                            @php
                                $badgeCount = $layoutIsAdmin 
                                    ? (($unreadNotificationsCount ?? 0) > 0 ? $unreadNotificationsCount : ($pendingReservationsCount ?? 0))
                                    : ($unreadNotificationsCount ?? 0);
                            @endphp
                            <span id="navbar-notification-badge" 
                                  class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 bg-[#EF4444] text-white text-[10px] font-bold rounded-full flex items-center justify-center ring-2 ring-white animate-pulse {{ $badgeCount > 0 ? '' : 'hidden' }}">
                                {{ $badgeCount > 99 ? '99+' : $badgeCount }}
                            </span>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false" 
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-slate-100 py-3 z-50 overflow-hidden"
                             x-cloak>
                            <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-800">{{ __('Notifications & Messages') }}</span>
                                    @if($badgeCount > 0)
                                        <span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold">
                                            {{ $badgeCount }} {{ __('New') }}
                                        </span>
                                    @endif
                                </div>
                                @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                                    <form action="{{ route('notifications.clear-all') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" 
                                                class="text-xs text-rose-600 hover:text-rose-800 hover:bg-rose-50 px-2.5 py-1 rounded-lg font-semibold flex items-center gap-1.5 transition-colors cursor-pointer border border-rose-200/60" 
                                                title="{{ __('Clear all notifications') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span>{{ __('Clear') }}</span>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                                @if(isset($adminNotifications) && $adminNotifications->count() > 0)
                                    @foreach($adminNotifications as $notif)
                                        <div class="group relative flex items-center justify-between hover:bg-slate-50 transition-colors {{ !$notif->is_read ? 'bg-indigo-50/40' : '' }}">
                                            <form action="{{ route('notifications.read', $notif) }}" method="POST" class="flex-1 min-w-0">
                                                @csrf
                                                <button type="submit" class="w-full text-left p-3.5 flex gap-3 cursor-pointer">
                                                    <div class="w-8 h-8 rounded-xl {{ in_array($notif->type, ['book_issued', 'book_issued_admin', 'reservation_approved']) ? 'bg-emerald-100 text-emerald-600' : ($notif->type === 'reservation_cancelled' ? 'bg-rose-100 text-rose-600' : (!$notif->is_read ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-500')) }} flex items-center justify-center shrink-0 text-sm">
                                                        @if($notif->type === 'reservation_cancelled')
                                                            ❌
                                                        @elseif($notif->type === 'reservation_request')
                                                            📥
                                                        @elseif($notif->type === 'reservation_approved')
                                                            🎉
                                                        @elseif($notif->type === 'book_issued')
                                                            📖
                                                        @elseif($notif->type === 'book_issued_admin')
                                                            ✅
                                                        @else
                                                            🔔
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0 pr-6">
                                                        <div class="flex items-center justify-between gap-1 mb-0.5">
                                                            <p class="font-bold text-xs text-slate-800 truncate">{{ $notif->title }}</p>
                                                            <span class="text-[10px] text-slate-400 shrink-0 font-mono">{{ $notif->created_at->diffForHumans(null, true) }}</span>
                                                        </div>
                                                        <p class="text-slate-600 text-xs line-clamp-2 leading-relaxed">{{ $notif->message }}</p>
                                                    </div>
                                                </button>
                                            </form>

                                            <!-- Single dismiss button (Clear item) -->
                                            <form action="{{ route('notifications.dismiss', $notif) }}" method="POST" class="absolute top-3 right-3 m-0 opacity-60 hover:opacity-100 transition-opacity">
                                                @csrf
                                                <button type="submit" 
                                                        class="p-1 text-slate-400 hover:text-rose-600 rounded-md hover:bg-rose-50 transition-colors cursor-pointer" 
                                                        title="{{ __('Clear') }}">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="py-8 text-center text-slate-400 text-xs">
                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                        <p>{{ __('No notifications or messages yet.') }}</p>
                                    </div>
                                @endif
                            </div>

                            @if($layoutIsAdmin)
                                <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">{{ __('Pending Holds') }}: <strong>{{ $pendingReservationsCount ?? 0 }}</strong></span>
                                    <a href="{{ route('reservations.index', ['status' => 'Pending']) }}" class="font-bold text-[#1E3A8A] hover:underline flex items-center gap-1">
                                        <span>{{ __('Review All Holds') }}</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors">
                            <img src="{{ auth()->user()?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80' }}" 
                                 class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200" 
                                 alt="Profile">
                            <span class="hidden sm:inline-block text-sm font-semibold text-slate-700 max-w-[120px] truncate">
                                {{ auth()->user()?->name ?? __('User') }}
                            </span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false" 
                             class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50"
                             x-cloak>
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs text-slate-400">{{ __('Signed in as') }}</p>
                                <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()?->email ?? 'admin@elibrary.com' }}</p>
                            </div>
                            <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>{{ __('Account Settings') }}</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs font-medium text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    <span>{{ __('Sign Out') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Alerts / Flash Messages -->
            <div class="px-4 sm:px-8 pt-4">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                            <span class="font-medium">{{ __(session('success')) }}</span>
                        </div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-800 text-lg leading-none">&times;</button>
                    </div>
                @endif

                @if(session('info'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-center justify-between p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-[#3B82F6]"></span>
                            <span class="font-medium">{{ __(session('info')) }}</span>
                        </div>
                        <button @click="show = false" class="text-blue-500 hover:text-blue-800 text-lg leading-none">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" class="mb-4 flex items-center justify-between p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span>
                            <span class="font-medium">{{ __(session('error')) }}</span>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-800 text-lg leading-none">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-xs">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-rose-600 mt-1 shrink-0"></span>
                                <div>
                                    <h4 class="font-bold text-rose-900 text-sm mb-1">{{ __('សូមពិនិត្យព័ត៌មានដែលបានបញ្ចូល៖') }}</h4>
                                    <ul class="list-disc list-inside space-y-0.5 text-rose-700">
                                        @foreach($errors->all() as $err)
                                            <li>{{ $err }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <button @click="show = false" class="text-rose-500 hover:text-rose-800 text-lg leading-none">&times;</button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="flex-1 px-4 sm:px-8 py-6">
                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="px-4 sm:px-8 py-4 border-t border-slate-200/80 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} {{ __('E-Library Smart Management System. Designed for Excellence.') }}
            </footer>
        </div>
    </div>

    <!-- Floating Toast Notification Container -->
    <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

    <script>
        // 1. Toast Notification Helper
        window.showToast = function(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            const isSuccess = type === 'success';
            toast.className = `pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-2xl shadow-xl text-xs font-medium border transition-all duration-300 transform translate-y-4 opacity-0 ${
                isSuccess ? 'bg-slate-900 text-white border-slate-800' : 'bg-rose-900 text-white border-rose-800'
            }`;
            toast.innerHTML = `
                <span class="w-2.5 h-2.5 rounded-full ${isSuccess ? 'bg-[#10B981]' : 'bg-[#EF4444]'} flex-shrink-0"></span>
                <span class="flex-1 leading-snug">${message}</span>
                <button type="button" class="text-slate-400 hover:text-white text-base leading-none ml-2">&times;</button>
            `;
            toast.querySelector('button').onclick = () => {
                toast.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => toast.remove(), 300);
            };
            container.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-4');
            });
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        };

        // 2. Global Scroll Position Preservation (prevents jumping to top on form submit or reload)
        (function() {
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }

            document.addEventListener('submit', function (e) {
                // If it's not handled via AJAX, remember scroll position
                if (!e.target.classList.contains('return-book-form') && !e.target.classList.contains('toggle-member-form')) {
                    sessionStorage.setItem('e_library_saved_scroll', window.scrollY);
                    sessionStorage.setItem('e_library_saved_path', window.location.pathname);
                }
            }, true);

            function restoreScroll() {
                const savedScroll = sessionStorage.getItem('e_library_saved_scroll');
                const savedPath = sessionStorage.getItem('e_library_saved_path');
                if (savedScroll !== null && savedPath === window.location.pathname) {
                    const y = parseInt(savedScroll, 10);
                    window.scrollTo(0, y);
                    requestAnimationFrame(() => window.scrollTo(0, y));
                    setTimeout(() => {
                        window.scrollTo(0, y);
                        sessionStorage.removeItem('e_library_saved_scroll');
                        sessionStorage.removeItem('e_library_saved_path');
                    }, 120);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', restoreScroll);
            } else {
                restoreScroll();
            }
            window.addEventListener('load', restoreScroll);
        })();

        // 3. Seamless In-Place AJAX Handlers (Zero Scroll Jump)
        document.addEventListener('DOMContentLoaded', function() {
            // A. Return Book Actions (Dashboard & Borrows table)
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.return-book-form');
                if (!form) return;
                e.preventDefault();

                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = `<span class="inline-flex items-center gap-1.5"><svg class="animate-spin h-3.5 w-3.5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> {{ __('Processing...') }}</span>`;

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        const tr = form.closest('tr');
                        if (tr) {
                            // Find status cell
                            const statusBadge = tr.querySelector('span.rounded-full');
                            if (statusBadge) {
                                statusBadge.className = 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50';
                                statusBadge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span> ${data.status_label || '{{ __("Returned") }}'}`;
                            }
                            // Replace action form with completed label
                            const actionTd = form.closest('td');
                            if (actionTd) {
                                actionTd.innerHTML = `<span class="text-xs text-slate-400 font-medium">{{ __('Completed') }}</span>`;
                            }
                        }
                    } else {
                        showToast(data.message || 'Error returning book', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    console.error('AJAX error, falling back to form submit', err);
                    form.submit();
                });
            });

            // B. Toggle Member Status (Cards view & Table view)
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.toggle-member-form');
                if (!form) return;
                e.preventDefault();

                const btn = form.querySelector('button[type="submit"]');
                const originalHtml = btn.innerHTML;
                btn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        btn.disabled = false;

                        // 1. Table view update
                        const tr = form.closest('[data-member-row]') || form.closest('tr');
                        if (tr) {
                            const statusTd = tr.querySelector('.member-status-td') || tr.querySelector('td:nth-child(5)');
                            if (statusTd) {
                                if (data.status === 'Active') {
                                    statusTd.innerHTML = `
                                        <span class="member-table-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                            ${data.status_label}
                                        </span>
                                    `;
                                    btn.className = 'member-toggle-btn px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60';
                                    btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span><span>${data.button_label}</span>`;
                                } else if (data.status === 'Temporary') {
                                    statusTd.innerHTML = `
                                        <span class="member-table-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></span>
                                            ${data.status_label}
                                        </span>
                                    `;
                                    btn.className = 'member-toggle-btn px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60';
                                    btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></span><span>${data.button_label}</span>`;
                                } else {
                                    statusTd.innerHTML = `
                                        <span class="member-table-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                            ${data.status_label}
                                        </span>
                                    `;
                                    btn.className = 'member-toggle-btn px-2.5 py-1 rounded-lg text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/50';
                                    btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span><span>${data.button_label}</span>`;
                                }
                            }
                        }

                        // 2. Card view update
                        const card = form.closest('[data-member-card]') || form.closest('.group.relative');
                        if (card) {
                            const badge = card.querySelector('.member-status-badge');
                            const bar = card.querySelector('.member-accent-bar');
                            const avatar = card.querySelector('.member-avatar');

                            if (data.status === 'Active') {
                                if (badge) {
                                    badge.className = 'member-status-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-[#10B981] border border-emerald-200/50';
                                    badge.innerText = data.status_label;
                                }
                                if (bar) {
                                    bar.className = 'member-accent-bar absolute top-0 inset-x-0 h-1.5 bg-[#10B981]';
                                }
                                if (avatar) {
                                    avatar.classList.remove('ring-red-400/40', 'ring-amber-400/40');
                                    avatar.classList.add('ring-emerald-400/40');
                                }
                                btn.className = 'member-toggle-btn px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60';
                                btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span><span>${data.button_label}</span>`;
                            } else if (data.status === 'Temporary') {
                                if (badge) {
                                    badge.className = 'member-status-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60';
                                    badge.innerText = data.status_label;
                                }
                                if (bar) {
                                    bar.className = 'member-accent-bar absolute top-0 inset-x-0 h-1.5 bg-[#F59E0B]';
                                }
                                if (avatar) {
                                    avatar.classList.remove('ring-emerald-400/40', 'ring-red-400/40');
                                    avatar.classList.add('ring-amber-400/40');
                                }
                                btn.className = 'member-toggle-btn px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60';
                                btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></span><span>${data.button_label}</span>`;
                            } else {
                                if (badge) {
                                    badge.className = 'member-status-badge px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-50 text-[#EF4444] border border-red-200/50';
                                    badge.innerText = data.status_label;
                                }
                                if (bar) {
                                    bar.className = 'member-accent-bar absolute top-0 inset-x-0 h-1.5 bg-[#EF4444]';
                                }
                                if (avatar) {
                                    avatar.classList.remove('ring-emerald-400/40', 'ring-amber-400/40');
                                    avatar.classList.add('ring-red-400/40');
                                }
                                btn.className = 'member-toggle-btn px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/50';
                                btn.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span><span>${data.button_label}</span>`;
                            }
                        }
                    } else {
                        showToast(data.message || 'Error updating member', 'error');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                })
                .catch(err => {
                    console.error('AJAX error, falling back to form submit', err);
                    form.submit();
                });
            });
        });
    </script>

    @if($layoutIsAdmin)
        <!-- Live Floating Pop-up Alert for Admin & Manager (លូតមកប្រាប់ Admin & Manager ភ្លាមៗ) -->
        <div x-data="adminNotificationWatcher()" 
             x-init="initWatcher()"
             class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 pointer-events-none"
             x-cloak>
            
            <template x-for="item in activeToasts" :key="item.id">
                <div x-show="item.visible"
                     x-transition:enter="transition ease-out duration-300 transform"
                     x-transition:enter-start="opacity-0 translate-y-6 scale-90"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-200 transform"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-3 scale-95"
                     class="pointer-events-auto w-84 sm:w-96 p-4 rounded-3xl bg-white border border-rose-200 shadow-2xl ring-4 ring-rose-500/10 flex items-start gap-3.5">
                    
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 text-lg shadow-xs">
                        <span x-text="item.type === 'reservation_cancelled' ? '❌' : '🔔'"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1 mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 px-2 py-0.5 rounded-md bg-rose-50 border border-rose-100">
                                {{ __('ការជូនដំណឹងបន្ទាន់') }}
                            </span>
                            <button @click="dismissToast(item.id)" class="text-slate-400 hover:text-slate-600 text-sm leading-none p-1 rounded-md hover:bg-slate-100 transition-colors cursor-pointer">
                                &times;
                            </button>
                        </div>
                        <h4 class="font-bold text-xs text-slate-900 leading-snug" x-text="item.title"></h4>
                        <p class="text-[11px] text-slate-600 mt-1 line-clamp-2 leading-relaxed" x-text="item.message"></p>
                        
                        <div class="mt-3 flex items-center gap-2">
                            <a :href="item.action_url || '{{ route('reservations.index', ['status' => 'Cancelled']) }}'" 
                               class="px-3 py-1.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-bold shadow-xs transition-all flex items-center gap-1.5">
                                <span>{{ __('ពិនិត្យមើល') }}</span>
                                <span>&rarr;</span>
                            </a>
                            <button type="button" @click="dismissToast(item.id)" class="px-2.5 py-1.5 text-xs font-medium text-slate-500 hover:text-slate-700 cursor-pointer">
                                {{ __('បិទ') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <script>
            function adminNotificationWatcher() {
                return {
                    activeToasts: [],
                    lastSeenIds: new Set(),
                    pollInterval: null,
                    initWatcher() {
                        @if(isset($adminNotifications))
                            @foreach($adminNotifications as $n)
                                this.lastSeenIds.add({{ $n->id }});
                            @endforeach
                        @endif

                        // Poll every 8 seconds for new unread notifications
                        this.pollInterval = setInterval(() => {
                            this.checkForNewNotifications();
                        }, 8000);
                    },
                    async checkForNewNotifications() {
                        try {
                            const res = await fetch('{{ route('notifications.unread-stream') }}', {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!res.ok) return;
                            const data = await res.json();
                            
                            // Update badge counter dynamically in navbar
                            const badgeEl = document.getElementById('navbar-notification-badge');
                            if (badgeEl) {
                                if (data.unreadCount > 0) {
                                    badgeEl.textContent = data.unreadCount > 99 ? '99+' : data.unreadCount;
                                    badgeEl.classList.remove('hidden');
                                } else {
                                    badgeEl.classList.add('hidden');
                                }
                            }

                            if (data.latest && data.latest.length > 0) {
                                data.latest.forEach(notif => {
                                    if (!this.lastSeenIds.has(notif.id)) {
                                        this.lastSeenIds.add(notif.id);
                                        this.triggerPopup(notif);
                                    }
                                });
                            }
                        } catch (e) {
                            // network fluctuations ignored
                        }
                    },
                    triggerPopup(notif) {
                        const toastItem = {
                            id: notif.id,
                            title: notif.title,
                            message: notif.message,
                            action_url: notif.action_url,
                            type: notif.type,
                            visible: true
                        };
                        this.activeToasts.unshift(toastItem);
                        this.playChime();

                        // Auto hide toast after 10 seconds
                        setTimeout(() => {
                            this.dismissToast(notif.id);
                        }, 10000);
                    },
                    dismissToast(id) {
                        const found = this.activeToasts.find(t => t.id === id);
                        if (found) {
                            found.visible = false;
                            setTimeout(() => {
                                this.activeToasts = this.activeToasts.filter(t => t.id !== id);
                            }, 300);
                        }
                    },
                    playChime() {
                        try {
                            const AudioContext = window.AudioContext || window.webkitAudioContext;
                            if (!AudioContext) return;
                            const ctx = new AudioContext();
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                            osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.15);
                            gain.gain.setValueAtTime(0.2, ctx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.5);
                        } catch(e) {}
                    }
                };
            }
        </script>
    @endif

    <!-- PDF.js Library for Client-Side PDF Cover Extraction and Viewing -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        window.extractPdfCover = async function(fileOrUrl, targetScale = 1.5) {
            if (!window.pdfjsLib) {
                throw new Error('PDF.js library is not loaded');
            }
            let loadingTask;
            if (typeof fileOrUrl === 'string') {
                loadingTask = window.pdfjsLib.getDocument({ url: fileOrUrl, withCredentials: false });
            } else {
                const arrayBuffer = await fileOrUrl.arrayBuffer();
                loadingTask = window.pdfjsLib.getDocument({ data: arrayBuffer });
            }
            const pdf = await loadingTask.promise;
            const page = await pdf.getPage(1);
            const viewport = page.getViewport({ scale: targetScale });

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({ canvasContext: context, viewport: viewport }).promise;
            return canvas.toDataURL('image/jpeg', 0.92);
        };
    </script>

    @stack('scripts')
</body>
</html>
