<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Language Switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'km'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

// Role Switcher for seamless demo testing (3 Roles: Super Admin, Manager, Member)
Route::get('/switch-role/{role}', function ($role) {
    if (in_array($role, ['admin', 'manager', 'member'])) {
        $targetUser = User::where('role', $role)->first();
        if ($targetUser) {
            Auth::login($targetUser);
            $roleLabel = match ($role) {
                'admin' => 'Super Admin',
                'manager' => 'Manager (ប្រធានបណ្ណាល័យ)',
                default => 'Member (សមាជិក)',
            };
            return back()->with('success', __('Switched active profile to :role (:name)', ['role' => $roleLabel, 'name' => $targetUser->name]));
        }
    }
    return back();
})->name('role.switch');

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::post('/reset-password-direct', [AuthController::class, 'resetPasswordDirect'])->name('password.reset.direct');
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.refresh');

// Real Google OAuth 2.0
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Core App Routes: Redirect to login if guest, or dashboard if already authenticated
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Books
    Route::get('/books/bundles', [BookController::class, 'bundles'])->name('books.bundles');
    Route::get('/books/bundles/{bundle}', [BookController::class, 'showBundle'])->name('books.bundles.show');
    Route::get('/books-quick-search', [BookController::class, 'quickSearch'])->name('books.quick-search');
    Route::post('/books/{book}/record-view', [BookController::class, 'recordView'])->name('books.record-view');
    Route::get('/books/{book}/download', [BookController::class, 'downloadPdf'])->name('books.download');
    Route::get('/books/{book}/read', [BookController::class, 'readPdf'])->name('books.read');
    Route::post('/books/{book}/extract-cover', [BookController::class, 'extractCover'])->name('books.extract-cover')->middleware('admin');
    Route::resource('books', BookController::class);


    // Member self-service reservation
    Route::post('/books/{book}/reserve', [BorrowController::class, 'reserve'])->name('books.reserve');

    // Borrow & Return Circulation
    Route::get('/borrows/check-patron', [BorrowController::class, 'checkPatron'])->name('borrows.check-patron')->middleware('admin');
    Route::get('/borrows', [BorrowController::class, 'index'])->name('borrows.index');
    Route::post('/borrows', [BorrowController::class, 'store'])->name('borrows.store')->middleware('admin');
    Route::post('/borrows/{borrow}/return', [BorrowController::class, 'returnBook'])->name('borrows.return')->middleware('admin');
    Route::post('/borrows/{borrow}/settle-fine', [BorrowController::class, 'settleFine'])->name('borrows.settle-fine')->middleware('admin');
    Route::post('/borrows/{borrow}/waive-fine', [BorrowController::class, 'waiveFine'])->name('borrows.waive-fine')->middleware('admin');

    // Reservations
    Route::get('/reservations', [BorrowController::class, 'reservations'])->name('reservations.index');
    Route::get('/reservations/user-status', [BorrowController::class, 'checkUserReservationStatus'])->name('reservations.user-status');
    Route::post('/reservations/{reservation}/approve', [BorrowController::class, 'approveReservation'])->name('reservations.approve')->middleware('admin');
    Route::post('/reservations/{reservation}/fulfill', [BorrowController::class, 'fulfillReservation'])->name('reservations.fulfill')->middleware('admin');
    Route::post('/reservations/{reservation}/cancel', [BorrowController::class, 'cancelReservation'])->name('reservations.cancel');
    Route::post('/reservations/{reservation}/force-cancel', [BorrowController::class, 'forceCancelReservation'])->name('reservations.force-cancel')->middleware('admin');

    // Admin Notifications & Messages
    Route::get('/admin/notifications/unread-stream', [BorrowController::class, 'getUnreadNotifications'])->name('notifications.unread-stream');
    Route::post('/admin/notifications/mark-all-read', [BorrowController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/clear-all', [BorrowController::class, 'clearAllNotifications'])->name('notifications.clear-all');
    Route::post('/notifications/{notification}/dismiss', [BorrowController::class, 'dismissNotification'])->name('notifications.dismiss');
    Route::post('/admin/notifications/{notification}/read', [BorrowController::class, 'readNotification'])->name('notifications.read');

    // 8. Settings & Personal Account Configuration
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/profile', [SettingController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password.update');
    Route::post('/settings/preferences', [SettingController::class, 'updatePreferences'])->name('settings.preferences.update');

    // Admin & Manager Management & System Configuration
    Route::middleware('admin')->group(function () {
        Route::resource('members', MemberController::class);
        Route::post('/members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('members.toggle-status');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/{type?}', [ReportController::class, 'exportCsv'])->name('reports.export');

        // Admin & Manager System Configuration
        Route::post('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('/settings/users/{user}/role', [SettingController::class, 'updateUserRole'])->name('settings.users.role');
        Route::post('/settings/users/create', [SettingController::class, 'createUser'])->name('settings.users.create');
        Route::delete('/settings/users/{user}', [SettingController::class, 'deleteUser'])->name('settings.users.delete');
        Route::post('/settings/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::post('/settings/system', [SettingController::class, 'updateSystem'])->name('settings.system.update');

        // Super Admin: Database & Files Backup & Recovery to Drive D
        Route::prefix('backups')->name('backups.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/run', [BackupController::class, 'run'])->name('run');
            Route::get('/{id}/download', [BackupController::class, 'download'])->name('download');
            Route::post('/{id}/restore', [BackupController::class, 'restore'])->name('restore');
            Route::delete('/{id}', [BackupController::class, 'destroy'])->name('destroy');
            Route::post('/settings', [BackupController::class, 'updateSettings'])->name('settings.update');
        });
    });
});
