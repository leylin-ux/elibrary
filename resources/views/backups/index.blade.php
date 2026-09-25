@extends('layouts.app')

@section('title', __('Data Backup & Recovery'))

@section('content')
<div class="space-y-6" x-data="{
    backupLoading: false,
    confirmBackupModal: false,
    confirmRestoreModal: false,
    restoreTarget: { id: null, filename: '' },
    confirmDeleteModal: false,
    deleteTarget: { id: null, filename: '' },
    openRestore(id, filename) {
        this.restoreTarget = { id: id, filename: filename };
        this.confirmRestoreModal = true;
    },
    openDelete(id, filename) {
        this.deleteTarget = { id: id, filename: filename };
        this.confirmDeleteModal = true;
    },
    triggerBackupNow() {
        this.confirmBackupModal = false;
        this.backupLoading = true;
        document.getElementById('instant-backup-form').submit();
    }
}">

    <!-- Top Breadcrumb & Title Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-[#1E3A8A] flex items-center gap-2">
                <span>💾</span>
                <span>{{ __('Data Backup & Recovery') }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl leading-relaxed">
                {{ __('Safely backup all MySQL database records and uploaded book files directly to Server Drive D, with automatic scheduled midnight backups.') }}
            </p>
        </div>

        <!-- Quick Top Action -->
        <div class="flex items-center gap-3">
            <button @click="confirmBackupModal = true" 
                    :disabled="backupLoading"
                    class="relative inline-flex items-center justify-center gap-2.5 px-6 py-3 rounded-2xl text-sm font-bold text-white bg-[#1E3A8A] hover:bg-blue-900 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-blue-950/25 border border-blue-800/60 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                <template x-if="!backupLoading">
                    <svg class="w-5 h-5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                </template>
                <template x-if="backupLoading">
                    <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </template>
                <span x-text="backupLoading ? '{{ __('Processing backup to Drive D...') }}' : '{{ __('Backup Now') }}'"></span>
            </button>
        </div>
    </div>

    <!-- Hidden Form for Instant Backup Trigger -->
    <form id="instant-backup-form" action="{{ route('backups.run') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- 3 Key Metric Cards & Drive D Storage Widget -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        
        <!-- Widget 1: Server Drive D Storage Status -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base shadow-xs">
                            💾
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Server Drive D Storage') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Drive Storage Gauge') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Visual Bar Indicator -->
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ __('Used:') }} <strong class="text-slate-800">{{ $driveStats['used_formatted'] }}</strong> ({{ $driveStats['used_percentage'] }}%)</span>
                        <span class="text-emerald-600 font-semibold">{{ __('Free:') }} <strong>{{ $driveStats['free_formatted'] }}</strong></span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-gradient-to-r from-blue-500 via-indigo-500 to-indigo-600 rounded-full transition-all duration-500" 
                             style="width: {{ min(100, max(5, $driveStats['used_percentage'])) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400 pt-0.5">
                        <span>{{ __('Total Drive D Size:') }} {{ $driveStats['total_formatted'] }}</span>
                        <span>{{ __('Backup Files:') }} {{ $driveStats['backups_formatted'] }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('Destination Folder:') }}</span>
                <code class="px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-mono text-[11px] truncate max-w-[180px]" title="{{ $driveStats['destination_path'] }}">
                    {{ $driveStats['destination_path'] }}
                </code>
            </div>
        </div>

        <!-- Widget 2: Total Backups & Footprint -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base shadow-xs">
                            📦
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Total Backup Archives') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Storage Footprint & Files') }}</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                        {{ $totalBackupsCount }} {{ __('files') }}
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <span class="text-[11px] text-slate-400 block">{{ __('Successful') }}</span>
                        <span class="text-lg font-bold text-emerald-600">{{ $successfulCount }}</span>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <span class="text-[11px] text-slate-400 block">{{ __('Total Space Used') }}</span>
                        <span class="text-lg font-bold text-indigo-700">{{ $totalStorageFormatted }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('Archive Type:') }}</span>
                <span class="text-slate-700 font-medium">{{ __('Full ZIP (Database Dump & Book Media)') }}</span>
            </div>
        </div>

        <!-- Widget 3: Last Run & Next Schedule -->
        <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-xs flex flex-col justify-between space-y-4">
            <div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base shadow-xs">
                            ⏱️
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">{{ __('Schedule & Last Run Status') }}</h3>
                            <p class="text-xs text-slate-400">{{ __('Automated Midnight Routine') }}</p>
                        </div>
                    </div>
                    @if($settings['last_status'] === 'success')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('Successful') }}
                        </span>
                    @elseif($settings['last_status'] === 'failed')
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            {{ __('Failed') }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-50 text-slate-600 border border-slate-200">
                            {{ __('Not Run Yet') }}
                        </span>
                    @endif
                </div>

                <div class="mt-4 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ __('Last Execution:') }}</span>
                        <span class="font-bold text-slate-800">
                            {{ !empty($settings['last_run_at']) ? $settings['last_run_at'] : __('None') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ __('Last Run Type:') }}</span>
                        <span class="font-medium {{ $settings['last_type'] === 'auto' ? 'text-emerald-700 font-bold' : 'text-slate-700' }}">
                            {{ $settings['last_type'] === 'auto' ? __('Automatic (ស្វ័យប្រវត្តិ)') : __('Manual (ដោយដៃ)') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500">{{ __('Next Auto-Backup:') }}</span>
                        <span class="font-bold text-indigo-600">
                            {{ __('Tonight at 12:00 AM') }} (00:00)
                        </span>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-xs">
                <span class="text-slate-500">{{ __('Automation Engine:') }}</span>
                <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-600" title="{{ __('Smart Web Fallback + Laravel Scheduler Active') }}">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ __('Active (Smart Web + Scheduler)') }}</span>
                </span>
            </div>
        </div>

    </div>

    <!-- Main Section: Backups List & Filter -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        
        <!-- Table Top Header & Filters -->
        <div class="p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>🗄️</span>
                    <span>{{ __('Backup History on Drive D') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                        {{ $backups->total() }}
                    </span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">
                    {{ __('All backup archives currently stored on Server Drive D') }}
                </p>
            </div>

            <!-- Filters -->
            <form action="{{ route('backups.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="{{ __('Search file name...') }}" 
                           class="w-48 sm:w-60 pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>

                <!-- Type Filter -->
                <select name="type" 
                        onchange="this.form.submit()" 
                        class="px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-medium">
                    <option value="">{{ __('All Types') }}</option>
                    <option value="manual" {{ request('type') === 'manual' ? 'selected' : '' }}>{{ __('Manual (Super Admin)') }}</option>
                    <option value="auto" {{ request('type') === 'auto' ? 'selected' : '' }}>{{ __('Automatic 12:00 AM') }}</option>
                </select>

                @if(request('search') || request('type'))
                    <a href="{{ route('backups.index') }}" class="px-2.5 py-2 text-xs text-slate-500 hover:text-slate-800 rounded-xl hover:bg-slate-100 transition-all font-medium">
                        {{ __('Reset') }}
                    </a>
                @endif
            </form>
        </div>

        <!-- Table View -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="px-6 py-3.5">{{ __('Backup File Name') }}</th>
                        <th class="px-6 py-3.5">{{ __('Backup Type') }}</th>
                        <th class="px-6 py-3.5">{{ __('File Size') }}</th>
                        <th class="px-6 py-3.5">{{ __('Included Data') }}</th>
                        <th class="px-6 py-3.5">{{ __('Date & Time') }}</th>
                        <th class="px-6 py-3.5">{{ __('Status') }}</th>
                        <th class="px-6 py-3.5 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($backups as $b)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <!-- Filename & Disk Location -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-100/70 text-blue-700 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900 block font-mono text-[12px]">{{ $b->filename }}</span>
                                        <span class="text-[11px] text-slate-400 block font-mono truncate max-w-xs" title="{{ $b->disk_path }}">
                                            {{ $b->disk_path }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Type (Manual vs Auto) -->
                            <td class="px-6 py-4">
                                @if($b->backup_type === 'auto')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                        <span>⏰</span>
                                        <span>{{ __('Automatic 12:00 AM') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                        <span>👤</span>
                                        <span>{{ __('Manual (Super Admin)') }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- File Size -->
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-800 text-[12px]">{{ $b->formatted_size }}</span>
                            </td>

                            <!-- Content Details (Tables & Files) -->
                            <td class="px-6 py-4">
                                <div class="space-y-0.5 text-[11px]">
                                    <span class="text-slate-600 block">📊 <strong>{{ $b->tables_count }}</strong> {{ __('Database Tables') }}</span>
                                    <span class="text-slate-400 block">📁 <strong>{{ $b->files_count }}</strong> {{ __('Media & PDF Files') }}</span>
                                </div>
                            </td>

                            <!-- Created Date & Time -->
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800 block text-[11px]">
                                    {{ $b->created_at ? $b->created_at->format('d/m/Y h:i:s A') : '-' }}
                                </span>
                                <span class="text-[10px] text-slate-400 block">
                                    {{ $b->created_at ? $b->created_at->diffForHumans() : '' }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($b->status === 'success')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>{{ __('Successful') }}</span>
                                    </span>
                                @elseif($b->status === 'failed')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $b->error_message }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>{{ __('Failed') }}</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-spin"></span>
                                        <span>{{ __('Running...') }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    
                                    <!-- Download Button -->
                                    <a href="{{ route('backups.download', $b->id) }}" 
                                       title="{{ __('Download this backup archive to computer') }}"
                                       class="p-2 rounded-xl text-blue-600 hover:text-blue-800 hover:bg-blue-50 transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>

                                    <!-- Restore Button -->
                                    <button @click="openRestore({{ $b->id }}, '{{ $b->filename }}')" 
                                            type="button"
                                            title="{{ __('Restore database from this backup') }}"
                                            class="p-2 rounded-xl text-amber-600 hover:text-amber-800 hover:bg-amber-50 transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button @click="openDelete({{ $b->id }}, '{{ $b->filename }}')" 
                                            type="button"
                                            title="{{ __('Delete backup file from Drive D') }}"
                                            class="p-2 rounded-xl text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition-all cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">{{ __('No backup archives found on Drive D') }}</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    {{ __('Click "Backup Now" above to generate your first backup archive.') }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($backups->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $backups->links() }}
            </div>
        @endif

    </div>

    <!-- Configuration & Settings Card -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs">
        <div class="border-b border-slate-100 pb-4 mb-6">
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span>⚙️</span>
                <span>{{ __('Backup Configuration') }}</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">
                {{ __('Configure destination folder on Server Drive D and automated backup options.') }}
            </p>
        </div>

        <form action="{{ route('backups.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Target Disk Path -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Destination Path on Drive D') }}
                    </label>
                    <div class="relative">
                        <input type="text" 
                               name="backup_disk_path" 
                               value="{{ old('backup_disk_path', $settings['disk_path']) }}" 
                               required
                               class="w-full px-4 py-2.5 text-xs font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-semibold text-slate-800 bg-slate-50/50">
                    </div>
                    <p class="text-[11px] text-slate-400">
                        {{ __('Default is D:\E-Library-Backups (The system will automatically create this directory).') }}
                    </p>
                </div>

                <!-- Retention Days -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        {{ __('Retention Period (Days)') }}
                    </label>
                    <input type="number" 
                           name="backup_retention_days" 
                           value="{{ old('backup_retention_days', $settings['retention_days']) }}" 
                           min="1" 
                           max="365" 
                           required
                           class="w-full px-4 py-2.5 text-xs font-semibold rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-800">
                    <p class="text-[11px] text-slate-400">
                        {{ __('Old backup files older than these days will be automatically purged to save Drive D storage.') }}
                    </p>
                </div>
            </div>

            <!-- Toggles -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Auto Backup Toggle -->
                <label class="flex items-start gap-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/60 transition-all cursor-pointer">
                    <input type="checkbox" 
                           name="backup_auto_enabled" 
                           value="1" 
                           {{ $settings['auto_enabled'] ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">
                            {{ __('Enable Automatic Daily Backup at 12:00 AM (Midnight)') }}
                        </span>
                        <span class="block text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            {{ __('The system will automatically trigger a full backup every night at 00:00 via Task Scheduler.') }}
                        </span>
                    </div>
                </label>

                <!-- Include Files Toggle -->
                <label class="flex items-start gap-3.5 p-4 rounded-2xl border border-slate-200/80 hover:bg-slate-50/60 transition-all cursor-pointer">
                    <input type="checkbox" 
                           name="backup_include_files" 
                           value="1" 
                           {{ $settings['include_files'] ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="block text-xs font-bold text-slate-800">
                            {{ __('Include Book PDF Files and Images') }}
                        </span>
                        <span class="block text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            {{ __('Packages both MySQL database dump and all storage media files into the ZIP archive.') }}
                        </span>
                    </div>
                </label>

            </div>

            <div class="flex items-center justify-end pt-3">
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-800 text-white font-bold text-xs shadow-md shadow-blue-900/20 active:scale-95 transition-all">
                    {{ __('Save Configuration') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Automation & Scheduling Information Card -->
    <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/50 to-slate-50 rounded-3xl p-6 border border-blue-100/80 shadow-xs">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-2xl bg-[#1E3A8A] text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-950/20 text-lg">
                💡
            </div>
            <div class="space-y-2 flex-1">
                <h3 class="text-sm font-bold text-slate-800">
                    {{ __('ដំណើរការនៃការ Backup ដោយស្វ័យប្រវត្តិ (How Automated Backup Works)') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1 text-xs leading-relaxed text-slate-600">
                    <div class="p-3.5 rounded-2xl bg-white/90 border border-blue-100/70 shadow-xs space-y-1">
                        <div class="flex items-center gap-2 font-bold text-[#1E3A8A]">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ __('1. ស្វ័យប្រវត្តិតាម Web (Web Smart Fallback - សកម្ម)') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            {{ __('ប្រព័ន្ធមានមុខងារឆ្លាតវៃ៖ នៅពេល Admin ចូលប្រើប្រព័ន្ធ ឬចូលទំព័រ Backup ប្រព័ន្ធនឹងពិនិត្យមើលថាតើថ្ងៃនេះបាន backup រួចរាល់ឬនៅ។ ប្រសិនបើមិនទាន់បាន backup ប្រព័ន្ធនឹងធ្វើការ backup ទៅ Drive D ដោយស្វ័យប្រវត្តិភ្លាមៗ ដោយមិនបាច់បើក terminal ឡើយ។') }}
                        </p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-white/90 border border-blue-100/70 shadow-xs space-y-1">
                        <div class="flex items-center gap-2 font-bold text-[#1E3A8A]">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>{{ __('2. ស្វ័យប្រវត្តិតាម Windows Background (Task Scheduler)') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500">
                            {{ __('ដើម្បីឱ្យប្រព័ន្ធ Backup នៅម៉ោង 12:00 យប់ស្វ័យប្រវត្តិតាមនាឡិកា ទោះបីជាគ្មានអ្នកបើក Browser ក៏ដោយ អ្នកអាចដំណើរការ file ') }}
                            <code class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] text-slate-800 font-bold">setup-backup-scheduler.bat</code>
                            {{ __(' ឬបើក ') }}
                            <code class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-[10px] text-slate-800 font-bold">run-scheduler.bat</code>
                            {{ __(' ក្នុងថតគម្រោងបាន។') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 1: Confirm Instant Backup -->
    <div x-show="confirmBackupModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="confirmBackupModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-bold text-slate-800">{{ __('Confirm Instant Backup to Drive D') }}</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    {{ __('Are you sure you want to start backing up all database records and uploaded files to Server Drive D (:path) right now?', ['path' => $driveStats['destination_path']]) }}
                </p>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" 
                        @click="confirmBackupModal = false" 
                        class="flex-1 px-4 py-2.5 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                    {{ __('Cancel') }}
                </button>
                <button type="button" 
                        @click="triggerBackupNow()" 
                        class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-[#1E3A8A] hover:bg-blue-900 shadow-md shadow-blue-950/25 border border-blue-800/60 transition-all cursor-pointer">
                    {{ __('Confirm Backup Now') }}
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 2: Confirm Restore -->
    <div x-show="confirmRestoreModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="confirmRestoreModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-bold text-slate-800 text-amber-900">{{ __('Warning: Database Restore') }}</h3>
                <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                    {{ __('Are you sure you want to restore the database from the backup file:') }} <br>
                    <strong class="font-mono text-slate-800 text-[11px]" x-text="restoreTarget.filename"></strong> ?
                </p>
                <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200/60 text-[11px] text-amber-800 text-left">
                    ⚠️ <strong>{{ __('Important Note:') }}</strong> {{ __('All current library data will be completely overwritten and replaced with data from this backup file. Please proceed with caution.') }}
                </div>
            </div>
            <form :action="'{{ url('backups') }}/' + restoreTarget.id + '/restore'" method="POST" class="flex items-center justify-end gap-3 pt-3">
                @csrf
                <button type="button" 
                        @click="confirmRestoreModal = false" 
                        class="flex-1 px-4 py-2.5 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/30 transition-all">
                    {{ __('Confirm Database Restore') }}
                </button>
            </form>
        </div>
    </div>

    <!-- MODAL 3: Confirm Delete -->
    <div x-show="confirmDeleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.away="confirmDeleteModal = false" 
             class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 shadow-2xl border border-slate-100">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div class="text-center">
                <h3 class="text-base font-bold text-slate-800">{{ __('Delete Backup File from Drive D') }}</h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    {{ __('Are you sure you want to permanently delete the backup file:') }} <br>
                    <strong class="font-mono text-slate-800 text-[11px]" x-text="deleteTarget.filename"></strong> <br>
                    {{ __('from Server Drive D? This action cannot be undone.') }}
                </p>
            </div>
            <form :action="'{{ url('backups') }}/' + deleteTarget.id" method="POST" class="flex items-center justify-end gap-3 pt-3">
                @csrf
                @method('DELETE')
                <button type="button" 
                        @click="confirmDeleteModal = false" 
                        class="flex-1 px-4 py-2.5 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition-all">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" 
                        class="flex-1 px-4 py-2.5 text-xs font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/30 transition-all">
                    {{ __('Permanently Delete') }}
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
