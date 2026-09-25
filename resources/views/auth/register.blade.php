<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('New Member Registration') }} - E-Library</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif; }
    </style>
</head>
<body class="min-h-screen antialiased text-slate-800 bg-slate-950 flex flex-col selection:bg-[#1E3A8A] selection:text-white relative overflow-x-hidden font-khmer">

    <!-- Full-Screen Soft Blurred Library Background (Identical to Login) -->
    <div class="fixed inset-0 z-0 overflow-hidden pointer-events-none select-none">
        <!-- High-Res Bookshelf Photo with Soft Blur and Scale -->
        <img src="{{ asset('images/library-bg.jpg') }}" 
             alt="Library Background" 
             class="w-full h-full object-cover scale-110 filter blur-[8px] brightness-[0.62] contrast-[1.05] transform-gpu">
        
        <!-- Layered Gradient Overlays for Deep Ambiance & Optimal Text Contrast -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#0B132B]/85 via-[#1E3A8A]/75 to-[#312E81]/80 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-slate-950/35 backdrop-blur-[1px]"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-transparent to-slate-950/40"></div>

        <!-- Soft Luminous Radial Glows -->
        <div class="absolute -top-32 -left-32 w-[34rem] h-[34rem] bg-indigo-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-[34rem] h-[34rem] bg-blue-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/3 w-96 h-96 bg-purple-500/15 rounded-full blur-3xl"></div>
    </div>

    <!-- Navigation Header -->
    <header class="relative z-20 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
        <a href="{{ route('login') }}" class="flex items-center gap-3.5 group">
            <div class="w-12 h-12 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-xl shadow-black/20 border border-white/20 group-hover:scale-105 transition-transform overflow-hidden shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="E-Library Logo" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="text-xl font-bold tracking-tight text-white block drop-shadow-sm">E-Library</span>
                <span class="text-[11px] text-blue-200/90 font-medium block">{{ __('Modern Library Management') }}</span>
            </div>
        </a>

        <!-- Top Right Actions: Language Switcher & Login Link -->
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-white/15 backdrop-blur-xl rounded-2xl p-1 shadow-lg border border-white/20 text-xs">
                <a href="{{ route('lang.switch', 'km') }}" 
                   class="px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ app()->getLocale() === 'km' ? 'bg-[#1E3A8A] text-white shadow-sm' : 'text-blue-100 hover:text-white' }}">
                    <span>🇰🇭</span>
                    <span>ភាសាខ្មែរ</span>
                </a>
                <a href="{{ route('lang.switch', 'en') }}" 
                   class="px-3 py-1.5 rounded-xl font-bold transition-all flex items-center gap-1.5 {{ app()->getLocale() === 'en' ? 'bg-[#1E3A8A] text-white shadow-sm' : 'text-blue-100 hover:text-white' }}">
                    <span>🇬🇧</span>
                    <span>English</span>
                </a>
            </div>

            <a href="{{ route('login') }}" 
               class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-xl border border-white/25 text-xs font-bold text-white shadow-lg hover:shadow-xl transition-all">
                <span>{{ __('Sign In') }}</span>
                <span>&rarr;</span>
            </a>
        </div>
    </header>

    <!-- Main Registration Container -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8 relative z-10"
          x-data="{
              registerStep: 1,
              selectedAvatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
              avatars: [
                  'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                  'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                  'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80'
              ]
          }">

        <div class="w-full max-w-xl bg-white/90 backdrop-blur-2xl rounded-3xl p-6 sm:p-10 shadow-2xl shadow-slate-950/40 border border-white/70 relative overflow-hidden transition-all duration-300">
            
            <!-- Top brand color highlight stripe -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#1E3A8A] via-blue-600 to-[#10B981]"></div>

            <!-- Header & Step Navigation -->
            <div class="mb-7">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-[#1E3A8A] uppercase tracking-wider">{{ __('New Member Registration') }}</span>
                    <span class="text-xs text-slate-400 font-semibold" x-text="'Step ' + registerStep + ' of 3'"></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">{{ __('Join E-Library') }}</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">
                    {{ __('Create your official library account to borrow books, read PDFs, and manage reservations.') }}
                </p>

                <!-- Step Progress Indicators -->
                <div class="flex items-center gap-2 mt-5">
                    <div class="flex-1 h-2 rounded-full transition-all duration-300" :class="registerStep >= 1 ? 'bg-[#1E3A8A]' : 'bg-slate-100'"></div>
                    <div class="flex-1 h-2 rounded-full transition-all duration-300" :class="registerStep >= 2 ? 'bg-[#1E3A8A]' : 'bg-slate-100'"></div>
                    <div class="flex-1 h-2 rounded-full transition-all duration-300" :class="registerStep >= 3 ? 'bg-[#1E3A8A]' : 'bg-slate-100'"></div>
                </div>
                <div class="flex justify-between text-[11px] font-bold text-slate-400 mt-2">
                    <span :class="registerStep === 1 ? 'text-[#1E3A8A]' : ''">{{ __('1. Account') }}</span>
                    <span :class="registerStep === 2 ? 'text-[#1E3A8A]' : ''">{{ __('2. Member Info') }}</span>
                    <span :class="registerStep === 3 ? 'text-[#1E3A8A]' : ''">{{ __('3. Confirmation') }}</span>
                </div>
            </div>

            <!-- Server Validation Errors -->
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>{{ __('Please correct the following errors:') }}</span>
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 pl-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Registration Form -->
            <form action="{{ route('register') }}" method="POST">
                @csrf

                <!-- ========================================================================= -->
                <!-- STEP 1: Account Info -->
                <!-- ========================================================================= -->
                <div x-show="registerStep === 1" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Full Name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               placeholder="{{ __('e.g. Sothea Meng') }}" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Email Address') }} <span class="text-rose-500">*</span></label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               placeholder="e.g. sothea@student.edu.kh" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Password') }} <span class="text-rose-500">*</span></label>
                            <input type="password" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Confirm Password') }} <span class="text-rose-500">*</span></label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   required 
                                   placeholder="••••••••" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="button" 
                                @click="registerStep = 2" 
                                class="w-full py-3 px-5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-blue-950/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>{{ __('Continue to Member Details') }}</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- STEP 2: Member Details -->
                <!-- ========================================================================= -->
                <div x-show="registerStep === 2" class="space-y-4" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Card ID / Student ID') }} <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="card_id" 
                               value="{{ old('card_id', 'LIB-' . date('Y') . '-' . rand(100, 999)) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none font-mono transition-all">
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Unique identification card code used for issuing and scanning library books.') }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Member Type') }} <span class="text-rose-500">*</span></label>
                        <select name="member_type" 
                                required 
                                class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none bg-white transition-all">
                            <option value="Student" {{ old('member_type') === 'Student' ? 'selected' : '' }}>{{ __('Student (សិស្ស / និស្សិត)') }}</option>
                            <option value="Teacher" {{ old('member_type') === 'Teacher' ? 'selected' : '' }}>{{ __('Teacher / Faculty (សាស្រ្តាចារ្យ / គ្រូបង្រៀន)') }}</option>
                            <option value="General" {{ old('member_type') === 'General' ? 'selected' : '' }}>{{ __('General Public (សាធារណជនទូទៅ)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">{{ __('Phone Number') }}</label>
                        <input type="text" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               placeholder="e.g. 012 345 678" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/10 outline-none transition-all">
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="registerStep = 1" 
                                class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-all cursor-pointer">
                            &larr; {{ __('Back') }}
                        </button>
                        <button type="button" 
                                @click="registerStep = 3" 
                                class="flex-1 py-3 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-blue-950/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>{{ __('Next') }}</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- STEP 3: Avatar & Submission -->
                <!-- ========================================================================= -->
                <div x-show="registerStep === 3" class="space-y-5" x-cloak>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-2">{{ __('Choose Avatar Icon') }}</label>
                        <div class="flex items-center justify-center gap-3.5 py-1">
                            <template x-for="(ava, idx) in avatars" :key="idx">
                                <button type="button" 
                                        @click="selectedAvatar = ava"
                                        class="w-14 h-14 rounded-2xl overflow-hidden transition-all transform hover:scale-105"
                                        :class="selectedAvatar === ava ? 'ring-3 ring-[#1E3A8A] shadow-md shadow-blue-950/20 scale-105' : 'ring-1 ring-slate-200 opacity-60 hover:opacity-100'">
                                    <img :src="ava" alt="Avatar option" class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                        <input type="hidden" name="photo" :value="selectedAvatar">
                    </div>

                    <!-- Library Agreement Card -->
                    <div class="p-4 bg-blue-50/80 rounded-2xl border border-blue-100 text-xs text-blue-950 space-y-1">
                        <div class="flex items-center gap-2 font-bold text-[#1E3A8A]">
                            <span>📜</span>
                            <span>{{ __('Library Card Agreement') }}</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed text-[11px]">
                            {{ __('By completing registration, you agree to return borrowed materials within 14 days, adhere to library decorum, and take responsibility for borrowed assets.') }}
                        </p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="registerStep = 2" 
                                class="flex-1 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition-all cursor-pointer">
                            &larr; {{ __('Back') }}
                        </button>
                        <button type="submit" 
                                class="flex-1 py-3 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 active:scale-[0.99] text-white font-bold text-sm shadow-lg shadow-blue-950/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <span>{{ __('Complete Registration') }}</span>
                            <span>✓</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Bottom Back to Login Link -->
            <div class="text-center pt-6 mt-6 border-t border-slate-100">
                <p class="text-xs text-slate-500">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}" class="font-bold text-[#1E3A8A] hover:underline ml-1">
                        {{ __('Sign in to your account') }} &rarr;
                    </a>
                </p>
            </div>

        </div>
    </main>

    <!-- Footer Copyright -->
    <footer class="relative z-10 py-5 text-center text-xs text-blue-200/70">
        <p>&copy; {{ date('Y') }} E-Library. {{ __('All rights reserved.') }}</p>
    </footer>

</body>
</html>
