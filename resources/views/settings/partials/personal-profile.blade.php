<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-6"
     x-data="{ 
         photoUrl: '{{ old('photo', $user->photo ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80') }}',
         previewImage(event) {
             const file = event.target.files[0];
             if (file) {
                 this.photoUrl = URL.createObjectURL(file);
             }
         }
     }">
    <div class="border-b border-slate-100 pb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
            <span>👤 {{ __('Personal Account Information') }}</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ __('Update your personal details, contact information, and avatar photo.') }}
        </p>
    </div>

    <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Avatar Upload & Live Preview -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-5 p-4 rounded-2xl bg-slate-50 border border-slate-100">
            <div class="relative shrink-0">
                <img :src="photoUrl" 
                     alt="Profile Avatar" 
                     class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover ring-4 ring-white shadow-md">
                <span class="absolute -bottom-1.5 -right-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-[#10B981] text-white shadow-xs">
                    {{ __('Active') }}
                </span>
            </div>

            <div class="space-y-3 flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        {{ __('Profile Photo / Avatar') }}
                    </label>
                    <p class="text-xs text-slate-500 mb-2">
                        {{ __('Upload a photo from your device to represent your account.') }}
                    </p>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Upload New Photo (JPG, PNG, WebP)') }}</label>
                    <input type="file" name="photo_file" accept="image/*" @change="previewImage"
                           class="w-full text-xs text-slate-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#1E3A8A] file:text-white hover:file:bg-blue-900 cursor-pointer">
                </div>
            </div>
        </div>

        <!-- Readonly Badges (Card ID & Role) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-blue-50/40 border border-blue-100/60 text-xs">
            <div>
                <span class="text-[10px] font-bold text-[#1E3A8A] uppercase tracking-wider block mb-1">
                    {{ __('Membership Card ID') }}
                </span>
                <span class="font-mono text-sm font-bold text-slate-800 bg-white px-3 py-1.5 rounded-xl border border-blue-200/60 inline-block">
                    {{ $user->card_id ?? 'STU-2026-001' }}
                </span>
            </div>
            <div>
                <span class="text-[10px] font-bold text-[#1E3A8A] uppercase tracking-wider block mb-1">
                    {{ __('Account Role & Privilege') }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl font-bold bg-white border border-blue-200/60 text-slate-800">
                    @if($user->role === 'admin')
                        👑 {{ __('Administrator') }}
                    @elseif($user->role === 'librarian')
                        📚 {{ __('Librarian') }}
                    @else
                        🎓 {{ __($user->member_type ?? 'Member') }}
                    @endif
                </span>
            </div>
        </div>

        <!-- Form Fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    {{ __('Full Name') }} *
                </label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                @error('name')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    {{ __('Email Address') }} *
                </label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                @error('email')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    {{ __('Phone Number') }}
                </label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="012 345 678"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                @error('phone')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                    {{ __('Physical Address / Living Location') }}
                </label>
                <input type="text" name="address" value="{{ old('address', $user->address) }}" placeholder="Phnom Penh, Cambodia"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                @error('address')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                {{ __('Bio / Department / Academic Info') }}
            </label>
            <textarea name="bio" rows="3" placeholder="{{ __('Department, class, major, or personal notes...') }}"
                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">{{ old('bio', $user->bio) }}</textarea>
            @error('bio')
                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold hover:bg-blue-900 shadow-md transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span>{{ __('Save Profile Information') }}</span>
            </button>
        </div>
    </form>
</div>
