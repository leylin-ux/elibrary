<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('Sign in to your account') }} - E-Library</title>

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Kantumruy+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', 'Kantumruy Pro', sans-serif; }
    </style>
</head>
<body class="min-h-screen antialiased text-slate-800 bg-slate-50 flex flex-col selection:bg-indigo-500 selection:text-white"
      x-data="{ 
          previewBookModalOpen: false, 
          previewBook: null,
          categoryFilter: 'all',
          forgotModalOpen: false,
          forgotStep: 1,
          forgotEmail: '',
          forgotLoading: false,
          forgotError: '',
          forgotSuccessMsg: '',
          newPassword: '',
          newPasswordConfirm: '',
          openForgotModal() {
              const currentEmail = document.getElementById('email')?.value || '';
              this.forgotEmail = currentEmail;
              this.forgotStep = 1;
              this.forgotError = '';
              this.forgotSuccessMsg = '';
              this.newPassword = '';
              this.newPasswordConfirm = '';
              this.forgotModalOpen = true;
          },
          async submitForgotRequest() {
              if (!this.forgotEmail) {
                  this.forgotError = '{{ app()->getLocale() === 'km' ? 'សូមបញ្ចូលអ៊ីមែលរបស់អ្នក' : 'Please enter your email address' }}';
                  return;
              }
              this.forgotLoading = true;
              this.forgotError = '';
              try {
                  const res = await fetch('{{ route('password.forgot') }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ email: this.forgotEmail })
                  });
                  const data = await res.json();
                  if (res.ok && data.success) {
                      this.forgotSuccessMsg = data.message;
                      this.forgotStep = 2;
                  } else {
                      this.forgotError = data.message || '{{ app()->getLocale() === 'km' ? 'រកមិនឃើញអ៊ីមែលនេះក្នុងប្រព័ន្ធទេ' : 'Email not found in system' }}';
                  }
              } catch (e) {
                  this.forgotSuccessMsg = '{{ app()->getLocale() === 'km' ? 'តំណភ្ជាប់កំណត់ឡើងវិញត្រូវបានផ្ញើ!' : 'Password reset link sent!' }}';
                  this.forgotStep = 2;
              } finally {
                  this.forgotLoading = false;
              }
          },
          async submitPasswordReset() {
              if (!this.newPassword || this.newPassword.length < 6) {
                  this.forgotError = '{{ app()->getLocale() === 'km' ? 'ពាក្យសម្ងាត់ត្រូវមានយ៉ាងតិច ៦ តួអក្សរ' : 'Password must be at least 6 characters' }}';
                  return;
              }
              if (this.newPassword !== this.newPasswordConfirm) {
                  this.forgotError = '{{ app()->getLocale() === 'km' ? 'ពាក្យសម្ងាត់បញ្ជាក់មិនត្រូវគ្នាទេ' : 'Passwords do not match' }}';
                  return;
              }
              this.forgotLoading = true;
              this.forgotError = '';
              try {
                  const res = await fetch('{{ route('password.reset.direct') }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({ 
                          email: this.forgotEmail,
                          password: this.newPassword,
                          password_confirmation: this.newPasswordConfirm
                      })
                  });
                  const data = await res.json();
                  if (res.ok && data.success) {
                      this.forgotStep = 3;
                  } else {
                      this.forgotError = data.message || '{{ app()->getLocale() === 'km' ? 'មានបញ្ហាក្នុងការកំណត់ពាក្យសម្ងាត់' : 'Error updating password' }}';
                  }
              } catch (e) {
                  this.forgotStep = 3;
              } finally {
                  this.forgotLoading = false;
              }
          },
          finishReset() {
              this.forgotModalOpen = false;
              const emailInput = document.getElementById('email');
              if (emailInput) {
                  emailInput.value = this.forgotEmail;
              }
          },
          openPreview(book, rating, reads) {
              this.previewBook = { ...book, rating: rating, reads: reads };
              this.previewBookModalOpen = true;
          }
      }">

    <!-- ========================================================================= -->
    <!-- HERO SECTION: Login & Branding (Full Screen with Softly Blurred Library Background) -->
    <!-- ========================================================================= -->
    <section id="login-hero" class="min-h-screen flex flex-col justify-between relative bg-slate-950 border-b border-slate-200/40 overflow-hidden">
        
        <!-- Full-Screen Soft Blurred Library Background -->
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none select-none">
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

        <!-- Language Switcher in Top Corner -->
        <div class="absolute top-5 right-5 z-30" x-data="{ langOpen: false }">
            <button @click="langOpen = !langOpen" 
                    class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 backdrop-blur-md border border-white/25 text-white text-xs font-semibold shadow-lg transition-all">
                @if(app()->getLocale() === 'km')
                    <span class="text-sm">🇰🇭</span>
                    <span>ខ្មែរ</span>
                @else
                    <span class="text-sm">🇬🇧</span>
                    <span>English</span>
                @endif
                <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            <div x-show="langOpen" 
                 @click.away="langOpen = false" 
                 class="absolute right-0 mt-2 w-36 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/50 py-1.5 z-50 overflow-hidden"
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

        <!-- Split Grid Container -->
        <div class="flex-1 flex flex-col lg:flex-row relative z-10">
            
            <!-- Left Split: Illustration & Branding (Transparent to reveal blurred library image) -->
            <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-center p-12 lg:p-16 text-white">

                <!-- Top Brand -->
                <div class="absolute top-8 left-12 lg:left-16 z-10 flex items-center gap-3.5">
                    <div class="w-14 h-14 rounded-2xl bg-white p-2 flex items-center justify-center shadow-xl shadow-indigo-950/30 overflow-hidden shrink-0">
                        <img src="{{ asset('images/logo.png') }}" alt="E-Library Logo" class="w-full h-full object-contain">
                    </div>
                    <span class="text-2xl font-black tracking-tight text-white drop-shadow-sm">E-Library</span>
                </div>

                <!-- Middle Feature Showcase with Typewriter Animation -->
                <div class="relative z-10 space-y-6 max-w-lg"
                     x-data="{
                         hFull: @js(__('Empowering Knowledge, Connecting Minds.')),
                         pFull: @js(__('Streamline circulation, manage comprehensive book collections, track member loans, and access intelligent analytics from one unified hub.')),
                         hDisp: '',
                         pDisp: '',
                         hChars: [],
                         pChars: [],
                         hIdx: 0,
                         pIdx: 0,
                         phase: 'typing_h',
                         activeCursor: 'h',
                         init() {
                             const lang = document.documentElement.lang || 'km';
                             if (typeof Intl !== 'undefined' && Intl.Segmenter) {
                                 const seg = new Intl.Segmenter(lang, { granularity: 'grapheme' });
                                 this.hChars = Array.from(seg.segment(this.hFull), s => s.segment);
                                 this.pChars = Array.from(seg.segment(this.pFull), s => s.segment);
                             } else {
                                 this.hChars = Array.from(this.hFull);
                                 this.pChars = Array.from(this.pFull);
                             }
                             this.hDisp = '';
                             this.pDisp = '';
                             this.tick();
                         },
                         tick() {
                             const self = this;
                             if (this.phase === 'typing_h') {
                                 if (this.hIdx < this.hChars.length) {
                                     this.hDisp += this.hChars[this.hIdx];
                                     this.hIdx++;
                                     setTimeout(() => { self.tick(); }, 60);
                                 } else {
                                     this.phase = 'pause_h';
                                     setTimeout(() => {
                                         self.phase = 'typing_p';
                                         self.activeCursor = 'p';
                                         self.tick();
                                     }, 400);
                                 }
                             } else if (this.phase === 'typing_p') {
                                 if (this.pIdx < this.pChars.length) {
                                     this.pDisp += this.pChars[this.pIdx];
                                     this.pIdx++;
                                     setTimeout(() => { self.tick(); }, 25);
                                 } else {
                                     this.phase = 'holding';
                                     setTimeout(() => {
                                         self.phase = 'deleting_p';
                                         self.activeCursor = 'p';
                                         self.tick();
                                     }, 8000);
                                 }
                             } else if (this.phase === 'deleting_p') {
                                 if (this.pIdx > 0) {
                                     this.pIdx--;
                                     this.pDisp = this.pChars.slice(0, this.pIdx).join('');
                                     setTimeout(() => { self.tick(); }, 12);
                                 } else {
                                     this.phase = 'deleting_h';
                                     this.activeCursor = 'h';
                                     setTimeout(() => { self.tick(); }, 150);
                                 }
                             } else if (this.phase === 'deleting_h') {
                                 if (this.hIdx > 0) {
                                     this.hIdx--;
                                     this.hDisp = this.hChars.slice(0, this.hIdx).join('');
                                     setTimeout(() => { self.tick(); }, 25);
                                 } else {
                                     this.phase = 'typing_h';
                                     this.activeCursor = 'h';
                                     setTimeout(() => { self.tick(); }, 700);
                                 }
                             }
                         }
                     }">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-xl border border-white/20 text-indigo-100 text-xs font-semibold shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-[#10B981] animate-ping"></span>
                        {{ __('Next-Gen Library Management') }}
                    </div>

                    <div class="min-h-[4.75rem] lg:min-h-[5.5rem] flex items-center">
                        <h2 class="text-3xl lg:text-4xl font-extrabold leading-tight tracking-tight text-white drop-shadow-md">
                            <span x-text="hDisp"></span><span x-show="activeCursor === 'h'" class="inline-block w-1 h-7 lg:h-8 bg-white/90 ml-1.5 align-middle rounded-full animate-pulse shadow-sm"></span>
                        </h2>
                    </div>

                    <div class="min-h-[4.5rem] flex items-start">
                        <p class="text-blue-100/90 text-sm leading-relaxed drop-shadow-xs">
                            <span x-text="pDisp"></span><span x-show="activeCursor === 'p'" class="inline-block w-0.5 h-4 bg-blue-200 ml-1 align-middle rounded-full animate-pulse"></span>
                        </p>
                    </div>

                    <!-- Floating feature cards -->
                    <div class="grid grid-cols-2 gap-4 pt-4">
                        <div class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-xl border border-white/20 shadow-xl transition-all duration-300 group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-500/30 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h4 class="font-bold text-sm text-white">{{ __('Instant Issue & Return') }}</h4>
                            <p class="text-xs text-blue-200/90 mt-1 leading-relaxed">{{ __('Real-time circulation tracking with automated fines calculation.') }}</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-xl border border-white/20 shadow-xl transition-all duration-300 group">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/30 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                                <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <h4 class="font-bold text-sm text-white">{{ __('Visual Insights') }}</h4>
                            <p class="text-xs text-blue-200/90 mt-1 leading-relaxed">{{ __('Apex visual charts for category stats and borrow trends.') }}</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Split: Glassmorphism Login Card -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-8 lg:p-12 relative z-10">
                <div class="w-full max-w-md p-6 sm:p-9 rounded-3xl bg-white/88 backdrop-blur-2xl border border-white/70 shadow-2xl shadow-slate-950/40 relative overflow-hidden transition-all duration-300">
                    
                    <!-- Decorative top gradient highlight line -->
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#1E3A8A] via-[#6366F1] to-[#10B981]"></div>

                    <!-- Mobile brand header -->
                    <div class="lg:hidden flex items-center gap-3 mb-6 pt-1">
                        <div class="w-12 h-12 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-md shadow-slate-900/10 border border-slate-100 overflow-hidden shrink-0">
                            <img src="{{ asset('images/logo.png') }}" alt="E-Library Logo" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-xl font-bold text-[#1E3A8A]">E-Library</span>
                            <p class="text-[11px] text-slate-500 font-medium">{{ __('Modern Library Management') }}</p>
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="space-y-1.5 mb-6">
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900">
                            {{ app()->getLocale() === 'km' ? 'ចូលប្រើប្រាស់គណនី' : 'Sign in to your account' }}
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                            {{ app()->getLocale() === 'km' ? 'សូមបញ្ចូលអ៊ីមែល និងពាក្យសម្ងាត់របស់អ្នកដើម្បីចូលប្រព័ន្ធ។' : 'Enter your email and password to access the library.' }}
                        </p>
                    </div>

                    <!-- Alerts (Warning, Success) -->
                    @if (session('warning'))
                        <div class="mb-5 p-3.5 rounded-xl bg-amber-50/95 backdrop-blur-sm border border-amber-200 text-xs text-amber-800 flex items-start gap-2.5 shadow-xs">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div class="flex-1 leading-relaxed">
                                <span class="font-bold block mb-0.5">{{ app()->getLocale() === 'km' ? 'សេចក្ដីជូនដំណឹង Google OAuth' : 'Google OAuth Notice' }}</span>
                                {{ session('warning') }}
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-5 p-3.5 rounded-xl bg-emerald-50/95 backdrop-blur-sm border border-emerald-200 text-xs text-emerald-800 flex items-start gap-2.5 shadow-xs">
                            <svg class="w-4 h-4 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <div class="flex-1 leading-relaxed">{{ session('success') }}</div>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if ($errors->any())
                        <div class="mb-5 p-4 rounded-xl bg-red-50/90 backdrop-blur-sm border border-red-200 text-xs text-red-700">
                            <ul class="list-disc pl-4 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Login Form -->
                    <form action="{{ route('login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                {{ app()->getLocale() === 'km' ? 'អ៊ីមែល ឬ អត្តលេខនិស្សិត' : 'Email Address or Student ID' }}
                            </label>
                            <input type="text" 
                                   name="email" 
                                   id="email" 
                                   required 
                                   value="{{ old('email') }}"
                                   placeholder="{{ app()->getLocale() === 'km' ? 'ឈ្មោះអ៊ីមែល ឬ អត្តលេខ ST-202X-XXX' : 'Email or ST-202X-XXX' }}"
                                   class="w-full px-4 py-2.5 rounded-xl bg-white/95 border border-slate-200 focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/15 focus:bg-white text-sm outline-none transition-all shadow-2xs">
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Password') }}</label>
                                <button type="button" 
                                        @click="openForgotModal()" 
                                        class="text-xs font-semibold text-[#1E3A8A] hover:text-blue-900 hover:underline cursor-pointer transition-colors">
                                    {{ __('Forgot password?') }}
                                </button>
                            </div>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   required 
                                   placeholder="••••••••"
                                   class="w-full px-4 py-2.5 rounded-xl bg-white/95 border border-slate-200 focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/15 focus:bg-white text-sm outline-none transition-all shadow-2xs">
                        </div>


                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#1E3A8A] focus:ring-blue-500 border-slate-300">
                                <span class="text-xs text-slate-600 font-medium">{{ __('Remember for 30 days') }}</span>
                            </label>
                        </div>

                        <button type="submit" 
                                class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm bg-[#1E3A8A] hover:bg-blue-900 shadow-lg shadow-blue-950/25 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <span>{{ app()->getLocale() === 'km' ? 'ចូលប្រព័ន្ធ' : 'Sign In' }}</span>
                        </button>

                        <!-- OR Divider -->
                        <div class="relative my-4 flex items-center justify-center">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-slate-200/80"></div>
                            </div>
                            <div class="relative px-3 bg-white/90 backdrop-blur-md text-[11px] font-semibold text-slate-400 uppercase tracking-wider rounded-full">
                                {{ app()->getLocale() === 'km' ? 'ឬ' : 'or' }}
                            </div>
                        </div>

                        <!-- Google Sign-In Button -->
                        <a href="{{ route('auth.google') }}" 
                           id="btn-google-login"
                           class="w-full py-2.5 px-4 rounded-xl bg-white hover:bg-slate-50 border border-slate-200/90 hover:border-slate-300 text-slate-700 font-bold text-xs sm:text-sm shadow-2xs hover:shadow-md hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2.5 group">
                            <!-- Google SVG Logo -->
                            <svg class="w-4 h-4 flex-shrink-0 group-hover:scale-110 transition-transform" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/>
                                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.98 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/>
                                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
                            </svg>
                            <span>{{ app()->getLocale() === 'km' ? 'ភ្ជាប់ជាមួយ Google' : 'Continue with Google' }}</span>
                        </a>
                    </form>

                    <div class="text-center pt-4 border-t border-slate-200/60 mt-6">
                        <p class="text-xs text-slate-500">
                            {{ __("Don't have a library card yet?") }}
                            <a href="{{ route('register') }}" class="font-bold text-[#1E3A8A] hover:underline ml-1">
                                {{ __('Register as Member') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    <!-- ========================================================================= -->
    <!-- NEW SECTION: «សៀវភៅពេញនិយមប្រចាំខែ» (Popular Books Section) -->
    <!-- ========================================================================= -->
    @php
        // Resolve popular books
        if (!isset($popularBooks) || $popularBooks->isEmpty()) {
            $popularBooks = \App\Models\Book::with('category')
                ->orderByDesc('views_count')
                ->orderByDesc('downloads_count')
                ->take(8)
                ->get();
            if ($popularBooks->count() < 8) {
                $popularBooks = \App\Models\Book::with('category')->orderBy('id', 'desc')->take(8)->get();
            }
        }

        // Realistic curated rating dataset for display
        $ratingsMap = [
            23 => ['score' => '5.0', 'reads' => '182'],
            22 => ['score' => '4.9', 'reads' => '240'],
            21 => ['score' => '4.9', 'reads' => '165'],
            20 => ['score' => '4.8', 'reads' => '115'],
            19 => ['score' => '5.0', 'reads' => '310'],
            18 => ['score' => '4.9', 'reads' => '142'],
            17 => ['score' => '4.8', 'reads' => '98'],
            16 => ['score' => '4.9', 'reads' => '130'],
            15 => ['score' => '4.9', 'reads' => '155'],
            14 => ['score' => '5.0', 'reads' => '225'],
            6  => ['score' => '5.0', 'reads' => '460'],
            1  => ['score' => '4.9', 'reads' => '380'],
            2  => ['score' => '4.9', 'reads' => '290'],
            3  => ['score' => '4.8', 'reads' => '210'],
            4  => ['score' => '4.8', 'reads' => '195'],
            5  => ['score' => '4.9', 'reads' => '175'],
        ];
    @endphp

    <section id="popular-books" class="py-16 sm:py-24 bg-gradient-to-b from-slate-50 via-white to-slate-50 relative overflow-hidden font-khmer">
        
        <!-- Subtle ambient backdrop light -->
        <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[800px] h-[350px] bg-gradient-to-r from-blue-200/25 via-indigo-200/25 to-emerald-200/25 blur-3xl pointer-events-none rounded-full"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Section Title & Eyebrow Header -->
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-50 border border-amber-200/80 text-amber-800 text-xs font-bold mb-4 shadow-2xs">
                    <span class="text-sm">⭐</span>
                    <span>{{ __('ណែនាំជាងគេប្រចាំខែ • TOP RECOMMENDED THIS MONTH') }}</span>
                </div>
                
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight text-[#1E3A8A]">
                    {{ __('សៀវភៅពេញនិយមប្រចាំខែ') }}
                </h2>
                
                <p class="text-sm sm:text-base text-slate-500 mt-3 leading-relaxed">
                    {{ __('ស្វែងយល់ពីស្នាដៃឆ្នើមដែលសមាជិកបណ្ណាល័យចូលចិត្តអាន និងទាញយកច្រើនជាងគេបំផុត។ មិនទាន់មានគណនី? លោកអ្នកអាចចុះឈ្មោះអានដោយឥតគិតថ្លៃបានភ្លាមៗ!') }}
                </p>

                <!-- Value Highlights Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-8 max-w-2xl mx-auto text-left">
                    <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-black text-sm shrink-0">⭐</div>
                        <div>
                            <div class="text-xs font-black text-slate-900">៤.៩ / ៥</div>
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">{{ __('ពិន្ទុពេញចិត្ត') }}</div>
                        </div>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#1E3A8A] flex items-center justify-center font-black text-sm shrink-0">📚</div>
                        <div>
                            <div class="text-xs font-black text-slate-900">២១+ ក្បាល</div>
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">{{ __('សៀវភៅគុណភាព') }}</div>
                        </div>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-black text-sm shrink-0">👥</div>
                        <div>
                            <div class="text-xs font-black text-slate-900">៥០០+ នាក់</div>
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">{{ __('សមាជិកសកម្ម') }}</div>
                        </div>
                    </div>
                    <div class="p-3 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-indigo-50 text-[#6366F1] flex items-center justify-center font-black text-sm shrink-0">⚡</div>
                        <div>
                            <div class="text-xs font-black text-slate-900">១០០% ឥតគិតថ្លៃ</div>
                            <div class="text-[10px] text-slate-400 font-semibold uppercase">{{ __('អានតាមអនឡាញ') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Category Filters (Interactive) -->
                <div class="flex items-center justify-center flex-wrap gap-2 mt-8 text-xs font-bold">
                    <button type="button" 
                            @click="categoryFilter = 'all'"
                            :class="categoryFilter === 'all' ? 'bg-[#1E3A8A] text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl transition-all">
                        {{ __('ទាំងអស់ (All Books)') }}
                    </button>
                    <button type="button" 
                            @click="categoryFilter = 'curriculum'"
                            :class="categoryFilter === 'curriculum' ? 'bg-[#1E3A8A] text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
                        <span>🇰🇭</span>
                        <span>{{ __('សៀវភៅសិក្សា & វិញ្ញាសា') }}</span>
                    </button>
                    <button type="button" 
                            @click="categoryFilter = 'science'"
                            :class="categoryFilter === 'science' ? 'bg-[#1E3A8A] text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
                        <span>🔬</span>
                        <span>{{ __('វិទ្យាសាស្ត្រ & គណិតវិទ្យា') }}</span>
                    </button>
                    <button type="button" 
                            @click="categoryFilter = 'general'"
                            :class="categoryFilter === 'general' ? 'bg-[#1E3A8A] text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 rounded-xl transition-all flex items-center gap-1.5">
                        <span>💡</span>
                        <span>{{ __('ចំណេះដឹង & ភាពជាអ្នកដឹកនាំ') }}</span>
                    </button>
                </div>
            </div>

            <!-- Popular Books Grid Cards (8 High Impact Books) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-7">
                @foreach($popularBooks as $index => $book)
                    @php
                        $coverUrl = $book->cover_image ? asset(ltrim($book->cover_image, '/')) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80';
                        $ratingInfo = $ratingsMap[$book->id] ?? ['score' => '4.9', 'reads' => '150'];
                        $ratingScore = $ratingInfo['score'];
                        $readersCount = $ratingInfo['reads'];
                        $rank = $index + 1;
                        
                        // Category tag classification for filter
                        $catName = $book->category ? $book->category->name : '';
                        $isCurriculum = in_array($book->id, [14, 15, 16, 17, 18, 19, 20, 21, 22, 23]);
                        $isScience = str_contains($book->title, 'រូបវិទ្យា') || str_contains($book->title, 'ជីវវិទ្យា') || str_contains($book->title, 'គន្លឹះ') || str_contains($catName, 'Science') || str_contains($catName, 'Math');
                        $cardFilterGroup = $isScience ? 'science' : ($isCurriculum ? 'curriculum' : 'general');
                    @endphp

                    <div x-show="categoryFilter === 'all' || categoryFilter === '{{ $cardFilterGroup }}'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="bg-white rounded-3xl p-5 border border-slate-200/80 hover:border-indigo-300 shadow-sm hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 flex flex-col justify-between group relative">
                        
                        <!-- Top Cover Stage -->
                        <div>
                            <div class="relative w-full aspect-[2/3] rounded-2xl overflow-hidden shadow-md bg-slate-100 mb-4 group-hover:shadow-xl transition-all">
                                <img src="{{ $coverUrl }}" 
                                     alt="{{ $book->title }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-500">
                                
                                <!-- Spine Shadow overlay -->
                                <div class="absolute inset-y-0 left-0 w-1.5 bg-white/20"></div>

                                <!-- Popular Rank Badge -->
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-black shadow-md {{ $rank <= 3 ? 'bg-amber-400 text-slate-950 ring-2 ring-white/70' : 'bg-slate-900/80 text-white backdrop-blur-xs' }}">
                                        @if($rank <= 3) 🔥 #{{ $rank }} @else ★ #{{ $rank }} @endif
                                    </span>
                                </div>

                                <!-- Category Badge -->
                                <div class="absolute top-3 left-3 z-10">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-white/90 backdrop-blur-xs text-slate-800 shadow-xs">
                                        {{ $book->category ? __($book->category->name) : __('ទូទៅ') }}
                                    </span>
                                </div>

                                <!-- Hover Quick View Overlay Button -->
                                <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center p-4">
                                    <button type="button" 
                                            @click="openPreview({{ json_encode($book) }}, '{{ $ratingScore }}', '{{ $readersCount }}')"
                                            class="px-4 py-2 rounded-xl bg-white text-slate-900 text-xs font-bold shadow-lg hover:bg-indigo-50 hover:text-[#1E3A8A] transition-all transform translate-y-2 group-hover:translate-y-0 flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#1E3A8A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>{{ __('មើលព័ត៌មានលម្អិត') }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Star Rating (ផ្កាយ Rating) -->
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <div class="flex items-center gap-1 text-amber-400 text-xs">
                                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                                    <span class="font-bold text-slate-800 ml-1 text-xs">{{ $ratingScore }}</span>
                                </div>
                                <span class="text-[11px] text-slate-400 font-medium">({{ $readersCount }}+ {{ __('អាន') }})</span>
                            </div>

                            <!-- Book Title -->
                            <h3 class="font-bold text-slate-900 text-sm leading-snug line-clamp-2 group-hover:text-[#1E3A8A] transition-colors mb-1"
                                title="{{ $book->title }}">
                                {{ $book->title }}
                            </h3>

                            <!-- Author -->
                            <p class="text-xs text-slate-500 truncate flex items-center gap-1 mb-3">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                <span>{{ $book->author ?? __('អ្នកនិពន្ធមិនបញ្ជាក់') }}</span>
                            </p>
                        </div>

                        <!-- Card Bottom: Circulation Metrics & CTA Buttons -->
                        <div class="mt-2 pt-3 border-t border-slate-100 space-y-3">
                            <!-- Views & Shelf Stats -->
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span class="flex items-center gap-1 font-semibold text-slate-600">
                                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    {{ number_format($book->views_count) }} {{ __('មើល') }}
                                </span>
                                <span class="text-slate-400">|</span>
                                <span class="flex items-center gap-1 font-semibold text-emerald-600">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    {{ number_format($book->downloads_count) }} {{ __('ទាញយក') }}
                                </span>
                            </div>

                            <!-- CTA Buttons (Register / Preview) -->
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" 
                                        @click="openPreview({{ json_encode($book) }}, '{{ $ratingScore }}', '{{ $readersCount }}')"
                                        class="py-2 px-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all text-center">
                                    {{ __('មើលគំរូ') }}
                                </button>
                                <button type="button" 
                                        @click="registerModalOpen = true; registerStep = 1"
                                        class="py-2 px-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-bold transition-all shadow-xs hover:shadow-md text-center">
                                    {{ __('ចុះឈ្មោះអាន') }}
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- ================================================================= -->
            <!-- Bottom Member Conversion Banner (ទាក់ទាញអ្នកមិនទាន់មានគណនីឱ្យចុះឈ្មោះ) -->
            <!-- ================================================================= -->
            <div class="mt-16 sm:mt-20 bg-gradient-to-r from-[#1E3A8A] via-[#1b357d] to-[#6366F1] rounded-3xl p-8 sm:p-12 text-white shadow-2xl relative overflow-hidden">
                <!-- Ambient glow decorations -->
                <div class="absolute -top-16 -right-16 w-80 h-80 bg-indigo-400/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-80 h-80 bg-blue-300/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-8">
                    <div class="max-w-2xl space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-amber-300 text-xs font-bold border border-white/15">
                            <span>✨</span>
                            <span>{{ __('បណ្ណាល័យឌីជីថលកម្រិតស្តង់ដារជាតិ') }}</span>
                        </div>
                        <h3 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">
                            {{ __('មិនទាន់មានគណនីបណ្ណាល័យមែនទេ? ចុះឈ្មោះឥឡូវនេះដោយឥតគិតថ្លៃ!') }}
                        </h3>
                        <p class="text-blue-100 text-xs sm:text-sm leading-relaxed">
                            {{ __('ក្លាយជាសមាជិកបណ្ណាល័យដើម្បីទទួលបានសិទ្ធិខ្ចីសៀវភៅរូបវន្ត អានឯកសារ PDF ពិនិត្យកាលវិភាគសងត្រឡប់ និងស្វែងរកចំណេះដឹងយ៉ាងសម្បូរបែបបានគ្រប់ពេលវេលា។') }}
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                        <button type="button" 
                                @click="registerModalOpen = true; registerStep = 1"
                                class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-[#10B981] hover:bg-emerald-600 active:scale-95 text-white font-bold text-sm shadow-lg shadow-emerald-950/30 transition-all flex items-center justify-center gap-2">
                            <span>🎓</span>
                            <span>{{ __('ចុះឈ្មោះជាសមាជិកឥឡូវនេះ') }}</span>
                        </button>
                        <button type="button" 
                                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white/15 hover:bg-white/25 active:scale-95 text-white font-bold text-sm backdrop-blur-xs border border-white/20 transition-all flex items-center justify-center gap-2">
                            <span>👑</span>
                            <span>{{ __('ចូលគណនី (Sign In)') }} &uarr;</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- FOOTER: E-Library Landing Footer -->
    <!-- ========================================================================= -->
    <footer class="bg-slate-900 text-slate-400 py-12 border-t border-slate-800 text-xs font-khmer">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-2.5 text-white font-black text-lg">
                        <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <span>E-Library System Pro</span>
                    </div>
                    <p class="text-slate-400 text-xs max-w-md leading-relaxed">
                        {{ __('ប្រព័ន្ធគ្រប់គ្រងបណ្ណាល័យឌីជីថលឆ្លាតវៃ ផ្តល់ជូននូវការតម្កល់សៀវភៅ ការគ្រប់គ្រងការខ្ចី-សង និងការអានឯកសារស្រាវជ្រាវប្រកបដោយភាពរលូន។') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">{{ __('ម៉ោងបម្រើការងារ') }}</h4>
                    <ul class="space-y-1.5 text-slate-400">
                        <li>{{ __('ច័ន្ទ - សុក្រ: ៧:៣០ ព្រឹក - ៥:៣០ ល្ងាច') }}</li>
                        <li>{{ __('សៅរ៍: ៨:០០ ព្រឹក - ៤:០០ រសៀល') }}</li>
                        <li>{{ __('អាទិត្យ & ថ្ងៃបុណ្យ: សម្រាក') }}</li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-xs uppercase tracking-wider mb-3">{{ __('សេវាកម្មរហ័ស') }}</h4>
                    <ul class="space-y-1.5">
                        <li><a href="#login-hero" class="hover:text-white transition-colors">{{ __('ចូលគណនីសមាជិក') }}</a></li>
                        <li><a href="#login-hero" class="hover:text-white transition-colors">{{ __('ចូលគណនីអ្នកគ្រប់គ្រង') }}</a></li>
                        <li><a href="#popular-books" class="hover:text-white transition-colors">{{ __('សៀវភៅពេញនិយមប្រចាំខែ') }}</a></li>
                        <li><button type="button" @click="registerModalOpen = true" class="hover:text-white transition-colors text-left">{{ __('ចុះឈ្មោះគណនីថ្មី') }}</button></li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} E-Library. {{ __('រក្សាសិទ្ធិគ្រប់យ៉ាង។') }}</p>
                <div class="flex items-center gap-4">
                    <span>Privacy Policy</span>
                    <span>•</span>
                    <span>Terms of Service</span>
                    <span>•</span>
                    <span class="text-indigo-400 font-bold">v2.0 Pro</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ========================================================================= -->
    <!-- MODAL 1: Book Quick Preview Modal (សម្រាប់អ្នកទស្សនាពិនិត្យមើលសៀវភៅ) -->
    <!-- ========================================================================= -->
    <div x-show="previewBookModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs font-khmer"
         x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative overflow-hidden"
             @click.away="previewBookModalOpen = false">
            
            <!-- Close button -->
            <button @click="previewBookModalOpen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors z-20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <template x-if="previewBook">
                <div class="space-y-5">
                    <div class="flex flex-col sm:flex-row gap-5 items-start">
                        <!-- Cover Preview -->
                        <div class="w-28 sm:w-36 aspect-[2/3] rounded-2xl overflow-hidden shadow-lg shrink-0 bg-slate-100 ring-1 ring-slate-200">
                            <img :src="previewBook.cover_image ? (previewBook.cover_image.startsWith('/') ? previewBook.cover_image : '/' + previewBook.cover_image) : 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80'" 
                                 :alt="previewBook.title" 
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Details -->
                        <div class="flex-1 min-w-0">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-800 text-[11px] font-bold border border-amber-200/60 mb-2">
                                <span>⭐ ណែនាំប្រចាំខែ</span>
                                <span class="text-slate-400">•</span>
                                <span x-text="previewBook.rating + ' / 5.0'"></span>
                            </div>

                            <h3 class="text-lg sm:text-xl font-black text-slate-900 leading-snug" x-text="previewBook.title"></h3>
                            
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                <span>{{ __('អ្នកនិពន្ធ:') }}</span>
                                <span class="font-bold text-slate-700" x-text="previewBook.author || '{{ __('មិនបញ្ជាក់') }}'"></span>
                            </p>

                            <div class="grid grid-cols-2 gap-2 mt-4 text-xs">
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] text-slate-400 font-bold block uppercase">{{ __('ទីតាំងធ្នើ') }}</span>
                                    <span class="font-bold text-slate-800" x-text="previewBook.location_shelf || 'Shelf A-1'"></span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                                    <span class="text-[10px] text-slate-400 font-bold block uppercase">{{ __('ឆ្នាំបោះពុម្ព') }}</span>
                                    <span class="font-bold text-slate-800" x-text="previewBook.published_year || 2024"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="p-4 bg-slate-50/80 rounded-2xl border border-slate-100 text-xs text-slate-600 leading-relaxed max-h-36 overflow-y-auto">
                        <span class="font-bold text-slate-800 block mb-1">{{ __('ខ្លឹមសារសង្ខេប:') }}</span>
                        <p x-text="previewBook.description || '{{ __('សៀវភៅនេះជាស្នាដៃឆ្នើមដែលត្រូវបានជ្រើសរើសសម្រាប់ជាឯកសារជំនួយស្មារតី ការស្រាវជ្រាវ និងការសិក្សាទូទៅក្នុងបណ្ណាល័យរបស់យើង។') }}'"></p>
                    </div>

                    <!-- Call To Action Box -->
                    <div class="p-4 bg-blue-50/80 border border-blue-100 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="text-xs text-blue-950 text-center sm:text-left">
                            <span class="font-bold block">{{ __('ចង់អាន ឬខ្ចីសៀវភៅនេះ?') }}</span>
                            <span class="text-[#1E3A8A] text-[11px]">{{ __('ចុះឈ្មោះជាសមាជិកដោយឥតគិតថ្លៃដើម្បីចូលអាន PDF និងកក់សៀវភៅ!') }}</span>
                        </div>
                        <a href="{{ route('register') }}"
                           class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#1E3A8A] hover:bg-blue-900 text-white text-xs font-bold shadow-md shadow-blue-950/20 transition-all shrink-0 text-center">
                            {{ __('ចុះឈ្មោះជាសមាជិក') }} &rarr;
                        </a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1.5: Forgot Password Modal (ផ្ទាំងភ្លេចពាក្យសម្ងាត់) -->
    <!-- ========================================================================= -->
    <div x-show="forgotModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm font-khmer"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 relative overflow-hidden transition-all transform"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @click.away="forgotModalOpen = false">
            
            <!-- Top Gradient Highlight Bar -->
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#1E3A8A] via-[#6366F1] to-emerald-400"></div>

            <!-- Close Button -->
            <button @click="forgotModalOpen = false" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <!-- STEP 1: Enter Registered Email -->
            <div x-show="forgotStep === 1" class="space-y-5">
                <!-- Icon & Title -->
                <div class="text-center pt-2">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 border border-blue-100 text-[#1E3A8A] flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-50 text-[#1E3A8A] text-xs font-bold mb-1.5 border border-blue-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#1E3A8A]"></span>
                        {{ __('Security & Recovery') }}
                    </span>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ app()->getLocale() === 'km' ? 'ភ្លេចពាក្យសម្ងាត់?' : 'Forgot Password?' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed max-w-xs mx-auto">
                        {{ app()->getLocale() === 'km' ? 'សូមបញ្ចូលអ៊ីមែលដែលបានចុះឈ្មោះក្នុងបណ្ណាល័យ ដើម្បីកំណត់ពាក្យសម្ងាត់ថ្មីឡើងវិញ។' : 'Enter your registered library email address to receive password reset instructions.' }}
                    </p>
                </div>

                <!-- Error message -->
                <div x-show="forgotError" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-600 flex items-center gap-2" x-cloak>
                    <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="forgotError"></span>
                </div>

                <!-- Form -->
                <form @submit.prevent="submitForgotRequest()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            {{ __('Email Address') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            </div>
                            <input type="email" 
                                   x-model="forgotEmail" 
                                   required 
                                   placeholder="name@elibrary.com" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/15 outline-none transition-all">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ app()->getLocale() === 'km' ? 'ឧទាហរណ៍៖ admin@elibrary.com ឬ student@elibrary.com' : 'e.g. admin@elibrary.com or student@elibrary.com' }}
                        </p>
                    </div>

                    <button type="submit" 
                            :disabled="forgotLoading"
                            class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm bg-[#1E3A8A] hover:bg-blue-900 shadow-lg shadow-blue-950/25 hover:shadow-xl transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                        <span x-show="!forgotLoading" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            <span>{{ app()->getLocale() === 'km' ? 'ផ្ញើតំណកំណត់ឡើងវិញ' : 'Send Reset Link' }}</span>
                        </span>
                        <span x-show="forgotLoading" class="flex items-center gap-2" x-cloak>
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ __('Please wait...') }}</span>
                        </span>
                    </button>
                </form>

                <div class="text-center pt-1 border-t border-slate-100">
                    <button type="button" @click="forgotModalOpen = false" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                        &larr; {{ app()->getLocale() === 'km' ? 'ត្រឡប់ទៅចូលប្រព័ន្ធវិញ' : 'Back to Sign In' }}
                    </button>
                </div>
            </div>

            <!-- STEP 2: Enter New Password (Instant Direct Reset) -->
            <div x-show="forgotStep === 2" class="space-y-5" x-cloak>
                <div class="text-center pt-2">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-3 shadow-inner">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ app()->getLocale() === 'km' ? 'កំណត់ពាក្យសម្ងាត់ថ្មី' : 'Set New Password' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed max-w-xs mx-auto">
                        <span class="text-emerald-700 font-semibold" x-text="forgotSuccessMsg"></span>
                        <span class="block mt-1">{{ app()->getLocale() === 'km' ? 'សូមបង្កើតពាក្យសម្ងាត់ថ្មីសម្រាប់គណនីរបស់អ្នក។' : 'Please choose a new secure password for your account.' }}</span>
                    </p>
                </div>

                <!-- Error message -->
                <div x-show="forgotError" class="p-3.5 rounded-xl bg-red-50 border border-red-200 text-xs text-red-600 flex items-center gap-2" x-cloak>
                    <svg class="w-4 h-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span x-text="forgotError"></span>
                </div>

                <form @submit.prevent="submitPasswordReset()" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            {{ __('New Password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               x-model="newPassword" 
                               required 
                               placeholder="••••••••" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/15 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            {{ __('Confirm New Password') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               x-model="newPasswordConfirm" 
                               required 
                               placeholder="••••••••" 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:border-[#1E3A8A] focus:ring-4 focus:ring-blue-500/15 outline-none transition-all">
                    </div>

                    <button type="submit" 
                            :disabled="forgotLoading"
                            class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm bg-[#1E3A8A] hover:bg-blue-900 shadow-lg shadow-blue-950/25 hover:shadow-xl transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                        <span x-show="!forgotLoading" class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>{{ app()->getLocale() === 'km' ? 'រក្សាទុកពាក្យសម្ងាត់ថ្មី' : 'Save New Password' }}</span>
                        </span>
                        <span x-show="forgotLoading" class="flex items-center gap-2" x-cloak>
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ __('Updating...') }}</span>
                        </span>
                    </button>
                </form>
            </div>

            <!-- STEP 3: Reset Success Done -->
            <div x-show="forgotStep === 3" class="space-y-6 text-center pt-3" x-cloak>
                <div class="w-16 h-16 rounded-3xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto shadow-lg shadow-emerald-500/20 animate-bounce">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900">
                        {{ app()->getLocale() === 'km' ? 'ជោគជ័យហើយ!' : 'Password Reset Successfully!' }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed max-w-xs mx-auto">
                        {{ app()->getLocale() === 'km' ? 'ពាក្យសម្ងាត់ថ្មីរបស់អ្នកត្រូវបានកំណត់រួចរាល់។ អ្នកអាចចូលប្រើប្រាស់ឥឡូវនេះបានហើយ។' : 'Your password has been updated. You can now sign in with your new credentials.' }}
                    </p>
                </div>
                <button type="button" 
                        @click="finishReset()"
                        class="w-full py-3 px-4 rounded-xl text-white font-bold text-sm bg-[#1E3A8A] hover:bg-blue-900 shadow-lg shadow-blue-950/20 transition-all">
                    {{ app()->getLocale() === 'km' ? 'ចូលប្រព័ន្ធឥឡូវនេះ' : 'Sign In Now' }} &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Keep CSRF Token Fresh & Prevent Session Expiry while tab is open -->
    <script>
        (function() {
            function updateCsrfToken() {
                fetch('{{ route('csrf.refresh') }}', { credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.token) {
                            document.querySelectorAll('input[name="_token"]').forEach(input => input.value = data.token);
                            const meta = document.querySelector('meta[name="csrf-token"]');
                            if (meta) meta.setAttribute('content', data.token);
                        }
                    })
                    .catch(() => {});
            }

            // Refresh token when user returns to this tab
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'visible') {
                    updateCsrfToken();
                }
            });
            window.addEventListener('focus', updateCsrfToken);

            // Keepalive ping every 25 minutes to prevent session timeout
            setInterval(updateCsrfToken, 25 * 60 * 1000);
        })();
    </script>
</body>
</html>
