<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-6"
     x-data="{ showPass: false }">
    <div class="border-b border-slate-100 pb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
            <span>🔒 {{ __('Change Password') }}</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ __('Update your account login password for enhanced security.') }}
        </p>
    </div>

    <form action="{{ route('settings.password.update') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Password Form Fields (2 cols) -->
            <div class="md:col-span-2 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        {{ __('Current Password') }} *
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="current_password" required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                    </div>
                    @error('current_password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        {{ __('New Password') }} *
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                    </div>
                    @error('password')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">
                        {{ __('Confirm New Password') }} *
                    </label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password_confirmation" required
                               placeholder="••••••••"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#1E3A8A]/20 focus:border-[#1E3A8A] outline-none">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="showPassToggle" @change="showPass = !showPass" class="rounded text-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <label for="showPassToggle" class="text-xs text-slate-600 font-medium cursor-pointer select-none">
                        {{ __('Show passwords while typing') }}
                    </label>
                </div>
            </div>

            <!-- Password Guidelines Card -->
            <div class="p-5 rounded-2xl bg-amber-50/50 border border-amber-200/60 space-y-3 self-start">
                <h4 class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                    <span>🛡️ {{ __('Security Checklist') }}</span>
                </h4>
                <ul class="space-y-2 text-xs text-amber-800">
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ __('At least 6 characters long') }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ __('Include letters & numbers') }}</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        <span>{{ __('Keep it private and confidential') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold hover:bg-blue-900 shadow-md transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>{{ __('Update Password') }}</span>
            </button>
        </div>
    </form>
</div>
