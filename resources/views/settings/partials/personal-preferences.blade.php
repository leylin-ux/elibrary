<div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-6">
    <div class="border-b border-slate-100 pb-4">
        <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
            <span>⚙️ {{ __('Personal Settings & Preferences') }}</span>
        </h3>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ __('Configure your preferred display language and personal notification alerts.') }}
        </p>
    </div>

    <form action="{{ route('settings.preferences.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Preferred Language -->
        <div class="space-y-3">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <span>🌐 {{ __('Preferred System Language') }}</span>
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="relative flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition-all {{ ($user->preferred_locale ?? session('locale', 'km')) === 'km' ? 'border-[#1E3A8A] bg-blue-50/40 ring-1 ring-[#1E3A8A]' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="preferred_locale" value="km" 
                           {{ ($user->preferred_locale ?? session('locale', 'km')) === 'km' ? 'checked' : '' }}
                           class="text-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🇰🇭</span>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">ភាសាខ្មែរ (Khmer)</span>
                            <span class="text-xs text-slate-400">{{ __('Default national library language') }}</span>
                        </div>
                    </div>
                </label>

                <label class="relative flex items-center gap-3 p-4 rounded-2xl border cursor-pointer transition-all {{ ($user->preferred_locale ?? session('locale', 'km')) === 'en' ? 'border-[#1E3A8A] bg-blue-50/40 ring-1 ring-[#1E3A8A]' : 'border-slate-200 hover:border-slate-300' }}">
                    <input type="radio" name="preferred_locale" value="en" 
                           {{ ($user->preferred_locale ?? session('locale', 'km')) === 'en' ? 'checked' : '' }}
                           class="text-[#1E3A8A] focus:ring-[#1E3A8A]">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">🇬🇧</span>
                        <div>
                            <span class="block text-sm font-bold text-slate-800">English (International)</span>
                            <span class="text-xs text-slate-400">{{ __('Global academic terminology') }}</span>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. Personal Notifications -->
        <div class="space-y-3 pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                <span>🔔 {{ __('Personal Notifications') }}</span>
            </h4>

            <div class="space-y-3">
                <!-- Toggle 1: Email alerts -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                    <div>
                        <span class="block text-sm font-bold text-slate-800">📧 {{ __('Receive Email Alerts on Loan & Return') }}</span>
                        <span class="text-xs text-slate-500">{{ __('Sends receipt updates and transaction logs to your registered email.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_email" value="1" 
                               {{ ($user->notify_email ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                    </label>
                </div>

                <!-- Toggle 2: Borrow due date reminders -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                    <div>
                        <span class="block text-sm font-bold text-slate-800">⏰ {{ __('Receive Due Date Overdue Reminders') }}</span>
                        <span class="text-xs text-slate-500">{{ __('Receive friendly alerts before your book reaches its due return date.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_borrow_reminders" value="1" 
                               {{ ($user->notify_borrow_reminders ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                    </label>
                </div>

                <!-- Toggle 3: Hold ready alert -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                    <div>
                        <span class="block text-sm font-bold text-slate-800">📦 {{ __('Receive Notification when Hold Book Arrives') }}</span>
                        <span class="text-xs text-slate-500">{{ __('Notifies you immediately when your reserved book is prepared for pickup.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_hold_ready" value="1" 
                               {{ ($user->notify_hold_ready ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                    </label>
                </div>

                <!-- Toggle 4: Sound effect alerts -->
                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/80 border border-slate-100">
                    <div>
                        <span class="block text-sm font-bold text-slate-800">🔔 {{ __('Enable Sound & Chime Effects in Portal') }}</span>
                        <span class="text-xs text-slate-500">{{ __('Plays polite audible sounds during notifications, book browsing, and actions.') }}</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_sound" value="1" 
                               {{ ($user->notify_sound ?? true) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#1E3A8A]"></div>
                    </label>
                </div>
            </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#1E3A8A] text-white text-xs sm:text-sm font-semibold hover:bg-blue-900 shadow-md transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>{{ __('Save Personal Preferences') }}</span>
            </button>
        </div>
    </form>
</div>
