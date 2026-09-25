@extends('layouts.app')

@section('title', $isAdmin ? __('Reservation Desk') : __('My Reservations'))

@section('content')
<div class="space-y-6" x-data="{
    approveModalOpen: false,
    forceCancelModalOpen: false,
    selectedReservation: null,
    forceCancelReservation: null,
    forceCancelReason: '',
    pickupHours: 48,

    openApproveModal(res) {
        this.selectedReservation = res;
        this.pickupHours = 48;
        this.approveModalOpen = true;
    },

    openForceCancelModal(res) {
        this.forceCancelReservation = res;
        this.forceCancelReason = '';
        this.forceCancelModalOpen = true;
    }
}">
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-[#1E3A8A]">
                {{ $isAdmin ? __('Reservations & Shelf Holds') : __('My Book Reservations') }}
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ $isAdmin ? __('Review online hold requests, hold books on the shelf, set pickup deadlines, and fulfill loans.') : __('Track your online book reservations and pickup deadlines before picking up in person.') }}
            </p>
        </div>

    </div>

    <!-- Filters & Search -->
    <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('reservations.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[240px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Search reservation code, patron or book...') }}" class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-[#6366F1] outline-none">
                <svg class="w-4 h-4 absolute left-3 top-2.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl outline-none">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                <option value="Fulfilled" {{ request('status') === 'Fulfilled' ? 'selected' : '' }}>{{ $isAdmin ? __('ទទួលរួចហើយ (Fulfilled)') : __('បានទទួល (Received)') }}</option>
                <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>{{ __('Cancelled') }}</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-[#1E3A8A] text-white text-xs font-semibold rounded-xl hover:bg-blue-900 transition-colors">{{ __('Filter') }}</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('reservations.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline">{{ __('Clear') }}</a>
            @endif
        </form>

        <span class="text-xs text-slate-400">{{ __('Showing') }} {{ $reservations->count() }} {{ __('of') }} {{ $reservations->total() }} {{ __('holds') }}</span>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                        <th class="py-3.5 px-6 whitespace-nowrap">{{ __('Hold Code') }}</th>
                        @if($isAdmin)
                            <th class="py-3.5 px-6 whitespace-nowrap">{{ __('Member') }}</th>
                        @endif
                        <th class="py-3.5 px-6">{{ __('Reserved Book') }}</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">{{ __('Requested Date') }}</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">{{ __('Pickup Deadline') }}</th>
                        <th class="py-3.5 px-6 whitespace-nowrap">{{ __('Status') }}</th>
                        <th class="py-3.5 px-6 text-right whitespace-nowrap">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse($reservations as $res)
                        @php
                            $isExpired = $res->isExpired();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-6 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 font-mono font-bold text-xs border border-indigo-100 whitespace-nowrap">
                                    {{ $res->reservation_code }}
                                </span>
                            </td>

                            @if($isAdmin)
                                <td class="py-3.5 px-6">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $res->user?->photo ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80' }}" 
                                             class="w-8 h-8 rounded-full object-cover ring-1 ring-slate-200">
                                        <div>
                                            <p class="font-bold text-slate-800 text-xs">{{ $res->user?->name ?? __('Unknown Patron') }}</p>
                                            <span class="text-[11px] font-mono text-slate-400">{{ $res->user?->card_id ?? 'No Card' }}</span>
                                        </div>
                                    </div>
                                </td>
                            @endif

                            <td class="py-3.5 px-6">
                                <p class="font-medium text-slate-800 text-xs sm:text-sm line-clamp-1">{{ $res->book?->title ?? __('Book Deleted / Unavailable') }}</p>
                                <span class="text-[11px] text-slate-400 font-mono">📍 {{ $res->book?->location_shelf ?? 'N/A' }}</span>
                            </td>

                            <td class="py-3.5 px-6 text-xs text-slate-600 font-mono whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($res->reservation_date)->format('M d, Y') }}
                            </td>

                            <td class="py-3.5 px-6 text-xs whitespace-nowrap">
                                @if($res->pickup_deadline)
                                    <span class="font-mono block {{ $isExpired ? 'text-rose-600 font-bold line-through' : 'text-slate-700 font-semibold' }}">
                                        {{ \Carbon\Carbon::parse($res->pickup_deadline)->format('M d, H:i') }}
                                    </span>
                                    @if($isExpired)
                                        <span class="text-[10px] text-rose-500 font-bold">⚠️ {{ __('Expired') }}</span>
                                    @elseif($res->status === 'Approved')
                                        <span class="text-[10px] text-amber-600 font-medium">⏳ {{ \Carbon\Carbon::now()->diffForHumans($res->pickup_deadline, true) }} {{ __('left') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs">{{ __('Pending approval') }}</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-6 whitespace-nowrap">
                                @if($res->status === 'Pending')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-[#F59E0B] border border-amber-200/50 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#F59E0B] shrink-0"></span>
                                        <span>{{ __('Pending Review') }}</span>
                                    </span>
                                @elseif($res->status === 'Approved')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#10B981] border border-emerald-200/50 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#10B981] shrink-0"></span>
                                        <span>{{ __('Approved') }}</span>
                                    </span>
                                @elseif($res->status === 'Fulfilled')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                        <span>{{ $isAdmin ? __('ទទួលរួចហើយ') : __('បានទទួល') }}</span>
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 whitespace-nowrap">
                                        {{ __($res->status) }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-6 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    @if($isAdmin)
                                        @if($res->status === 'Pending')
                                            <!-- Admin Approve Button -->
                                            <button type="button" 
                                                    @click="openApproveModal({{ json_encode($res) }})" 
                                                    class="px-2.5 py-1 rounded-lg text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-2xs transition-colors"
                                                    {{ !$res->book ? 'disabled' : '' }}>
                                                {{ __('Approve Hold') }}
                                            </button>
                                        @elseif($res->status === 'Approved')
                                            <!-- Direct Issue Book on Reservations Page (No redirect to Circulation) -->
                                            @if($res->book)
                                                <form action="{{ route('reservations.fulfill', $res) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('{{ __('Confirm issuing this book to :name? (Hold: :code)', ['name' => $res->user?->name ?? 'Member', 'code' => $res->reservation_code]) }}')"
                                                            class="px-2.5 py-1 rounded-lg text-xs font-semibold text-white bg-[#1E3A8A] hover:bg-blue-900 shadow-2xs transition-colors inline-flex items-center gap-1"
                                                            title="{{ __('Issue Book Now') }}">
                                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        <span>{{ __('Issue Book') }}</span>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        @if(in_array($res->status, ['Pending', 'Approved']))
                                            @if($layoutUser?->canForceCancelReservations())
                                                <!-- Force Cancel Button (Manager / Super Admin) -->
                                                <button type="button" 
                                                        @click="openForceCancelModal({{ json_encode($res) }})" 
                                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition-colors inline-flex items-center gap-1"
                                                        title="{{ __('Special Cancellation (Manager Decision)') }}">
                                                    <span>🚫</span>
                                                    <span>{{ __('Force Cancel') }}</span>
                                                </button>
                                            @else
                                                <!-- Cancel Hold Form -->
                                                <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            onclick="return confirm('{{ __('Are you sure you want to cancel this reservation?') }}')"
                                                            class="p-1 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100 transition-colors"
                                                            title="{{ __('Cancel Reservation') }}">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    @else
                                        <!-- Member Actions -->
                                        @if(in_array($res->status, ['Pending', 'Approved']))
                                            <form action="{{ route('reservations.cancel', $res) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('{{ __('Cancel your hold on this book?') }}')"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                                                    {{ __('Cancel Hold') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">{{ __('No actions') }}</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isAdmin ? 7 : 6 }}" class="text-center py-12 text-slate-400 text-sm">
                                <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ __('No book reservations found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $reservations->links() }}
    </div>

    @if($isAdmin)
        <!-- APPROVE RESERVATION MODAL -->
        <div x-show="approveModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 relative" @click.away="approveModalOpen = false">
                <template x-if="selectedReservation">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <h3 class="text-lg font-bold text-slate-900">{{ __('Approve Book Hold') }}</h3>
                            <button @click="approveModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">&times;</button>
                        </div>

                        <div class="mt-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-1.5">
                            <p><strong>{{ __('Patron') }}:</strong> <span x-text="selectedReservation.user ? selectedReservation.user.name : '{{ __('Unknown Patron') }}'"></span></p>
                            <p><strong>{{ __('Book') }}:</strong> <span x-text="selectedReservation.book ? selectedReservation.book.title : '{{ __('Book Deleted / Unavailable') }}'"></span></p>
                            <p><strong>{{ __('Hold Code') }}:</strong> <span class="font-mono font-bold text-indigo-600" x-text="selectedReservation.reservation_code"></span></p>
                        </div>

                        <form :action="'/reservations/' + selectedReservation.id + '/approve'" method="POST" class="mt-4 space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">{{ __('Pickup Hold Shelf Duration') }} *</label>
                                <select name="pickup_hours" x-model="pickupHours" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs outline-none bg-white">
                                    <option value="24">24 {{ __('Hours (1 Day)') }}</option>
                                    <option value="48">48 {{ __('Hours (2 Days - Recommended)') }}</option>
                                    <option value="72">72 {{ __('Hours (3 Days)') }}</option>
                                </select>
                                <p class="text-[11px] text-slate-400 mt-1">{{ __('One copy will be held at the circulation shelf. If not picked up before deadline, hold expires.') }}</p>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                                <button type="button" @click="approveModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold">{{ __('Cancel') }}</button>
                                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-md">{{ __('Confirm Approval') }}</button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        <!-- FORCE CANCEL RESERVATION MODAL (Manager Exclusive) -->
        @if($layoutUser?->canForceCancelReservations())
            <div x-show="forceCancelModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" x-cloak>
                <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 shadow-2xl border border-slate-100 relative" @click.away="forceCancelModalOpen = false">
                    <template x-if="forceCancelReservation">
                        <div>
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg">
                                        🚫
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ __('Force Cancel Reservation') }}</h3>
                                        <p class="text-xs text-slate-500">{{ __('Special manager cancellation with mandatory reason.') }}</p>
                                    </div>
                                </div>
                                <button @click="forceCancelModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">&times;</button>
                            </div>

                            <div class="mt-4 p-3.5 bg-rose-50/60 rounded-2xl border border-rose-100 text-xs space-y-1.5">
                                <p><strong>{{ __('Patron') }}:</strong> <span x-text="forceCancelReservation.user ? forceCancelReservation.user.name : '{{ __('Unknown Patron') }}'"></span></p>
                                <p><strong>{{ __('Book') }}:</strong> <span x-text="forceCancelReservation.book ? forceCancelReservation.book.title : '{{ __('Book Deleted / Unavailable') }}'"></span></p>
                                <p><strong>{{ __('Hold Code') }}:</strong> <span class="font-mono font-bold text-rose-700" x-text="forceCancelReservation.reservation_code"></span></p>
                            </div>

                            <form :action="'/reservations/' + forceCancelReservation.id + '/force-cancel'" method="POST" class="mt-4 space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                        {{ __('Mandatory Cancellation Reason') }} *
                                    </label>
                                    <input type="text" 
                                           name="reason" 
                                           x-model="forceCancelReason" 
                                           required 
                                           placeholder="{{ __('e.g., Book damaged & pulled for repair, student no-show...') }}" 
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs outline-none focus:ring-2 focus:ring-rose-200 bg-white">

                                    <!-- Quick Reason Chips -->
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        <button type="button" 
                                                @click="forceCancelReason = '{{ __('Book damaged and pulled from shelf for repair') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            🛠️ {{ __('Damaged / Needs Repair') }}
                                        </button>
                                        <button type="button" 
                                                @click="forceCancelReason = '{{ __('Member failed to pick up within pickup deadline') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            ⏰ {{ __('No-Show / Deadline Passed') }}
                                        </button>
                                        <button type="button" 
                                                @click="forceCancelReason = '{{ __('Reserved for urgent faculty lecture curriculum') }}'"
                                                class="px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-[11px] text-slate-600">
                                            📚 {{ __('Faculty Curriculum Demand') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600">
                                    ℹ️ {{ __('Cancelling this hold immediately returns 1 copy to available shelf inventory and logs the action in the manager audit trail.') }}
                                </div>

                                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                                    <button type="button" @click="forceCancelModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold">{{ __('Close') }}</button>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-md flex items-center gap-1.5">
                                        <span>🚫</span>
                                        <span>{{ __('Confirm Force Cancel') }}</span>
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
