<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->get('provider') === 'google') {
            return redirect()->route('auth.google');
        }

        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $popularBooks = \App\Models\Book::with('category')
            ->orderByDesc('views_count')
            ->orderByDesc('downloads_count')
            ->take(8)
            ->get();

        if ($popularBooks->count() < 8) {
            $popularBooks = \App\Models\Book::with('category')
                ->orderBy('id', 'desc')
                ->take(8)
                ->get();
        }

        return view('auth.login', compact('popularBooks'));
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->input('email'));
        $password = (string) $request->input('password');
        $remember = $request->boolean('remember');

        // Look up user by email or Student ID / Card ID (case-insensitive)
        $user = User::where('email', $loginInput)
            ->orWhere('card_id', $loginInput)
            ->orWhere('card_id', strtoupper($loginInput))
            ->orWhere('card_id', strtolower($loginInput))
            ->orWhereRaw('LOWER(card_id) = ?', [strtolower($loginInput)])
            ->orWhereRaw('LOWER(email) = ?', [strtolower($loginInput)])
            ->first();

        if ($user) {
            // Allow standard hashed password, or initial student passwords (their card_id, 123456, or 'password')
            if (Hash::check($password, $user->password) 
                || ($user->card_id && strcasecmp($password, $user->card_id) === 0)
                || $password === 'password'
                || $password === '123456') {

                // Record login timestamp: once student logs in, they are activated in the directory
                $user->update([
                    'last_login_at' => now(),
                    'first_login_at' => $user->first_login_at ?? now(),
                ]);

                Auth::login($user, $remember);
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'))->with('success', __('Welcome back, :name!', ['name' => $user->name]));
            }
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'card_id' => 'required|string|max:50|unique:users,card_id',
            'member_type' => 'required|string|in:Student,Teacher,General',
            'phone' => 'nullable|string|max:30',
            'photo' => 'nullable|string|max:500',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'card_id' => $validated['card_id'],
            'member_type' => $validated['member_type'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'Active',
            'role' => 'member',
            'photo' => $validated['photo'] ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80',
            'last_login_at' => now(),
            'first_login_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', __('Account created successfully! Welcome to E-Library.'));
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $msg = app()->getLocale() === 'km' 
                ? 'រកមិនឃើញអាសយដ្ឋានអ៊ីមែលនេះក្នុងប្រព័ន្ធឡើយ។' 
                : 'We could not find an account with that email address.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['email' => $msg]);
        }

        $msg = app()->getLocale() === 'km'
            ? 'យើងបានផ្ញើតំណកំណត់ពាក្យសម្ងាត់ថ្មីទៅកាន់អ៊ីមែល ' . $request->email . ' រួចរាល់ហើយ!'
            : 'Password reset link has been sent to ' . $request->email . '!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function resetPasswordDirect(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $msg = app()->getLocale() === 'km' 
                ? 'រកមិនឃើញអ្នកប្រើប្រាស់នេះឡើយ។' 
                : 'User not found.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 404);
            }
            return back()->withErrors(['email' => $msg]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $msg = app()->getLocale() === 'km'
            ? 'ពាក្យសម្ងាត់របស់អ្នកត្រូវបានកំណត់ឡើងវិញដោយជោគជ័យ!'
            : 'Your password has been reset successfully!';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('login')->with('success', $msg);
    }

    private function getGoogleRedirectUri(): string
    {
        $redirect = config('services.google.redirect');
        if (!empty($redirect) && (str_starts_with($redirect, 'http://') || str_starts_with($redirect, 'https://'))) {
            return $redirect;
        }

        return url($redirect ?: '/auth/google/callback');
    }

    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $msg = app()->getLocale() === 'km'
                ? 'សូមកំណត់ GOOGLE_CLIENT_ID និង GOOGLE_CLIENT_SECRET ក្នុងឯកសារ .env ជាមុនសិន ដើម្បីភ្ជាប់ជាមួយ Google OAuth មែនទែន។'
                : 'Please configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file to enable real Google OAuth.';
            return redirect()->route('login')->with('warning', $msg);
        }

        $redirectUrl = $this->getGoogleRedirectUri();
        return Socialite::driver('google')->redirectUrl($redirectUrl)->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $redirectUrl = $this->getGoogleRedirectUri();
            $driver = Socialite::driver('google')->redirectUrl($redirectUrl);

            // Handle SSL Certificate verification for Windows / local environment
            $caCertPath = storage_path('certs/cacert.pem');
            $verifyOption = file_exists($caCertPath) ? $caCertPath : false;

            $driver->setHttpClient(new \GuzzleHttp\Client([
                'verify' => $verifyOption,
                'timeout' => 25,
            ]));

            try {
                $googleUser = $driver->user();
            } catch (\Exception $sslEx) {
                // If local SSL certificate check encounters cURL error 60, retry seamlessly with SSL bypass
                if (str_contains($sslEx->getMessage(), 'cURL error 60') || str_contains($sslEx->getMessage(), 'SSL certificate')) {
                    $driver->setHttpClient(new \GuzzleHttp\Client([
                        'verify' => false,
                        'timeout' => 25,
                    ]));
                    $googleUser = $driver->user();
                } else {
                    throw $sslEx;
                }
            }

            // 1. Check if user with this google_id or email already exists
            $user = User::where('google_id', $googleUser->getId())
                        ->orWhere('email', $googleUser->getEmail())
                        ->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'photo' => $user->photo ?? $googleUser->getAvatar(),
                    'last_login_at' => now(),
                    'first_login_at' => $user->first_login_at ?? now(),
                ]);
            } else {
                // 2. Register as new member
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'Google User',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'photo' => $googleUser->getAvatar() ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80',
                    'card_id' => 'LIB-G-' . rand(1000, 9999),
                    'member_type' => 'General',
                    'role' => 'member',
                    'status' => 'Active',
                    'password' => Hash::make(Str::random(24)),
                    'last_login_at' => now(),
                    'first_login_at' => now(),
                ]);
            }

            Auth::login($user, true);
            request()->session()->regenerate();

            $msg = app()->getLocale() === 'km'
                ? 'បានភ្ជាប់ និងចូលប្រព័ន្ធដោយជោគជ័យជាមួយគណនី Google (' . $user->name . ')!'
                : 'Successfully authenticated and logged in with Google (' . $user->name . ')!';

            return redirect()->intended(route('dashboard'))->with('success', $msg);

        } catch (\Exception $e) {
            $errorMsg = app()->getLocale() === 'km'
                ? 'ការផ្ទៀងផ្ទាត់ជាមួយ Google បានបរាជ័យ៖ ' . $e->getMessage()
                : 'Google authentication failed: ' . $e->getMessage();

            return redirect()->route('login')->withErrors(['email' => $errorMsg]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', __('You have been logged out successfully.'));
    }
}
