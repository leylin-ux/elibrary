<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if (!app()->runningInConsole() && (request()->header('cf-ray') || str_contains(request()->getHost(), 'trycloudflare.com') || request()->header('x-forwarded-proto') === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');

            // External tunnel requests cannot access local Vite dev server (http://[::1]:5173).
            // Direct Laravel to use compiled assets in public/build instead.
            \Illuminate\Support\Facades\Vite::useHotFile(storage_path('framework/non_existent_hot'));
        }

        \Illuminate\Support\Facades\View::composer('*', function ($view) {
            $user = \Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::where('role', 'admin')->first();
            $isAdmin = $user ? $user->isAdmin() : true;

            $pendingReservationsCount = 0;
            $adminNotifications = collect();
            $unreadNotificationsCount = 0;

            try {
                if (Schema::hasTable('reservations')) {
                    $pendingReservationsCount = \App\Models\Reservation::where('status', 'Pending')->count();
                }
                if (Schema::hasTable('admin_notifications')) {
                    if ($isAdmin) {
                        $unreadNotificationsCount = \App\Models\AdminNotification::where('recipient_role', 'admin')->where('is_read', false)->count();
                        $adminNotifications = \App\Models\AdminNotification::where('recipient_role', 'admin')->with(['user', 'book'])->latest()->take(10)->get();
                    } elseif ($user) {
                        $unreadNotificationsCount = \App\Models\AdminNotification::where('user_id', $user->id)->where('recipient_role', 'user')->where('is_read', false)->count();
                        $adminNotifications = \App\Models\AdminNotification::where('user_id', $user->id)->where('recipient_role', 'user')->with(['user', 'book'])->latest()->take(10)->get();
                    }
                }
            } catch (\Throwable $e) {
                // Ignore DB errors during early boot
            }

            $view->with([
                'layoutUser' => $user,
                'layoutIsAdmin' => $isAdmin,
                'currentUser' => $user,
                'isAdmin' => $isAdmin,
                'pendingReservationsCount' => $pendingReservationsCount,
                'adminNotifications' => $adminNotifications,
                'unreadNotificationsCount' => $unreadNotificationsCount,
            ]);
        });
    }
}
