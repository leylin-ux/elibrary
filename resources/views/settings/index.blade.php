@extends('layouts.app')

@section('title', __('System Settings'))

@section('content')
<div class="space-y-6" x-data="{ 
    activeTab: '{{ $activeTab }}',
    addUserModalOpen: false,
    editUserModalOpen: false,
    editingUser: { id: null, name: '', email: '', role: 'member', member_type: 'Student', card_id: '', status: 'Active' },
    openEditUser(u) {
        this.editingUser = Object.assign({}, u);
        this.editUserModalOpen = true;
    }
}">

    @if($layoutIsAdmin)
        <!-- ========================================================================= -->
        <!-- ADMIN VIEW: 4 CORE SETTINGS SECTIONS & RBAC MANAGEMENT -->
        <!-- ========================================================================= -->
        
        <!-- Header & Section Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl lg:text-3xl font-bold tracking-tight text-[#1E3A8A]">{{ __('System Settings') }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    {{ __('Configure library rules, loan limits, fine rates, and notification preferences.') }}
                </p>
            </div>

            @if($layoutUser?->isSuperAdmin())
                <a href="{{ route('backups.index') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200/70 font-bold text-xs shadow-xs transition-all">
                    <span>💾</span>
                    <span>{{ __('Manage Drive D Backups') }}</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                </a>
            @endif
        </div>

        <!-- 4 Section Interactive Tabs -->
        <div class="bg-white rounded-2xl p-1.5 border border-slate-100 shadow-xs flex items-center flex-wrap gap-1">
            <!-- Tab 1: Library Info -->
            <button @click="activeTab = 'general'" 
                    :class="activeTab === 'general' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span>1. {{ __('Library Information') }}</span>
            </button>

            <!-- Tab 2: Users & Roles -->
            <button @click="activeTab = 'users'" 
                    :class="activeTab === 'users' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>2. {{ __('Users & Role Management') }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-bold" :class="activeTab === 'users' ? 'bg-blue-800 text-white' : 'bg-slate-100 text-slate-600'">{{ $roleCounts['total'] }}</span>
            </button>

            <!-- Tab 3: Notification Settings -->
            <button @click="activeTab = 'notifications'" 
                    :class="activeTab === 'notifications' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span>3. {{ __('Notification Settings') }}</span>
            </button>

            <!-- Tab 4: System Policies & Rules -->
            <button @click="activeTab = 'system'" 
                    :class="activeTab === 'system' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>4. {{ __('Circulation Policies & Rules') }}</span>
            </button>

            <!-- Tab 5: My Profile & Personal Settings -->
            <button @click="activeTab = 'my_profile'" 
                    :class="activeTab === 'my_profile' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>5. {{ __('My Profile & Personal Settings') }}</span>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 1: ព័ត៌មានបណ្ណាល័យ (LIBRARY INFORMATION) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'general'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Form (2 cols) -->
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span>🏛️ {{ __('Library Profile & Contact Info') }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Official library name, campus location, opening hours, and contact details.') }}
                        </p>
                    </div>

                    <form action="{{ route('settings.general.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        
                        <!-- Library Names (EN & KM) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Library Name (English)') }} *</label>
                                <input type="text" name="library_name" value="{{ old('library_name', $settings['general']['library_name'] ?? '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Library Name (Khmer)') }}</label>
                                <input type="text" name="library_name_km" value="{{ old('library_name_km', $settings['general']['library_name_km'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                        </div>

                        <!-- Taglines (EN & KM) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Slogan / Tagline (English)') }}</label>
                                <input type="text" name="library_tagline" value="{{ old('library_tagline', $settings['general']['library_tagline'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Slogan / Tagline (Khmer)') }}</label>
                                <input type="text" name="library_tagline_km" value="{{ old('library_tagline_km', $settings['general']['library_tagline_km'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                        </div>

                        <!-- Contact Email & Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Contact Email') }} *</label>
                                <input type="email" name="library_email" value="{{ old('library_email', $settings['general']['library_email'] ?? '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Contact Phone') }} *</label>
                                <input type="text" name="library_phone" value="{{ old('library_phone', $settings['general']['library_phone'] ?? '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                        </div>

                        <!-- Physical Addresses (EN & KM) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Physical Address (English)') }} *</label>
                                <textarea name="library_address" rows="2" required
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">{{ old('library_address', $settings['general']['library_address'] ?? '') }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Physical Address (Khmer)') }}</label>
                                <textarea name="library_address_km" rows="2"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">{{ old('library_address_km', $settings['general']['library_address_km'] ?? '') }}</textarea>
                            </div>
                        </div>

                        <!-- Operating Hours (EN & KM) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Operating Hours (English)') }} *</label>
                                <input type="text" name="library_hours" value="{{ old('library_hours', $settings['general']['library_hours'] ?? '') }}" required
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Operating Hours (Khmer)') }}</label>
                                <input type="text" name="library_hours_km" value="{{ old('library_hours_km', $settings['general']['library_hours_km'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                        </div>

                        <!-- Website & Logo Image URL / Upload -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Official Website URL') }}</label>
                                <input type="url" name="library_website" value="{{ old('library_website', $settings['general']['library_website'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Library Logo URL') }}</label>
                                <input type="text" name="library_logo" value="{{ old('library_logo', $settings['general']['library_logo'] ?? '') }}"
                                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="submit" 
                                    class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-900/20 transition-all">
                                {{ __('Save Library Information') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Live Badge Preview -->
                <div class="space-y-6">
                    <div class="bg-gradient-to-br from-[#1E3A8A] via-[#1e3272] to-[#6366F1] rounded-3xl p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[300px]">
                        <div class="absolute -right-12 -bottom-12 w-52 h-52 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                        <div>
                            <div class="flex items-center gap-3">
                                <img src="{{ $settings['general']['library_logo'] ?? 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?w=150&auto=format&fit=crop&q=80' }}" 
                                     alt="Logo" class="w-12 h-12 rounded-2xl object-cover ring-2 ring-white/30 shadow-md">
                                <div>
                                    <h3 class="font-black text-lg tracking-tight leading-tight">
                                        {{ app()->getLocale() === 'km' && !empty($settings['general']['library_name_km']) ? $settings['general']['library_name_km'] : ($settings['general']['library_name'] ?? 'E-Library') }}
                                    </h3>
                                    <p class="text-[11px] text-blue-200">
                                        {{ app()->getLocale() === 'km' && !empty($settings['general']['library_tagline_km']) ? $settings['general']['library_tagline_km'] : ($settings['general']['library_tagline'] ?? 'Academic Management System') }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 space-y-2.5 text-xs text-blue-100">
                                <div class="flex items-start gap-2">
                                    <span class="opacity-75">📍</span>
                                    <span class="text-[11px] leading-relaxed">
                                        {{ app()->getLocale() === 'km' && !empty($settings['general']['library_address_km']) ? $settings['general']['library_address_km'] : ($settings['general']['library_address'] ?? 'Phnom Penh, Cambodia') }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="opacity-75">📞</span>
                                    <span class="font-mono text-[11px]">{{ $settings['general']['library_phone'] ?? '012 889 900' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="opacity-75">✉️</span>
                                    <span class="font-mono text-[11px]">{{ $settings['general']['library_email'] ?? 'contact@elibrary.edu.kh' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="opacity-75">🕒</span>
                                    <span class="text-[11px]">
                                        {{ app()->getLocale() === 'km' && !empty($settings['general']['library_hours_km']) ? $settings['general']['library_hours_km'] : ($settings['general']['library_hours'] ?? 'Mon - Fri: 7:30 - 18:00') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/15 flex items-center justify-between text-[11px] text-blue-200">
                            <span>{{ __('Live Badge Preview') }}</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold border border-emerald-400/30">
                                ✓ {{ __('Online Catalog') }}
                            </span>
                        </div>
                    </div>

                    <!-- Quick Information Notes -->
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs space-y-3 text-xs text-slate-600">
                        <h4 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-[#1E3A8A]"></span>
                            {{ __('System Integration Note') }}
                        </h4>
                        <p class="text-slate-500 leading-relaxed">
                            {{ __('Information updated here is dynamically rendered on printed receipt slips, borrower digital cards, and notification emails.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 2: គ្រប់គ្រងអ្នកប្រើប្រាស់ និងតួនាទី (USERS & ROLES MANAGEMENT) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'users'" class="space-y-6">
            <!-- Summary KPI metrics -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3.5">
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __('Total Accounts') }}</span>
                    <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ number_format($roleCounts['total']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-indigo-100 shadow-xs">
                    <span class="text-[11px] font-bold text-indigo-500 uppercase tracking-wider">👑 {{ __('Super Admins') }}</span>
                    <p class="text-2xl font-extrabold text-indigo-700 mt-1">{{ number_format($roleCounts['admin']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-blue-100 shadow-xs">
                    <span class="text-[11px] font-bold text-blue-500 uppercase tracking-wider">👔 {{ __('Managers') }}</span>
                    <p class="text-2xl font-extrabold text-[#1E3A8A] mt-1">{{ number_format($roleCounts['manager'] ?? 0) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-emerald-100 shadow-xs">
                    <span class="text-[11px] font-bold text-emerald-500 uppercase tracking-wider">🎓 {{ __('Members') }}</span>
                    <p class="text-2xl font-extrabold text-[#10B981] mt-1">{{ number_format($roleCounts['member']) }}</p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-rose-100 shadow-xs col-span-2 sm:col-span-1">
                    <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">🚫 {{ __('Suspended') }}</span>
                    <p class="text-2xl font-extrabold text-[#EF4444] mt-1">{{ number_format($roleCounts['suspended']) }}</p>
                </div>
            </div>

            <!-- Users & Roles Main Card -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-6">
                <!-- Action Header Bar -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span>👥 {{ __('User & Role Directory') }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Manage user accounts, assign administrative roles, and inspect permission privileges.') }}
                        </p>
                    </div>

                    <div class="flex items-center flex-wrap gap-2.5">
                        <!-- Add User Modal Button -->
                        <button @click="addUserModalOpen = true" 
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-900/20 transition-all">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            <span>{{ __('Add New Staff / User') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Filters & Search Form -->
                <form action="{{ route('settings.index') }}" method="GET" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <input type="hidden" name="tab" value="users">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search_user" value="{{ request('search_user') }}" 
                               placeholder="{{ __('Search users by name, email, card ID...') }}"
                               class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none text-xs">
                    </div>

                    <!-- Role & Status Dropdown Filters -->
                    <div class="flex items-center gap-2">
                        <select name="role_filter" onchange="this.form.submit()" 
                                class="px-3 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white font-medium outline-none">
                            <option value="all" {{ request('role_filter') === 'all' ? 'selected' : '' }}>{{ __('All Roles') }}</option>
                            <option value="admin" {{ request('role_filter') === 'admin' ? 'selected' : '' }}>👑 {{ __('Super Admin') }}</option>
                            <option value="manager" {{ request('role_filter') === 'manager' ? 'selected' : '' }}>👔 {{ __('Manager') }}</option>
                            <option value="member" {{ request('role_filter') === 'member' ? 'selected' : '' }}>🎓 {{ __('Member') }}</option>
                        </select>

                        <select name="status_filter" onchange="this.form.submit()" 
                                class="px-3 py-2 rounded-xl border border-slate-200 text-slate-700 bg-white font-medium outline-none">
                            <option value="all" {{ request('status_filter') === 'all' || !request('status_filter') ? 'selected' : '' }}>{{ __('All Status') }}</option>
                            <option value="Active" {{ request('status_filter') === 'Active' ? 'selected' : '' }}>🟢 {{ __('Active') }}</option>
                            <option value="Temporary" {{ request('status_filter') === 'Temporary' ? 'selected' : '' }}>🟡 {{ __('Temporary') }}</option>
                            <option value="Suspended" {{ request('status_filter') === 'Suspended' ? 'selected' : '' }}>🔴 {{ __('Suspended') }}</option>
                            <option value="pending_login" {{ request('status_filter') === 'pending_login' ? 'selected' : '' }}>⏳ {{ app()->getLocale() === 'km' ? 'គណនីមិនទាន់ Login (' . ($roleCounts['pending_login'] ?? 0) . ')' : 'Pending First Login (' . ($roleCounts['pending_login'] ?? 0) . ')' }}</option>
                        </select>

                        @if(request('search_user') || request('role_filter') || request('status_filter'))
                            <a href="{{ route('settings.index', ['tab' => 'users']) }}" 
                                class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition-colors">
                                {{ __('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>

                <!-- Users Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                <th class="py-3 px-4">{{ __('User') }}</th>
                                <th class="py-3 px-4">{{ __('Card ID') }}</th>
                                <th class="py-3 px-4">{{ __('Role') }}</th>
                                <th class="py-3 px-4">{{ __('Member Type') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('Active Loans') }}</th>
                                <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                                <th class="py-3 px-4 text-right">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                            @forelse($users as $patron)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $patron->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                                 alt="{{ $patron->name }}" 
                                                 class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200 shrink-0">
                                            <div>
                                                <p class="font-bold text-slate-800">{{ $patron->name }}</p>
                                                <p class="text-xs text-slate-400">{{ $patron->email }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 font-mono text-xs font-semibold text-slate-600">
                                        {{ $patron->card_id ?? '-' }}
                                    </td>

                                    <!-- Role Badge -->
                                    <td class="py-3.5 px-4">
                                        @if($patron->role === 'admin')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/60">
                                                👑 {{ __('Super Admin') }}
                                            </span>
                                        @elseif($patron->role === 'manager')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#1E3A8A] border border-blue-200/60">
                                                👔 {{ __('Manager') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                🎓 {{ __('Member') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-xs text-slate-600">
                                        {{ __($patron->member_type ?? 'Student') }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center font-bold text-[#6366F1]">
                                        {{ $patron->active_loans_count }}
                                    </td>

                                    <td class="py-3.5 px-4 text-center">
                                        @if($patron->role === 'member' && !$patron->last_login_at)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/70" title="{{ __('Account has not logged in yet') }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                {{ app()->getLocale() === 'km' ? 'មិនទាន់ Login' : 'Pending Login' }}
                                            </span>
                                        @elseif($patron->status === 'Active')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981]">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></span>
                                                {{ __('Active') }}
                                            </span>
                                        @elseif($patron->status === 'Temporary')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></span>
                                                {{ __('Temporary') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-[#EF4444] border border-red-200/50">
                                                <span class="w-1.5 h-1.5 rounded-full bg-[#EF4444]"></span>
                                                {{ __('Suspended') }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button @click="openEditUser({{ json_encode($patron) }})"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-[#1E3A8A] bg-blue-50 hover:bg-blue-100 transition-colors">
                                                {{ __('Change Role') }}
                                            </button>
                                            @if($patron->role === 'admin')
                                                <span class="p-1 text-slate-400 cursor-not-allowed" title="{{ __('Super Admin is strictly protected from deletion') }}">
                                                    🔒
                                                </span>
                                            @else
                                                <form action="{{ route('settings.users.delete', $patron) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Are you sure you want to permanently delete user :name?', ['name' => $patron->name]) }}');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="{{ __('Delete User') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400 text-sm">{{ __('No users found matching query.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                @if($users->hasPages())
                    <div class="pt-4 border-t border-slate-100">
                        {{ $users->links() }}
                    </div>
                @endif

                <!-- RBAC Permissions Matrix Reference Table -->
                <div class="pt-8 border-t border-slate-100">
                    <div class="mb-4">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span>🛡️ {{ __('Role Permissions Matrix') }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ __('Detailed capability matrix across roles in the library system.') }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                    <th class="py-3 px-4">{{ __('Module / Capability') }}</th>
                                    <th class="py-3 px-4 text-center">👑 {{ __('Super Admin') }}</th>
                                    <th class="py-3 px-4 text-center">👔 {{ __('Manager') }}</th>
                                    <th class="py-3 px-4 text-center">🎓 {{ __('Member') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs">
                                @foreach($permissionsMatrix as $perm)
                                    <tr class="hover:bg-slate-50/40 transition-colors">
                                        <td class="py-3 px-4">
                                            <p class="font-bold text-slate-800">
                                                {{ app()->getLocale() === 'km' ? $perm['module_km'] : $perm['module'] }}
                                            </p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">
                                                {{ app()->getLocale() === 'km' ? $perm['description_km'] : $perm['description'] }}
                                            </p>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">✓</span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if(($perm['manager'] ?? false) === true)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">✓</span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if($perm['member'] === true)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs">✓</span>
                                            @elseif($perm['member'] === false)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-slate-400 text-xs">-</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold">{{ $perm['member'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 3: ការកំណត់ការជូនដំណឹង (NOTIFICATION SETTINGS) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'notifications'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs max-w-3xl">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>🔔 {{ __('Alerts & Notifications') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Configure automatic overdue reminders, email notices, and broadcast banners.') }}
                    </p>
                </div>

                <form action="{{ route('settings.notifications.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Notification Toggles Group -->
                    <div class="space-y-4">
                        <!-- 1. Email Notifications -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Email Notifications') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Send automated email updates for holds and returns.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_email_notifications" value="1" 
                                       {{ ($settings['notifications']['enable_email_notifications'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>

                        <!-- 2. New Reservation Request Alert -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('New Reservation Alert') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Notify library administrators instantly when a patron reserves a book.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_new_reservation" value="1" 
                                       {{ ($settings['notifications']['notify_new_reservation'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>

                        <!-- 3. Overdue Loan Reminder -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Overdue Loan Reminder') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Automatically flag and notify patrons before and after their due dates.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_overdue_loans" value="1" 
                                       {{ ($settings['notifications']['notify_overdue_loans'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>

                        <!-- 4. Reminder Timing (Days before due date) -->
                        <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Reminder Timing (Days Before Due)') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('How many days in advance to send initial reminder.') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="number" name="reminder_days_before_due" min="1" max="7" 
                                       value="{{ $settings['notifications']['reminder_days_before_due'] ?? '2' }}" 
                                       class="w-20 px-3 py-1.5 rounded-xl border border-slate-200 text-sm font-bold text-center outline-none">
                                <span class="text-xs text-slate-500 font-medium">{{ __('days before') }}</span>
                            </div>
                        </div>

                        <!-- 5. Daily Fine Notices -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Daily Fine Notices') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Send recurring daily penalty calculation notices to defaulters.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="notify_daily_fine" value="1" 
                                       {{ ($settings['notifications']['notify_daily_fine'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>

                        <!-- 6. Circulation Sound Alerts -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Circulation Sound Alerts') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Play audible chime sounds during book scan, checkout, and returns.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="enable_sound_alerts" value="1" 
                                       {{ ($settings['notifications']['enable_sound_alerts'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Broadcast Announcement Banner -->
                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <div>
                            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                                <span>📢 {{ __('Broadcast Announcement Banner') }}</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ __('Display general notice to all members and students on their portal dashboard.') }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="enable_broadcast_banner" name="enable_broadcast_banner" value="1"
                                   {{ ($settings['notifications']['enable_broadcast_banner'] ?? '1') == '1' ? 'checked' : '' }}
                                   class="rounded border-slate-300 text-[#1E3A8A] focus:ring-[#1E3A8A]">
                            <label for="enable_broadcast_banner" class="text-xs font-semibold text-slate-700">
                                {{ __('Show Announcement Banner on Dashboard') }}
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Broadcast Announcement (English)') }}</label>
                                <textarea name="broadcast_announcement" rows="3"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">{{ $settings['notifications']['broadcast_announcement'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Broadcast Announcement (Khmer)') }}</label>
                                <textarea name="broadcast_announcement_km" rows="3"
                                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">{{ $settings['notifications']['broadcast_announcement_km'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-900/20 transition-all">
                            {{ __('Save Notification Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 4: ការកំណត់ប្រព័ន្ធ (CIRCULATION POLICIES & RULES) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'system'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs max-w-4xl">
                <div class="border-b border-slate-100 pb-4 mb-6">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>⚖️ {{ __('Circulation Policies & Rules') }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Configure loan periods, concurrent borrowing limits, fines, and hold windows.') }}
                    </p>
                </div>

                <form action="{{ route('settings.system.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Borrowing Quotas and Durations -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Standard Loan Period (Days)') }} *</label>
                            <input type="number" name="standard_loan_days" min="1" max="90" 
                                   value="{{ old('standard_loan_days', $settings['system']['standard_loan_days'] ?? 14) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Standard checkout window') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Daily Overdue Fine Rate ($ USD)') }} *</label>
                            <input type="number" step="0.05" min="0" max="20" name="daily_fine_rate" 
                                   value="{{ old('daily_fine_rate', $settings['system']['daily_fine_rate'] ?? '0.50') }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Applied daily after grace period (USD)') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Daily Overdue Fine Rate (៛ KHR)') }} *</label>
                            <input type="number" step="100" min="0" max="50000" name="daily_fine_rate_khr" 
                                   value="{{ old('daily_fine_rate_khr', $settings['system']['daily_fine_rate_khr'] ?? 2000) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none font-mono">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Standard Khmer Riel rate (e.g. 500 ៛ or 2,000 ៛/day)') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Grace Period (Days)') }} *</label>
                            <input type="number" min="0" max="14" name="grace_period_days" 
                                   value="{{ old('grace_period_days', $settings['system']['grace_period_days'] ?? 1) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Days before fine accumulates') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Maximum Books per Student') }} *</label>
                            <input type="number" min="1" max="20" name="max_books_student" 
                                   value="{{ old('max_books_student', $settings['system']['max_books_student'] ?? 3) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Student loan quota limit') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Maximum Books per Lecturer') }} *</label>
                            <input type="number" min="1" max="50" name="max_books_teacher" 
                                   value="{{ old('max_books_teacher', $settings['system']['max_books_teacher'] ?? 5) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Teacher & staff loan quota') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Reservation Pickup Window (Hours)') }} *</label>
                            <input type="number" min="1" max="168" name="hold_pickup_window_hours" 
                                   value="{{ old('hold_pickup_window_hours', $settings['system']['hold_pickup_window_hours'] ?? 48) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                            <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Hold deadline before auto-cancel') }}</span>
                        </div>
                    </div>

                    <!-- Digital PDF & System Toggles -->
                    <div class="pt-6 border-t border-slate-100 space-y-4">
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/70 border border-slate-100">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Allow Real PDF Downloads') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Permit registered patrons to download full PDF documents to their devices.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="allow_pdf_downloads" value="1" 
                                       {{ ($settings['system']['allow_pdf_downloads'] ?? '1') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                            </label>
                        </div>

                        <!-- System Language Selector -->
                        <div class="p-4 rounded-2xl bg-slate-50/70 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ __('Default System Language') }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ __('Primary language when new sessions or guests access the catalog.') }}</p>
                            </div>
                            <select name="default_language" 
                                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white outline-none">
                                <option value="km" {{ ($settings['system']['default_language'] ?? 'km') === 'km' ? 'selected' : '' }}>🇰🇭 ភាសាខ្មែរ (Khmer)</option>
                                <option value="en" {{ ($settings['system']['default_language'] ?? 'km') === 'en' ? 'selected' : '' }}>🇬🇧 English</option>
                            </select>
                        </div>

                        <!-- Maintenance Mode Toggle -->
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-red-50/40 border border-red-100">
                            <div>
                                <p class="text-sm font-bold text-red-800">{{ __('System Maintenance Mode') }}</p>
                                <p class="text-xs text-red-600/80 mt-0.5">{{ __('Temporarily disable patron catalog searches and reservations for maintenance.') }}</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="maintenance_mode" value="1" 
                                       {{ ($settings['system']['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"></div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-900/20 transition-all">
                            {{ __('Save System Settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION 5: ព័ត៌មានគណនី & ការកំណត់ផ្ទាល់ខ្លួន (MY PROFILE & PERSONAL SETTINGS) -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'my_profile'" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-6">
                    @include('settings.partials.personal-profile', ['user' => $user])
                    @include('settings.partials.change-password', ['user' => $user])
                </div>
                <div class="space-y-6">
                    @include('settings.partials.personal-preferences', ['user' => $user])
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 1: ADD NEW STAFF / USER MODAL -->
        <!-- ========================================================================= -->
        <div x-show="addUserModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-slate-100 space-y-5"
                 @click.away="addUserModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span>👤 {{ __('Create New Staff Account') }}</span>
                    </h3>
                    <button @click="addUserModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        ✕
                    </button>
                </div>

                <form action="{{ route('settings.users.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Email Address') }} *</label>
                            <input type="email" name="email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Temporary Password') }} *</label>
                            <input type="password" name="password" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Role Privilege') }} *</label>
                            <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                                <option value="manager">👔 {{ __('Manager (ប្រធានបណ្ណាល័យ)') }}</option>
                                @if($layoutUser?->isSuperAdmin())
                                    <option value="admin">👑 {{ __('Super Admin (អភិបាលប្រព័ន្ធ)') }}</option>
                                @endif
                                <option value="member">🎓 {{ __('Member (សមាជិក)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Member Type') }} *</label>
                            <select name="member_type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                                <option value="Student">{{ __('Student') }}</option>
                                <option value="Teacher">{{ __('Teacher') }}</option>
                                <option value="General">{{ __('General') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Card ID (Optional)') }}</label>
                            <input type="text" name="card_id" placeholder="Auto-generated if blank" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Account Status') }} *</label>
                            <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                                <option value="Active">🟢 {{ __('Active') }}</option>
                                <option value="Temporary">🟡 {{ __('Temporary') }}</option>
                                <option value="Suspended">🔴 {{ __('Suspended') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="addUserModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">
                            {{ __('Create Account') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: EDIT USER ROLE MODAL -->
        <!-- ========================================================================= -->
        <div x-show="editUserModalOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-slate-100 space-y-5"
                 @click.away="editUserModalOpen = false">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span>⚙️ {{ __('Update Role') }} - <span x-text="editingUser.name"></span></span>
                    </h3>
                    <button @click="editUserModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        ✕
                    </button>
                </div>

                <form :action="'{{ url('settings/users') }}/' + editingUser.id + '/role'" method="POST" class="space-y-4">
                    @csrf

                    <!-- Super Admin Protection Notice -->
                    <template x-if="editingUser.role === 'admin' && !{{ $layoutUser?->isSuperAdmin() ? 'true' : 'false' }}">
                        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200 text-xs text-amber-900 flex items-center gap-2">
                            <span class="text-base">🔒</span>
                            <span>{{ __('Super Admin accounts are strictly protected and cannot be modified by Manager.') }}</span>
                        </div>
                    </template>

                    <div x-show="editingUser.role !== 'admin' || {{ $layoutUser?->isSuperAdmin() ? 'true' : 'false' }}">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Assigned Role') }} *</label>
                        <select name="role" x-model="editingUser.role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                            @if($layoutUser?->isSuperAdmin())
                                <option value="admin">👑 {{ __('Super Admin') }}</option>
                            @endif
                            <option value="manager">👔 {{ __('Manager') }}</option>
                            <option value="member">🎓 {{ __('Member') }}</option>
                        </select>
                    </div>

                    <div x-show="editingUser.role !== 'admin' || {{ $layoutUser?->isSuperAdmin() ? 'true' : 'false' }}">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Account Status') }} *</label>
                        <select name="status" x-model="editingUser.status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                            <option value="Active">🟢 {{ __('Active') }}</option>
                            <option value="Temporary">🟡 {{ __('Temporary') }}</option>
                            <option value="Suspended">🔴 {{ __('Suspended') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Membership Card ID') }}</label>
                        <input type="text" name="card_id" x-model="editingUser.card_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm font-mono outline-none focus:ring-2 focus:ring-[#1E3A8A]/20">
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="editUserModalOpen = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @else
        <!-- ========================================================================= -->
        <!-- MEMBER / STUDENT VIEW: DIGITAL CARD, PROFILE, PASSWORD & PREFERENCES -->
        <!-- ========================================================================= -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">{{ __('My Patron Profile & Settings') }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('View your digital card, update personal info, change password, and customize preferences.') }}</p>
        </div>

        <!-- Member Interactive Tabs -->
        <div class="bg-white rounded-2xl p-1.5 border border-slate-100 shadow-xs flex items-center flex-wrap gap-1">
            <button @click="activeTab = 'card'" 
                    :class="activeTab === 'card' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <span>🪪 {{ __('Digital Membership Card') }}</span>
            </button>
            <button @click="activeTab = 'profile'" 
                    :class="activeTab === 'profile' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <span>👤 {{ __('Personal Account Information') }}</span>
            </button>
            <button @click="activeTab = 'password'" 
                    :class="activeTab === 'password' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <span>🔒 {{ __('Change Password') }}</span>
            </button>
            <button @click="activeTab = 'preferences'" 
                    :class="activeTab === 'preferences' ? 'bg-[#1E3A8A] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex-1 sm:flex-initial flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                <span>⚙️ {{ __('Personal Settings & Preferences') }}</span>
            </button>
        </div>

        <!-- Tab 1: Digital Membership Card & Rules -->
        <div x-show="activeTab === 'card'" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Digital Member Card Mockup -->
                <div class="md:col-span-2 bg-gradient-to-br from-[#1E3A8A] via-[#1e3272] to-[#6366F1] rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden flex flex-col justify-between min-h-[260px]">
                    <div class="absolute -right-12 -bottom-12 w-56 h-56 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center font-bold text-white shadow-md">
                                🏛️
                            </div>
                            <div>
                                <h3 class="font-black tracking-tight text-lg">
                                    {{ app()->getLocale() === 'km' && !empty($settings['general']['library_name_km']) ? $settings['general']['library_name_km'] : ($settings['general']['library_name'] ?? 'E-Library') }}
                                </h3>
                                <span class="text-[11px] text-blue-200 uppercase tracking-wider font-semibold">{{ __('Digital Library Card') }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                            {{ __('Active Borrowing Privileges') }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 my-6 relative z-10">
                        <div class="flex items-center gap-4">
                            <img src="{{ $user?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80' }}" 
                                 alt="Member Avatar" 
                                 class="w-16 h-16 rounded-2xl object-cover ring-2 ring-white/40 shadow-lg">
                            <div>
                                <h4 class="text-xl font-bold text-white">{{ $user?->name }}</h4>
                                <p class="text-xs text-blue-200">{{ $user?->email }}</p>
                                <span class="inline-block mt-1 text-[11px] px-2.5 py-0.5 rounded-md bg-white/15 font-semibold text-white">
                                    🎓 {{ __($user?->member_type ?? 'Member') }}
                                </span>
                            </div>
                        </div>

                        <button @click="activeTab = 'profile'" 
                                class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white/20 hover:bg-white/30 text-white backdrop-blur-md transition-all">
                            <span>✏️ {{ __('Edit Profile') }}</span>
                        </button>
                    </div>

                    <div class="pt-4 border-t border-white/15 flex items-center justify-between relative z-10">
                        <div>
                            <span class="text-[10px] uppercase text-blue-200 font-bold tracking-wider block">{{ __('Membership Card ID') }}</span>
                            <span class="font-mono text-base font-bold tracking-widest">{{ $user?->card_id ?? 'STU-2026-001' }}</span>
                        </div>
                        <div class="font-mono text-xs opacity-60 tracking-widest select-none">
                            ||||| | |||| ||| |||| |
                        </div>
                    </div>
                </div>

                <!-- Rules & Policy Summary Card -->
                <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs space-y-4">
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#10B981]"></span>
                        {{ __('Member Borrowing Rules') }}
                    </h4>

                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block mb-0.5">📚 {{ __('Borrowing Quota') }}</span>
                            <span class="text-slate-500">{{ __('Max') }} {{ $settings['system']['max_books_student'] ?? 3 }} {{ __('books concurrent') }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block mb-0.5">⏳ {{ __('Loan Period Window') }}</span>
                            <span class="text-slate-500">{{ $settings['system']['standard_loan_days'] ?? 14 }} {{ __('Days per checkout') }}</span>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="font-bold text-slate-700 block mb-0.5">⚠️ {{ __('Overdue Policy') }}</span>
                            <span class="text-slate-500">${{ $settings['system']['daily_fine_rate'] ?? '0.50' }} {{ __('per day after grace period') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Personal Account Information -->
        <div x-show="activeTab === 'profile'" class="space-y-6">
            @include('settings.partials.personal-profile', ['user' => $user])
        </div>

        <!-- Tab 3: Change Password -->
        <div x-show="activeTab === 'password'" class="space-y-6">
            @include('settings.partials.change-password', ['user' => $user])
        </div>

        <!-- Tab 4: Personal Settings & Preferences -->
        <div x-show="activeTab === 'preferences'" class="space-y-6">
            @include('settings.partials.personal-preferences', ['user' => $user])
        </div>
    @endif

</div>
@endsection
