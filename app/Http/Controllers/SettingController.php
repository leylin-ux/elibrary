<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    /**
     * Display the comprehensive Settings & Configuration dashboard.
     */
    public function index(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        $isAdmin = $user ? $user->isAdmin() : true;

        // Grouped application settings
        $settings = Setting::getAllGrouped();

        // Active tab determination
        $activeTab = $request->input('tab', $isAdmin ? 'general' : 'profile');

        $users = collect();
        $roleCounts = [
            'total' => 0,
            'admin' => 0,
            'librarian' => 0,
            'member' => 0,
            'suspended' => 0,
        ];
        $permissionsMatrix = [];

        if ($isAdmin) {
            // Section 2: Manage Users & Roles Query
            $usersQuery = User::query();

            // Default directory filter: only show accounts that are staff/admin, or members who have actually logged in!
            // Pre-imported student and lecturer data is kept safely in DB for login, but hidden from directory until their first login.
            if ($request->get('status_filter') === 'pending_login') {
                $usersQuery->whereNull('last_login_at')->where('role', 'member');
            } else {
                $usersQuery->where(function ($q) {
                    $q->where('role', '!=', 'member')
                      ->orWhere('member_type', 'Staff')
                      ->orWhereNotNull('last_login_at');
                });

                if ($request->filled('status_filter') && $request->status_filter !== 'all') {
                    $usersQuery->where('status', $request->status_filter);
                }
            }

            if ($request->filled('role_filter') && $request->role_filter !== 'all') {
                $usersQuery->where('role', $request->role_filter);
            }

            if ($request->filled('search_user')) {
                $s = trim($request->search_user);
                $usersQuery->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('email', 'like', "%{$s}%")
                      ->orWhere('card_id', 'like', "%{$s}%")
                      ->orWhere('phone', 'like', "%{$s}%");
                });
            }

            $users = $usersQuery->withCount([
                'borrows as total_loans_count',
                'borrows as active_loans_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
            ])->latest('id')->paginate(12)->withQueryString();

            // Role & User summary counts for active directory users
            $activeDirectoryUsers = User::where(function ($q) {
                $q->where('role', '!=', 'member')
                  ->orWhere('member_type', 'Staff')
                  ->orWhereNotNull('last_login_at');
            });

            $roleCounts = [
                'total' => (clone $activeDirectoryUsers)->count(),
                'admin' => User::where('role', 'admin')->count(),
                'manager' => User::where('role', 'manager')->count(),
                'member' => (clone $activeDirectoryUsers)->where('role', 'member')->count(),
                'suspended' => (clone $activeDirectoryUsers)->where('status', 'Suspended')->count(),
                'pending_login' => User::whereNull('last_login_at')->where('role', 'member')->count(),
            ];

            // RBAC Permissions Matrix mapping (3 Official Roles: Super Admin, Manager, Member)
            $permissionsMatrix = [
                [
                    'module' => 'Catalog & Book Management',
                    'module_km' => 'ការគ្រប់គ្រងសៀវភៅ & ស្តុក PDF',
                    'description' => 'Add new books, edit shelf locations, upload PDF documents, and update inventory counts.',
                    'description_km' => 'បញ្ចូលសៀវភៅថ្មី កែសម្រួលទីតាំងទូ បញ្ចូលឯកសារ PDF និងគ្រប់គ្រងស្តុក។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Exclusive Book Deletion',
                    'module_km' => 'សិទ្ធិផ្ដាច់មុខក្នុងការលុបសៀវភៅ',
                    'description' => 'Permanently delete worn out, damaged, or lost books from the system catalog.',
                    'description_km' => 'សម្រេចលុបសៀវភៅចាស់ទ្រុឌទ្រោម ឬបាត់បង់អចិន្ត្រៃយ៍ចេញពីប្រព័ន្ធបណ្ណាល័យ។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Circulation Desk (Borrow & Return)',
                    'module_km' => 'តុសេវាខ្ចី-សង (ឱ្យខ្ចី & ទទួលសៀវភៅសង)',
                    'description' => 'Issue physical book loans, inspect book condition upon return, and track due dates.',
                    'description_km' => 'កត់ត្រាការឱ្យខ្ចីសៀវភៅ ទទួលសៀវភៅសង និងពិនិត្យស្ថានភាពសៀវភៅ។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Waive Overdue Fines',
                    'module_km' => 'ការសម្រេចលើកលែងប្រាក់ពិន័យ (Waive Fine)',
                    'description' => 'Exempt fines for exceptional cases (medical excuse, official school errand) with audit log.',
                    'description_km' => 'ចុចលើកលែងប្រាក់ពិន័យចំពោះករណីពិសេស (មានលិខិតបញ្ជាក់ជំងឺ ឬធុរៈសាលា) ជាមួយកំណត់ហេតុ។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Reservation Holds & Force Cancel',
                    'module_km' => 'ការអនុម័ត & លុបចោលការកក់ពិសេស',
                    'description' => 'Approve hold requests, set pickup deadlines, or force cancel reservations for urgent needs.',
                    'description_km' => 'អនុម័តសំណើកក់សៀវភៅ កំណត់ម៉ោងយក ឬបដិសេធ/លុបចោលការកក់ពេលមានធុរៈបន្ទាន់។',
                    'admin' => true,
                    'manager' => true,
                    'member' => 'Reserve Only',
                ],
                [
                    'module' => 'Member Management & Moderation',
                    'module_km' => 'ការគ្រប់គ្រងសមាជិក & ផ្អាកគណនី (Moderation)',
                    'description' => 'Inspect borrowing history, suspend or reactivate patrons who violate library rules.',
                    'description_km' => 'ពិនិត្យប្រវត្តិខ្ចី និងផ្អាកគណនី (Suspend/Block) សមាជិកដែលប្រព្រឹត្តខុសបទបញ្ជា។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Managerial Reports & Audit Exports',
                    'module_km' => 'របាយការណ៍ ៤ ប្រភេទស្នូល & ទាញយក CSV/PDF',
                    'description' => 'Top books, most active patrons, monthly fines & waivers, overdue/lost books export.',
                    'description_km' => 'របាយការណ៍សៀវភៅអានច្រើន, សមាជិកសកម្ម, ប្រាក់ពិន័យប្រចាំខែ, សៀវភៅបាត់បង់ និង Export។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Library Policies & Rules Configuration',
                    'module_km' => 'កំណត់គោលការណ៍បណ្ណាល័យ (Policies)',
                    'description' => 'Configure max loan days, fine rate per day (KHR/USD), and concurrent book limits.',
                    'description_km' => 'កំណត់ចំនួនថ្ងៃខ្ចីអតិបរមា, អត្រាប្រាក់ពិន័យ ៛/ថ្ងៃ, និងកូតាចំនួនសៀវភៅដែលអាចខ្ចី។',
                    'admin' => true,
                    'manager' => true,
                    'member' => false,
                ],
                [
                    'module' => 'Super Administrator Account Protection',
                    'module_km' => 'ការការពារគណនី Super Admin',
                    'description' => 'Super Admin account is permanently protected and cannot be deleted or demoted by Manager.',
                    'description_km' => 'គណនី Super Admin ត្រូវបានការពារជាដាច់ខាត មិនអាចលុប ឬទម្លាក់តំណែងដោយ Manager ឡើយ។',
                    'admin' => true,
                    'manager' => 'Protected 🔒',
                    'member' => false,
                ],
            ];
        }

        return view('settings.index', compact(
            'isAdmin',
            'user',
            'settings',
            'activeTab',
            'users',
            'roleCounts',
            'permissionsMatrix'
        ));
    }

    /**
     * 1. Update Library Information (ព័ត៌មានបណ្ណាល័យ)
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'library_name' => 'required|string|max:255',
            'library_name_km' => 'nullable|string|max:255',
            'library_tagline' => 'nullable|string|max:255',
            'library_tagline_km' => 'nullable|string|max:255',
            'library_email' => 'required|email|max:255',
            'library_phone' => 'required|string|max:100',
            'library_address' => 'required|string|max:500',
            'library_address_km' => 'nullable|string|max:500',
            'library_hours' => 'required|string|max:255',
            'library_hours_km' => 'nullable|string|max:255',
            'library_website' => 'nullable|url|max:255',
            'library_logo' => 'nullable|string|max:500',
            'library_logo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        // If a logo file was uploaded
        if ($request->hasFile('library_logo_file')) {
            $file = $request->file('library_logo_file');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/settings', $filename, 'public');
            $validated['library_logo'] = Storage::url($path);
        }

        unset($validated['library_logo_file']);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value, 'general');
        }

        \App\Models\ActivityLog::log(
            'settings_updated',
            __(':admin បានកែប្រែព័ត៌មានទូទៅរបស់បណ្ណាល័យ', ['admin' => auth()->user()?->name ?? 'Admin'])
        );

        return redirect()->route('settings.index', ['tab' => 'general'])
            ->with('success', __('Library profile information updated successfully!'));
    }

    /**
     * 2. Update a User's Role & Status (គ្រប់គ្រងអ្នកប្រើប្រាស់ និងតួនាទី)
     */
    public function updateUserRole(Request $request, User $user)
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'role' => 'required|in:admin,manager,member',
            'status' => 'required|in:Active,Temporary,Suspended',
            'member_type' => 'nullable|string|max:50',
            'card_id' => 'nullable|string|max:50|unique:users,card_id,' . $user->id,
        ]);

        // SUPER ADMIN PROTECTION: Manager cannot modify Super Admin profile
        if ($user->role === 'admin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return back()->with('error', __('មានតែ Super Administrator ទើបមានសិទ្ធិកែប្រែគណនី Super Admin បាន!'));
        }

        // SUPER ADMIN PROTECTION: Manager cannot promote anyone to Super Admin
        if ($validated['role'] === 'admin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return back()->with('error', __('មានតែ Super Administrator ទើបមានសិទ្ធិតម្លើងតួនាទីជា Super Admin!'));
        }

        // Security check: Prevent self-demotion
        if ($currentUser && $currentUser->id === $user->id && $validated['role'] !== $user->role) {
            return back()->with('error', __('You cannot change your own role.'));
        }

        $user->update($validated);

        \App\Models\ActivityLog::log(
            'user_updated',
            __(':admin បានកែប្រែតួនាទីគណនី :name ទៅជា :role', [
                'admin' => $currentUser?->name ?? 'Admin',
                'name' => $user->name,
                'role' => ucfirst($validated['role']),
            ]),
            $user
        );

        return redirect()->route('settings.index', ['tab' => 'users'])
            ->with('success', __('User ":name" role & status updated successfully!', ['name' => $user->name]));
    }

    /**
     * 2. Create a new User Account (Super Admin, Manager, Member)
     */
    public function createUser(Request $request)
    {
        $currentUser = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,manager,member',
            'member_type' => 'nullable|string|max:50',
            'card_id' => 'nullable|string|unique:users,card_id|max:50',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:Active,Temporary,Suspended',
        ]);

        // SUPER ADMIN PROTECTION: Manager cannot create a Super Admin
        if ($validated['role'] === 'admin' && (!$currentUser || !$currentUser->isSuperAdmin())) {
            return back()->with('error', __('មានតែ Super Administrator ទើបមានសិទ្ធិបង្កើតគណនី Super Admin ថ្មីបាន!'));
        }

        if (empty($validated['member_type'])) {
            $validated['member_type'] = match ($validated['role']) {
                'admin', 'manager' => 'Staff',
                default => 'Student',
            };
        }

        // Auto-generate card ID if not supplied
        if (empty($validated['card_id'])) {
            $prefix = match ($validated['role']) {
                'admin' => 'ADM',
                'manager' => 'MGR',
                default => 'STU',
            };
            $validated['card_id'] = $prefix . '-' . date('Y') . '-' . str_pad((string) (User::max('id') + 1), 3, '0', STR_PAD_LEFT);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['photo'] = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80';
        $validated['last_login_at'] = now();
        $validated['first_login_at'] = now();

        $newUser = User::create($validated);

        \App\Models\ActivityLog::log(
            'user_created',
            __(':admin បានបង្កើតគណនីថ្មី :name (:role, ID: :card)', [
                'admin' => $currentUser?->name ?? 'Admin',
                'name' => $newUser->name,
                'role' => ucfirst($newUser->role),
                'card' => $newUser->card_id,
            ]),
            $newUser
        );

        return redirect()->route('settings.index', ['tab' => 'users'])
            ->with('success', __('User account for :name (:role) created successfully with Card ID :card!', [
                'name' => $newUser->name,
                'role' => ucfirst($newUser->role),
                'card' => $newUser->card_id,
            ]));
    }

    /**
     * Delete a User Account (Strict Super Admin Protection)
     */
    public function deleteUser(User $user)
    {
        $currentUser = Auth::user();

        // 1. SUPER ADMIN PROTECTION: Super Admin cannot be deleted by anyone!
        if ($user->role === 'admin') {
            return back()->with('error', __('គណនី Super Administrator ត្រូវបានការពារជាដាច់ខាត! មិនអាចលុបបានឡើយ (Super Admin accounts cannot be deleted).'));
        }

        // 2. Prevent deleting self
        if ($currentUser && $currentUser->id === $user->id) {
            return back()->with('error', __('You cannot delete your own currently signed-in account.'));
        }

        // 3. Prevent deleting if active loans
        if ($user->activeBorrows()->exists()) {
            return back()->with('error', __('Cannot delete this user because they currently have active book loans. Please return all books first.'));
        }

        $userName = $user->name;
        $user->delete();

        \App\Models\ActivityLog::log(
            'user_deleted',
            __(':admin បានលុបគណនីអ្នកប្រើប្រាស់ :name ចេញពីប្រព័ន្ធ', [
                'admin' => $currentUser?->name ?? 'Admin',
                'name' => $userName,
            ]),
            null
        );

        return redirect()->route('settings.index', ['tab' => 'users'])
            ->with('success', __('User account ":name" deleted successfully.', ['name' => $userName]));
    }

    /**
     * 3. Update Notification Settings (ការកំណត់ការជូនដំណឹង)
     */
    public function updateNotifications(Request $request)
    {
        $keys = [
            'enable_email_notifications' => $request->boolean('enable_email_notifications') ? '1' : '0',
            'notify_new_reservation' => $request->boolean('notify_new_reservation') ? '1' : '0',
            'notify_overdue_loans' => $request->boolean('notify_overdue_loans') ? '1' : '0',
            'reminder_days_before_due' => (string) max(1, min(7, (int) $request->input('reminder_days_before_due', 2))),
            'notify_daily_fine' => $request->boolean('notify_daily_fine') ? '1' : '0',
            'enable_sound_alerts' => $request->boolean('enable_sound_alerts') ? '1' : '0',
            'broadcast_announcement' => (string) $request->input('broadcast_announcement', ''),
            'broadcast_announcement_km' => (string) $request->input('broadcast_announcement_km', ''),
            'enable_broadcast_banner' => $request->boolean('enable_broadcast_banner') ? '1' : '0',
        ];

        foreach ($keys as $k => $v) {
            Setting::set($k, $v, 'notifications');
        }

        return redirect()->route('settings.index', ['tab' => 'notifications'])
            ->with('success', __('Notification preferences and broadcast announcement updated successfully!'));
    }

    /**
     * 4. Update System Policies & Circulation Rules (ការកំណត់គោលការណ៍បណ្ណាល័យ)
     */
    public function updateSystem(Request $request)
    {
        $validated = $request->validate([
            'standard_loan_days' => 'required|numeric|min:1|max:90',
            'max_books_student' => 'required|numeric|min:1|max:20',
            'max_books_teacher' => 'required|numeric|min:1|max:50',
            'max_books_general' => 'required|numeric|min:1|max:20',
            'daily_fine_rate' => 'required|numeric|min:0|max:20',
            'daily_fine_rate_khr' => 'nullable|numeric|min:0|max:100000',
            'grace_period_days' => 'required|numeric|min:0|max:14',
            'hold_pickup_window_hours' => 'required|numeric|min:1|max:168',
            'default_language' => 'required|in:km,en',
        ]);

        $validated['allow_pdf_downloads'] = $request->boolean('allow_pdf_downloads') ? '1' : '0';
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode') ? '1' : '0';

        foreach ($validated as $k => $v) {
            Setting::set($k, (string) $v, 'system');
        }

        \App\Models\ActivityLog::log(
            'policy_updated',
            __(':admin បានកែសម្រួលគោលការណ៍បណ្ណាល័យ (រយៈពេលខ្ចី :days ថ្ងៃ, ពិន័យ :khr ៛/ថ្ងៃ)', [
                'admin' => auth()->user()?->name ?? 'Admin',
                'days' => $validated['standard_loan_days'],
                'khr' => number_format((float)($validated['daily_fine_rate_khr'] ?? 500)),
            ])
        );

        return redirect()->route('settings.index', ['tab' => 'system'])
            ->with('success', __('System policies & circulation borrowing rules saved successfully!'));
    }

    /**
     * Update Current User Personal Profile (ព័ត៌មានគណនីផ្ទាល់ខ្លួន)
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', __('Please sign in to update your account.'));
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'photo' => 'nullable|string|max:500',
            'photo_file' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        if ($request->hasFile('photo_file')) {
            $file = $request->file('photo_file');
            $filename = 'user_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/avatars', $filename, 'public');
            $validated['photo'] = Storage::url($path);
        } else {
            unset($validated['photo']);
        }

        unset($validated['photo_file']);

        $user->update($validated);

        return back()->with('success', __('Your personal profile information has been updated successfully!'));
    }

    /**
     * Change Current User Password (ផ្លាស់ប្តូរពាក្យសម្ងាត់)
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', __('Please sign in to update your account.'));
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => __('The provided current password does not match our records.'),
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', __('Your password has been changed successfully!'));
    }

    /**
     * Update Current User Personal Settings & Preferences (ការកំណត់ផ្ទាល់ខ្លួន)
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if (!$user) {
            return redirect()->route('login')->with('error', __('Please sign in to update your account.'));
        }

        $validated = $request->validate([
            'preferred_locale' => 'required|in:km,en',
        ]);

        $updateData = [
            'preferred_locale' => $validated['preferred_locale'],
            'notify_email' => $request->boolean('notify_email'),
            'notify_sound' => $request->boolean('notify_sound'),
            'notify_borrow_reminders' => $request->boolean('notify_borrow_reminders'),
            'notify_hold_ready' => $request->boolean('notify_hold_ready'),
        ];

        $user->update($updateData);

        // Apply preferred locale immediately to session
        session(['locale' => $validated['preferred_locale']]);

        return back()->with('success', __('Your personal settings and notification preferences have been saved!'));
    }
}
