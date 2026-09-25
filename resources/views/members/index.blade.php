@extends('layouts.app')

@section('title', __('Members Directory'))

@section('content')
<div class="space-y-6" x-data="{
    viewMode: 'cards',
    addModalOpen: false,
    editModalOpen: false,
    detailModalOpen: false,
    deleteModalOpen: false,
    memberToDelete: { id: '', name: '' },
    selectedMember: null,
    issueInitialBook: false,
    newMemberType: 'Student',
    currentMember: { 
        id: '', 
        name: '', 
        email: '', 
        card_id: '', 
        member_type: 'Student', 
        academic_year: '1',
        major: '',
        status: 'Active', 
        phone: '', 
        photo: '', 
        notes: '' 
    },
    openEdit(member) {
        this.currentMember = {
            id: member.id,
            name: member.name || '',
            email: member.email || '',
            card_id: member.card_id || '',
            member_type: member.member_type || 'Student',
            academic_year: member.academic_year || 1,
            major: member.major || '',
            status: member.status || 'Active',
            phone: member.phone || '',
            photo: member.photo || '',
            notes: member.notes || ''
        };
        this.detailModalOpen = false;
        this.editModalOpen = true;
    },
    openDetail(member) {
        this.selectedMember = member;
        this.detailModalOpen = true;
    },
    confirmDelete(member) {
        this.memberToDelete = { id: member.id, name: member.name };
        this.detailModalOpen = false;
        this.deleteModalOpen = true;
    }
}">

    <!-- Header Row -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">{{ __('Members Directory') }}</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ __('Manage student academic recommendations, member quotas, and late/lost fine policies.') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- View Toggle (Cards / Table) -->
            <div class="flex items-center bg-slate-200/80 p-1 rounded-xl border border-slate-200">
                <button @click="viewMode = 'cards'" 
                        :class="viewMode === 'cards' ? 'bg-white text-[#1E3A8A] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="p-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>{{ __('Cards') }}</span>
                </button>
                <button @click="viewMode = 'table'" 
                        :class="viewMode === 'table' ? 'bg-white text-[#1E3A8A] shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                        class="p-2 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                    <span>{{ __('Table') }}</span>
                </button>
            </div>

            <!-- Add Member Button -->
            <button @click="addModalOpen = true; newMemberType = 'Student'" 
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs sm:text-sm font-semibold shadow-md shadow-blue-950/20 transition-all">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                <span>{{ __('Add Member') }}</span>
            </button>
        </div>
    </div>

    <!-- Quick Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('members.index', array_merge(request()->except(['status', 'loan_filter', 'member_type', 'fine_status']), [])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ !request('status') && !request('loan_filter') && !request('member_type') && !request('fine_status') ? 'bg-[#1E3A8A] text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>{{ __('All') }} ({{ $counts['total'] }})</span>
        </a>

        <!-- Students -->
        <a href="{{ route('members.index', array_merge(request()->except(['member_type', 'fine_status']), ['member_type' => 'Student'])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ request('member_type') === 'Student' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>🎓 {{ __('Students (និស្សិត)') }} ({{ $counts['students'] }})</span>
        </a>

        <!-- Teachers -->
        <a href="{{ route('members.index', array_merge(request()->except(['member_type', 'fine_status']), ['member_type' => 'Teacher'])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ request('member_type') === 'Teacher' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>👨‍🏫 {{ __('Teachers (សាស្ត្រាចារ្យ)') }} ({{ $counts['teachers'] }})</span>
        </a>

        <!-- General External Members -->
        <a href="{{ route('members.index', array_merge(request()->except(['member_type', 'fine_status']), ['member_type' => 'General'])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ request('member_type') === 'General' && !request('fine_status') ? 'bg-amber-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span>👤 {{ __('General/External (សមាជិកក្រៅ)') }} ({{ $counts['general'] }})</span>
        </a>

        <!-- Members with Unpaid Fines -->
        <a href="{{ route('members.index', array_merge(request()->except(['fine_status', 'member_type']), ['fine_status' => 'unpaid'])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ request('fine_status') === 'unpaid' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span class="w-2 h-2 rounded-full bg-rose-300"></span>
            <span>🛑 {{ __('With Unpaid Fines (ជំពាក់ប្រាក់ពិន័យ)') }} ({{ $counts['with_unpaid_fines'] }})</span>
        </a>

        <!-- Active Loans Filter -->
        <a href="{{ route('members.index', array_merge(request()->except('loan_filter'), ['loan_filter' => 'active_loans'])) }}" 
           class="px-3.5 py-2 rounded-xl font-semibold transition-all flex items-center gap-2 shrink-0 {{ request('loan_filter') === 'active_loans' ? 'bg-[#6366F1] text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            <span class="w-2 h-2 rounded-full bg-indigo-300"></span>
            <span>{{ __('With Active Loans') }} ({{ $counts['active_loans'] }})</span>
        </a>
    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('members.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            @if(request('loan_filter'))
                <input type="hidden" name="loan_filter" value="{{ request('loan_filter') }}">
            @endif

            <div class="relative min-w-[240px] flex-1 sm:flex-initial">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="{{ __('Search name, Card ID, Major, Notes...') }}" 
                       class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#6366F1] outline-none">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <!-- Member Type Filter -->
            <select name="member_type" onchange="this.form.submit()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <option value="">{{ __('All Types') }}</option>
                <option value="Student" {{ request('member_type') === 'Student' ? 'selected' : '' }}>🎓 {{ __('Student') }}</option>
                <option value="Teacher" {{ request('member_type') === 'Teacher' ? 'selected' : '' }}>👨‍🏫 {{ __('Teacher') }}</option>
                <option value="General" {{ request('member_type') === 'General' ? 'selected' : '' }}>👤 {{ __('General / External') }}</option>
                <option value="Staff" {{ request('member_type') === 'Staff' ? 'selected' : '' }}>🏢 {{ __('Staff') }}</option>
            </select>

            <!-- Academic Year Filter -->
            <select name="academic_year" onchange="this.form.submit()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <option value="">{{ __('All Academic Years') }}</option>
                <option value="1" {{ request('academic_year') == '1' ? 'selected' : '' }}>{{ __('Year 1 (ឆ្នាំទី ១)') }}</option>
                <option value="2" {{ request('academic_year') == '2' ? 'selected' : '' }}>{{ __('Year 2 (ឆ្នាំទី ២)') }}</option>
                <option value="3" {{ request('academic_year') == '3' ? 'selected' : '' }}>{{ __('Year 3 (ឆ្នាំទី ៣)') }}</option>
                <option value="4" {{ request('academic_year') == '4' ? 'selected' : '' }}>{{ __('Year 4 (ឆ្នាំទី ៤)') }}</option>
            </select>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>🟢 {{ __('Active') }}</option>
                <option value="Temporary" {{ request('status') === 'Temporary' ? 'selected' : '' }}>🟡 {{ __('Temporary') }}</option>
                <option value="Suspended" {{ request('status') === 'Suspended' ? 'selected' : '' }}>🔴 {{ __('Suspended') }}</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-[#6366F1] text-white text-xs font-semibold rounded-xl hover:bg-indigo-600 transition-colors">{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'member_type', 'academic_year', 'status', 'loan_filter', 'fine_status']))
                <a href="{{ route('members.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">{{ __('Clear') }}</a>
            @endif
        </form>

        <span class="text-xs text-slate-400 shrink-0">{{ __('Total registered members') }}: {{ $members->total() }}</span>
    </div>

    <!-- 1. PROFILE CARDS VIEW -->
    <div x-show="viewMode === 'cards'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($members as $member)
            <div class="bg-white rounded-3xl border border-slate-100 shadow-xs hover:shadow-xl transition-all duration-300 p-6 flex flex-col justify-between group relative overflow-hidden">
                <!-- Top Status Accent Bar -->
                <div class="member-accent-bar absolute top-0 inset-x-0 h-1.5 {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}"></div>

                <div>
                    <!-- Header with Avatar, Name & Status Badge -->
                    <div class="flex items-start justify-between gap-4 cursor-pointer" @click="openDetail({{ json_encode($member) }})">
                        <div class="flex items-center gap-3.5">
                            <img src="{{ $member->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                 class="member-avatar w-14 h-14 rounded-2xl object-cover ring-2 {{ $member->status === 'Active' ? 'ring-emerald-400/40' : ($member->status === 'Temporary' ? 'ring-amber-400/40' : 'ring-red-400/40') }} shadow-xs" 
                                 alt="{{ $member->name }}">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base group-hover:text-[#1E3A8A] transition-colors line-clamp-1">{{ $member->name }}</h3>
                                <p class="text-xs text-slate-400 truncate max-w-[150px]">{{ $member->email }}</p>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        <span class="member-status-badge px-2.5 py-1 rounded-full text-[11px] font-bold shrink-0 {{ $member->status === 'Active' ? 'bg-emerald-50 text-[#10B981] border border-emerald-200/50' : ($member->status === 'Temporary' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-red-50 text-[#EF4444] border border-red-200/50') }}">
                            {{ $member->status === 'Active' ? __('Active') : ($member->status === 'Temporary' ? __('Temporary') : __('Suspended')) }}
                        </span>
                    </div>

                    <!-- Member Meta Box (Card ID, Type, Academic Year, Subscription, Borrow Counts) -->
                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Card ID') }}:</span>
                            <span class="font-mono font-bold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">
                                {{ $member->card_id ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Member Type') }}:</span>
                            <span class="px-2 py-0.5 rounded-md font-bold text-[11px] {{ $member->member_type === 'Student' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : ($member->member_type === 'Teacher' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-amber-50 text-amber-700 border border-amber-200') }}">
                                {{ __($member->member_type) }}
                            </span>
                        </div>

                        <!-- Academic Year or Subscription Validity -->
                        @if($member->isStudent())
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Academic Year') }}:</span>
                                <span class="font-bold text-indigo-700 bg-indigo-50/70 px-2 py-0.5 rounded border border-indigo-100">
                                    🎯 {{ $member->academic_year_label }}
                                </span>
                            </div>
                            @if($member->major)
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">{{ __('Major') }}:</span>
                                    <span class="font-medium text-slate-700 truncate max-w-[160px]" title="{{ $member->major }}">
                                        {{ $member->major }}
                                    </span>
                                </div>
                            @endif
                        @elseif($member->isTeacher() && $member->major)
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Department') }}:</span>
                                <span class="font-medium text-slate-700 truncate max-w-[160px]" title="{{ $member->major }}">
                                    {{ $member->major }}
                                </span>
                            </div>
                        @endif

                        @if($member->unpaidFinesTotal() > 0)
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/50">
                                <span class="text-slate-400">{{ __('Fines Due') }}:</span>
                                <span class="font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200 text-[11px] flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                    <span>${{ number_format($member->unpaidFinesTotal(), 2) }} ({{ __('Blocked') }})</span>
                                </span>
                            </div>
                        @endif

                        <!-- Borrow Counts Highlight -->
                        <div class="flex items-center justify-between pt-1.5 border-t border-slate-200/60">
                            <span class="text-slate-400">{{ __('Total Borrow Times') }}:</span>
                            <span class="font-bold text-slate-800 bg-blue-50 text-[#1E3A8A] px-2 py-0.5 rounded-md border border-blue-100">
                                {{ $member->total_borrows_count }} {{ __('times') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Currently Borrowed') }}:</span>
                            <span class="font-bold {{ $member->active_borrows_count > 0 ? 'text-indigo-600 font-black' : 'text-slate-600' }}">
                                {{ $member->active_borrows_count }} / {{ $member->maxAllowedLoans() }} {{ __('books limit') }}
                            </span>
                        </div>
                    </div>

                    <!-- Books Borrowed & Dates Preview (ថាបានខ្ចីសៀវភៅអ្វីខ្លះ និងកាលបរិច្ឆេទខ្ចី) -->
                    <div class="mt-3.5">
                        <span class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5 flex items-center justify-between">
                            <span>{{ __('Books Borrowed & Dates') }}</span>
                            <span class="text-[10px] text-slate-400 font-normal">{{ $member->borrows->count() }} {{ __('recorded') }}</span>
                        </span>

                        @if($member->borrows->isNotEmpty())
                            <div class="space-y-1.5">
                                @foreach($member->borrows->take(2) as $borrow)
                                    <div class="p-2 rounded-xl bg-white border border-slate-200/70 text-xs flex items-center justify-between gap-2 shadow-2xs">
                                        <div class="truncate max-w-[170px]">
                                            <span class="font-semibold text-slate-800 block truncate" title="{{ $borrow->book->title ?? 'N/A' }}">
                                                📖 {{ $borrow->book->title ?? 'Book #' . $borrow->book_id }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ __('Loan Date') }}: {{ \Carbon\Carbon::parse($borrow->borrow_date)->format('M d, Y') }}
                                            </span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $borrow->status === 'Returned' ? 'bg-slate-100 text-slate-600' : ($borrow->status === 'Overdue' ? 'bg-rose-50 text-rose-600 border border-rose-200' : 'bg-indigo-50 text-[#6366F1] border border-indigo-200') }}">
                                            {{ __($borrow->status) }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-2.5 px-3 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-slate-400 text-[11px]">
                                {{ __('No borrow history found for this member.') }}
                            </div>
                        @endif
                    </div>

                    <!-- Member Note Snippet (ព័ត៌មានលម្អិតសមាជិក៖ Note) -->
                    @if(!empty($member->notes))
                        <div class="mt-3 p-2 rounded-xl bg-amber-50/60 border border-amber-200/60 text-[11px] text-slate-600 italic truncate" title="{{ $member->notes }}">
                            📝 <span class="font-semibold text-amber-900">{{ __('Note') }}:</span> "{{ $member->notes }}"
                        </div>
                    @endif
                </div>

                <!-- Footer Actions -->
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                    <!-- Toggle Status Button -->
                    <form action="{{ route('members.toggle-status', $member) }}" method="POST" class="inline toggle-member-form">
                        @csrf
                        <button type="submit" 
                                class="member-toggle-btn px-2.5 py-1.5 rounded-xl text-xs font-semibold transition-all inline-flex items-center gap-1.5 {{ $member->status === 'Active' ? 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60' : ($member->status === 'Temporary' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200/60' : 'text-red-700 bg-red-50 hover:bg-red-100 border border-red-200/50') }}"
                                title="{{ __('Click to cycle status') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}"></span>
                            <span>{{ $member->status === 'Active' ? __('Active') : ($member->status === 'Temporary' ? __('Temporary') : __('Suspended')) }}</span>
                        </button>
                    </form>

                    <div class="flex items-center gap-1">
                        <!-- Quick View Details Modal Button (Eye) -->
                        <button type="button"
                                @click="openDetail({{ json_encode($member) }})"
                                class="p-1.5 text-slate-500 hover:text-[#1E3A8A] hover:bg-slate-100 rounded-lg transition-colors"
                                title="{{ __('View Details') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                        </button>

                        <!-- Edit Button (Pencil) -->
                        <button type="button"
                                @click="openEdit({{ json_encode($member) }})"
                                class="p-1.5 text-slate-500 hover:text-[#6366F1] hover:bg-slate-100 rounded-lg transition-colors"
                                title="{{ __('Edit Member') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        </button>

                        <!-- Delete Button (Trash) -->
                        <button type="button" 
                                @click="confirmDelete({{ json_encode($member) }})" 
                                class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-lg transition-colors" 
                                title="{{ __('Delete Member') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-slate-400 text-sm bg-white rounded-3xl border border-dashed border-slate-200">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <p class="font-semibold text-slate-700 mb-1">{{ __('No members found.') }}</p>
                <p class="text-xs text-slate-400">{{ __('Try clearing filters or search terms.') }}</p>
            </div>
        @endforelse
    </div>

    <!-- 2. TABLE VIEW -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden" x-cloak>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6">{{ __('Member') }}</th>
                        <th class="py-3.5 px-6">{{ __('Card ID') }}</th>
                        <th class="py-3.5 px-6">{{ __('Member Type') }}</th>
                        <th class="py-3.5 px-6">{{ __('Borrow Statistics') }}</th>
                        <th class="py-3.5 px-6">{{ __('Books Borrowed & Dates') }}</th>
                        <th class="py-3.5 px-6">{{ __('Status') }}</th>
                        <th class="py-3.5 px-6 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($members as $member)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-6 cursor-pointer" @click="openDetail({{ json_encode($member) }})">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $member->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80' }}" 
                                         class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200">
                                    <div>
                                        <p class="font-semibold text-slate-800 hover:text-[#1E3A8A] transition-colors">{{ $member->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $member->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 font-mono text-xs font-bold text-slate-700">
                                {{ $member->card_id ?? 'N/A' }}
                            </td>
                            <td class="py-3.5 px-6 text-xs font-semibold text-[#6366F1]">
                                {{ __($member->member_type) }}
                            </td>
                            <td class="py-3.5 px-6 text-xs">
                                <div class="space-y-0.5">
                                    <span class="font-bold text-[#1E3A8A] block">{{ $member->total_borrows_count }} {{ __('times borrowed') }}</span>
                                    <span class="text-slate-500 font-medium block">{{ $member->active_borrows_count }} {{ __('active loans') }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-6 text-xs">
                                @if($member->borrows->isNotEmpty())
                                    <div class="space-y-1 max-w-xs">
                                        @foreach($member->borrows->take(2) as $b)
                                            <div class="truncate text-slate-700">
                                                <span class="font-semibold truncate">📖 {{ $b->book->title ?? 'N/A' }}</span>
                                                <span class="text-slate-400 text-[10px] ml-1">({{ \Carbon\Carbon::parse($b->borrow_date)->format('M d') }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 text-xs italic">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $member->status === 'Active' ? 'bg-emerald-50 text-[#10B981] border border-emerald-200/50' : ($member->status === 'Temporary' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-red-50 text-[#EF4444] border border-red-200/50') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $member->status === 'Active' ? 'bg-[#10B981]' : ($member->status === 'Temporary' ? 'bg-[#F59E0B]' : 'bg-[#EF4444]') }}"></span>
                                    {{ $member->status === 'Active' ? __('Active') : ($member->status === 'Temporary' ? __('Temporary') : __('Suspended')) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button"
                                            @click="openDetail({{ json_encode($member) }})"
                                            class="p-1.5 text-slate-500 hover:text-[#1E3A8A] hover:bg-slate-100 rounded-lg transition-colors"
                                            title="{{ __('View Details') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </button>

                                    <button type="button"
                                            @click="openEdit({{ json_encode($member) }})"
                                            class="p-1.5 text-slate-500 hover:text-[#6366F1] hover:bg-slate-100 rounded-lg transition-colors"
                                            title="{{ __('Edit Member') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>

                                    <button type="button" 
                                            @click="confirmDelete({{ json_encode($member) }})"
                                            class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-slate-100 rounded-lg transition-colors"
                                            title="{{ __('Delete Member') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-8 text-slate-400 text-sm">{{ __('No members found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $members->links() }}
    </div>

    <!-- MODAL 1: VIEW MEMBER DETAILS & BORROWING HISTORY (ព័ត៌មានលម្អិត & ប្រវត្តិខ្ចីសៀវភៅ) -->
    <div x-show="detailModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="detailModalOpen = false">
            <template x-if="selectedMember">
                <div>
                    <!-- Header -->
                    <div class="flex items-start justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3.5">
                            <img :src="selectedMember.photo || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80'" 
                                 class="w-14 h-14 rounded-2xl object-cover ring-2 ring-slate-200">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900" x-text="selectedMember.name"></h2>
                                <p class="text-xs text-slate-500" x-text="selectedMember.email"></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-xs font-bold text-slate-700 bg-slate-100 px-2 py-0.5 rounded" x-text="selectedMember.card_id || 'No Card'"></span>
                                    <span class="text-xs font-semibold text-[#6366F1]" x-text="selectedMember.member_type"></span>
                                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full" 
                                          :class="selectedMember.status === 'Active' ? 'bg-emerald-50 text-emerald-700' : (selectedMember.status === 'Temporary' ? 'bg-amber-50 text-amber-700 border border-amber-200/60' : 'bg-red-50 text-red-700 border border-red-200/50')" 
                                          x-text="selectedMember.status === 'Active' ? '{{ __('Active') }}' : (selectedMember.status === 'Temporary' ? '{{ __('Temporary') }}' : '{{ __('Suspended') }}')"></span>
                                </div>
                            </div>
                        </div>
                        <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-xl hover:bg-slate-100">&times;</button>
                    </div>

                    <!-- Member Note Section (ព័ត៌មានលម្អិតសមាជិក៖ Note) -->
                    <div class="mt-4 p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-xs">
                        <span class="font-bold uppercase tracking-wider text-amber-900 block mb-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>{{ __('Member Notes') }}</span>
                        </span>
                        <p class="italic text-slate-700" x-text="selectedMember.notes || '{{ __('No administrative notes recorded for this member.') }}'"></p>
                    </div>

                    <!-- Statistics Pills -->
                    <div class="grid grid-cols-3 gap-3 my-4 text-center text-xs">
                        <div class="p-3 rounded-2xl bg-blue-50/60 border border-blue-100">
                            <span class="block text-[10px] uppercase font-bold text-blue-600">{{ __('Total Borrowed') }}</span>
                            <span class="text-lg font-black text-[#1E3A8A]" x-text="(selectedMember.total_borrows_count || 0) + ' {{ __('times') }}'"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                            <span class="block text-[10px] uppercase font-bold text-indigo-600">{{ __('Active Borrows') }}</span>
                            <span class="text-lg font-black text-[#6366F1]" x-text="(selectedMember.active_borrows_count || 0) + ' {{ __('books') }}'"></span>
                        </div>
                        <div class="p-3 rounded-2xl" :class="selectedMember.status === 'Active' ? 'bg-emerald-50/60 border border-emerald-100' : (selectedMember.status === 'Temporary' ? 'bg-amber-50/60 border border-amber-200/60' : 'bg-red-50/60 border border-red-200/50')">
                            <span class="block text-[10px] uppercase font-bold" :class="selectedMember.status === 'Active' ? 'text-emerald-600' : (selectedMember.status === 'Temporary' ? 'text-amber-700' : 'text-red-700')">{{ __('Status') }}</span>
                            <span class="text-base font-bold" :class="selectedMember.status === 'Active' ? 'text-emerald-700' : (selectedMember.status === 'Temporary' ? 'text-amber-700' : 'text-red-700')" x-text="selectedMember.status === 'Active' ? '{{ __('Active') }}' : (selectedMember.status === 'Temporary' ? '{{ __('Temporary') }}' : '{{ __('Suspended') }}')"></span>
                        </div>
                    </div>

                    <!-- Active Borrowed Books Report (របាយការណ៍សៀវភៅដែលកំពុងខ្ចី) -->
                    <div class="mt-5">
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2 flex items-center justify-between">
                            <span>{{ __('Active Borrowed Books Report') }}</span>
                            <span class="text-indigo-600" x-text="(selectedMember.active_borrows ? selectedMember.active_borrows.length : 0) + ' {{ __('active loans') }}'"></span>
                        </h3>

                        <template x-if="selectedMember.active_borrows && selectedMember.active_borrows.length > 0">
                            <div class="space-y-2 max-h-48 overflow-y-auto">
                                <template x-for="loan in selectedMember.active_borrows" :key="loan.id">
                                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-slate-800" x-text="loan.book ? loan.book.title : 'Book #' + loan.book_id"></p>
                                            <p class="text-slate-400 text-[11px]" x-text="'Loan Date: ' + (loan.borrow_date ? loan.borrow_date.substring(0, 10) : '') + ' | Due: ' + (loan.due_date ? loan.due_date.substring(0, 10) : '')"></p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" 
                                                  :class="loan.status === 'Overdue' ? 'bg-rose-100 text-rose-700' : 'bg-indigo-100 text-indigo-700'" 
                                                  x-text="loan.status"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="!selectedMember.active_borrows || selectedMember.active_borrows.length === 0">
                            <div class="py-4 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                                {{ __('No active loans currently.') }}
                            </div>
                        </template>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="flex items-center justify-between pt-6 mt-6 border-t border-slate-100">
                        <a :href="'/members/' + selectedMember.id" class="text-xs font-semibold text-[#1E3A8A] hover:underline">
                            {{ __('Full Details') }} &rarr;
                        </a>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="detailModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                                {{ __('Cancel') }}
                            </button>
                            <button type="button" @click="openEdit(selectedMember)" class="px-4 py-2 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-semibold shadow-md shadow-blue-950/20 transition-colors">
                                {{ __('Edit Member') }}
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 2: ADD NEW MEMBER & RECORD INITIAL BOOK LOAN (បន្ថែមសមាជិកថ្មី & ឱ្យខ្ចីសៀវភៅ) -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="addModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Add New Library Member') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Enter patron credentials and optionally issue their first book loan.') }}</p>
                </div>
                <button @click="addModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            <form action="{{ route('members.store') }}" method="POST" class="mt-5 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Full Name') }} *</label>
                        <input type="text" name="name" required placeholder="{{ __('e.g. Chan Samnang') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Email Address') }} *</label>
                        <input type="email" name="email" required placeholder="samnang@example.com" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Card ID / Student ID') }} *</label>
                        <input type="text" name="card_id" required value="LIB-2025-{{ rand(100, 999) }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Member Type') }} *</label>
                        <select name="member_type" x-model="newMemberType" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="Student">🎓 {{ __('Student') }}</option>
                            <option value="Teacher">👨‍🏫 {{ __('Teacher') }}</option>
                            <option value="General">👤 {{ __('General / External') }}</option>
                            <option value="Staff">🏢 {{ __('Staff') }}</option>
                        </select>
                    </div>

                    <!-- Conditional Student Fields: Academic Year & Major -->
                    <div x-show="newMemberType === 'Student'" class="sm:col-span-2 p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-3">
                        <span class="text-xs font-bold text-indigo-900 block">🎯 {{ __('Student Academic Information') }}</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Academic Year (ឆ្នាំសិក្សា)') }} *</label>
                                <select name="academic_year" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                                    <option value="1">ឆ្នាំទី ១ (Year 1)</option>
                                    <option value="2">ឆ្នាំទី ២ (Year 2)</option>
                                    <option value="3">ឆ្នាំទី ៣ (Year 3)</option>
                                    <option value="4">ឆ្នាំទី ៤ (Year 4)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">{{ __('Major / Department') }}</label>
                                <input type="text" name="major" placeholder="{{ __('e.g. វិទ្យាសាស្ត្រកុំព្យូទ័រ (Computer Science)') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                            </div>
                        </div>
                    </div>

                    <!-- Conditional Teacher Major -->
                    <div x-show="newMemberType === 'Teacher'" class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Faculty / Department') }}</label>
                        <input type="text" name="major" placeholder="{{ __('e.g. Faculty of Science & IT') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Phone Number') }}</label>
                        <input type="text" name="phone" placeholder="{{ __('e.g. 012 345 678') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Status') }} *</label>
                        <select name="status" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                            <option value="Active">{{ __('Active') }}</option>
                            <option value="Temporary">{{ __('Temporary') }}</option>
                            <option value="Suspended">{{ __('Suspended') }}</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Photo URL') }}</label>
                        <input type="url" name="photo" placeholder="https://images.unsplash.com/photo-..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Member Notes') }}</label>
                        <textarea name="notes" rows="2" placeholder="{{ __('e.g. Thesis student, allowed extra loan duration') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none"></textarea>
                    </div>
                </div>

                <!-- INITIAL BOOK LOAN ACCORDION / TOGGLE (ការកត់ត្រាសមាជិកថ្មី និងសៀវភៅដែលបានខ្ចី) -->
                <div class="pt-3 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="issue_initial_book" value="1" x-model="issueInitialBook" class="w-4 h-4 text-[#1E3A8A] rounded border-slate-300 focus:ring-indigo-500">
                        <span class="text-xs font-bold text-[#1E3A8A]">📖 {{ __('Issue Initial Book Loan') }}</span>
                    </label>

                    <div x-show="issueInitialBook" x-transition class="mt-3 p-4 rounded-2xl bg-slate-50 border border-blue-100 space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Select Book to Loan') }} *</label>
                            <select name="initial_book_id" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none bg-white">
                                <option value="">-- {{ __('Select Book to Loan') }} --</option>
                                @foreach($availableBooks as $book)
                                    <option value="{{ $book->id }}">
                                        {{ $book->title }} ({{ $book->available_copies }} {{ __('In Stock') }} - 📍 {{ $book->location_shelf }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">{{ __('Loan Date') }}</label>
                                <input type="date" name="initial_borrow_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">{{ __('Due Date') }}</label>
                                <input type="date" name="initial_due_date" value="{{ date('Y-m-d', strtotime('+14 days')) }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">{{ __('Notes') }}</label>
                            <input type="text" name="initial_loan_notes" placeholder="{{ __('Course research or special approval notes...') }}" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white">
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md">{{ __('Save Member') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: EDIT MEMBER PROFILE & NOTES (កែប្រែព័ត៌មានសមាជិក) -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative max-h-[90vh] overflow-y-auto" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">{{ __('Edit Member Profile') }}</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100">&times;</button>
            </div>

            <form :action="'/members/' + currentMember.id" method="POST" class="mt-5 space-y-4">
                @csrf
                @method('PUT')
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
                            <option value="Student">🎓 {{ __('Student') }}</option>
                            <option value="Teacher">👨‍🏫 {{ __('Teacher') }}</option>
                            <option value="General">👤 {{ __('General') }}</option>
                            <option value="Staff">🏢 {{ __('Staff') }}</option>
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
                                <input type="text" name="major" x-model="currentMember.major" placeholder="{{ __('e.g. វិទ្យាសាស្ត្រកុំព្យូទ័រ') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white">
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
                        <textarea name="notes" rows="2" x-model="currentMember.notes" placeholder="{{ __('e.g. Thesis student, allowed extra loan duration') }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-100 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">{{ __('Cancel') }}</button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs font-semibold hover:bg-blue-900 shadow-md shadow-blue-950/20">{{ __('Update Member') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 4: DELETE MEMBER CONFIRMATION (លុបសមាជិក) -->
    <div x-show="deleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 text-center" @click.away="deleteModalOpen = false">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            
            <h3 class="text-lg font-bold text-slate-900 mb-1">{{ __('Delete Member Confirmation') }}</h3>
            <p class="text-xs text-slate-500 mb-6">
                {{ __('Are you sure you want to remove this member?') }} <strong class="text-slate-800" x-text="'«' + memberToDelete.name + '»'"></strong>
            </p>

            <form :action="'/members/' + memberToDelete.id" method="POST" class="flex items-center justify-center gap-3">
                @csrf
                @method('DELETE')
                <button type="button" @click="deleteModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50">
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md">
                    {{ __('Yes, Delete Member') }}
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
