<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user ? $user->isAdmin() : false;

        if (! $isAdmin) {
            // Member Personal Dashboard
            $activeBorrows = $user->activeBorrows()->with('book.category')->get();
            $myBorrowsCount = $activeBorrows->count();
            $myOverdueCount = $activeBorrows->where('status', 'Overdue')->count();
            $myUnpaidFines = $user->unpaidFinesTotal();
            
            $myReservations = $user->reservations()
                ->whereIn('status', ['Pending', 'Approved'])
                ->with('book')
                ->latest()
                ->get();
                
            $recentHistory = $user->completedBorrows()
                ->with('book')
                ->latest('return_date')
                ->take(5)
                ->get();

            $quotaLimit = $user->maxAllowedLoans();
            $remainingQuota = max(0, $quotaLimit - $myBorrowsCount);

            $featuredBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                ->where('is_featured', true)
                ->take(8)
                ->get();
            if ($featuredBooks->count() < 4) {
                $featuredBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                    ->whereIn('id', [14, 15, 19, 21, 22, 1, 6])
                    ->take(8)
                    ->get();
            }

            $latestBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                ->whereIn('title', [
                    'សៀវភៅកម្រងស្នាដៃសិក្សាតាមឆ្នាំ២០២៤',
                    'សំណួរតេស្តគំរូនៃកម្មវិធីអន្តរជាតិ(PISA) រូបវិទ្យា ថ្នាក់ទី ៨ (សម្រាប់គ្រូ)',
                    'សំណួរតេស្តគំរូនៃកម្មវិធីអន្តរជាតិ(PISA) រូបវិទ្យា ថ្នាក់ទី ៨ (សម្រាប់សិស្ស)',
                    'សំណួរតេស្តគំរូនៃកម្មវិធីអន្តរជាតិ(PISA) ជីវវិទ្យា ថ្នាក់ទី ៧ (សម្រាប់សិស្ស)',
                    'រឿងល្ខោន ពិសោធន៍ស្នេហា',
                ])->get();
            if ($latestBooks->count() < 5) {
                $latestBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])->latest('id')->take(8)->get();
            }

            $trendingBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                ->orderByDesc('views_count')
                ->orderByDesc('downloads_count')
                ->take(8)
                ->get();

            $recommendedBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                ->whereIn('title', [
                    'Atomic Habits',
                    'Clean Code: A Handbook of Agile Software Craftsmanship',
                    'Designing Data-Intensive Applications',
                    'The Lean Startup',
                    'Zero to One: Notes on Startups',
                    'A History of Cambodia',
                ])->get();
            if ($recommendedBooks->count() < 4) {
                $recommendedBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])->inRandomOrder()->take(8)->get();
            }

            // 5. Smart Academic Year & Major Recommendations for Students (ការណែនាំសៀវភៅតាមជំនាញ និងឆ្នាំសិក្សាឆ្លាតវៃ)
            $academicYearBooks = collect();
            if ($user->isStudent()) {
                $targetYear = $user->academic_year ?: 1;
                $majorStr = trim((string) ($user->major ?? ''));

                // Extract primary major and faculty (e.g. "Software Engineering (វិទ្យាសាស្ត្រកុំព្យូទ័រ)")
                $majorKeywords = [];
                if (!empty($majorStr)) {
                    if (preg_match('/^([^(]+)(?:\(([^)]+)\))?/', $majorStr, $matches)) {
                        $pMajor = trim($matches[1] ?? '');
                        $fMajor = trim($matches[2] ?? '');
                        if ($pMajor) $majorKeywords[] = $pMajor;
                        if ($fMajor) $majorKeywords[] = $fMajor;
                    }
                    $majorKeywords[] = $majorStr;
                }
                $majorKeywords = array_values(array_unique(array_filter($majorKeywords)));

                // 1. Priority: Exact Academic Year + Matching Major / Subject
                if (!empty($majorKeywords)) {
                    $academicYearBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                        ->where('target_academic_year', $targetYear)
                        ->where(function ($q) use ($majorKeywords) {
                            foreach ($majorKeywords as $kw) {
                                $q->orWhere('recommended_major', 'like', "%{$kw}%")
                                  ->orWhere('subject', 'like', "%{$kw}%");
                            }
                        })
                        ->orderByDesc('views_count')
                        ->take(8)
                        ->get();
                } else {
                    $academicYearBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                        ->where('target_academic_year', $targetYear)
                        ->orderByDesc('views_count')
                        ->take(8)
                        ->get();
                }

                // 2. Secondary: Other books in the same Major/Discipline for other years or general level
                if ($academicYearBooks->count() < 5 && !empty($majorKeywords)) {
                    $extraMajorBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])
                        ->whereNotIn('id', $academicYearBooks->pluck('id'))
                        ->where(function ($q) use ($majorKeywords) {
                            foreach ($majorKeywords as $kw) {
                                $q->orWhere('recommended_major', 'like', "%{$kw}%")
                                  ->orWhere('subject', 'like', "%{$kw}%");
                            }
                        })
                        ->orderByDesc('views_count')
                        ->take(8 - $academicYearBooks->count())
                        ->get();
                    $academicYearBooks = $academicYearBooks->merge($extraMajorBooks);
                }

                // 3. Fallback: University-level books for that academic year or research methodology
                if ($academicYearBooks->count() < 5) {
                    $supplement = Book::with(['category', 'activeBorrows', 'activeReservations'])
                        ->whereNotIn('id', $academicYearBooks->pluck('id'))
                        ->where(function ($q) use ($targetYear) {
                            $q->where('target_academic_year', $targetYear)
                              ->orWhere('subject', 'like', '%ស្រាវជ្រាវ%')
                              ->orWhere('education_level', 'បរិញ្ញាបត្រ');
                        })
                        ->orderByDesc('views_count')
                        ->take(8 - $academicYearBooks->count())
                        ->get();
                    $academicYearBooks = $academicYearBooks->merge($supplement);
                }
            }

            // 6. External Member Subscription Status (ស្ថានភាពសមាជិកភាពក្រៅសាកលវិទ្យាល័យ)
            $hasActiveMembership = $user->hasActiveMembership();
            $membershipDaysRemaining = $user->membershipDaysRemaining();
            $latestMembershipPayment = $user->isExternalMember()
                ? $user->membershipPayments()->latest('id')->first()
                : null;

            $allLibraryBooks = Book::with(['category', 'activeBorrows', 'activeReservations'])->orderBy('id', 'desc')->get();

            return view('dashboard_member', compact(
                'user',
                'activeBorrows',
                'myBorrowsCount',
                'myOverdueCount',
                'myUnpaidFines',
                'myReservations',
                'recentHistory',
                'quotaLimit',
                'remainingQuota',
                'allLibraryBooks',
                'featuredBooks',
                'latestBooks',
                'trendingBooks',
                'recommendedBooks',
                'academicYearBooks',
                'hasActiveMembership',
                'membershipDaysRemaining',
                'latestMembershipPayment'
            ));
        }

        // Auto-cancel expired pickup deadlines (24-48h hold window expired)
        $expiredHolds = Reservation::where('status', 'Approved')
            ->whereNotNull('pickup_deadline')
            ->where('pickup_deadline', '<', Carbon::now())
            ->with('book')
            ->get();

        foreach ($expiredHolds as $expired) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($expired) {
                $expired->update([
                    'status' => 'Cancelled',
                    'notes' => ($expired->notes ? $expired->notes . ' | ' : '') . 'Auto-cancelled: pickup deadline expired.',
                ]);
                if ($expired->book) {
                    $expired->book->increment('available_copies');
                }
            });
        }

        // Trigger automated daily backup check on Drive D if enabled & not yet run today
        try {
            app(\App\Services\BackupService::class)->checkAndRunMissedAutoBackup();
        } catch (\Exception $e) {
            // Silently continue to prevent admin dashboard disruption
        }

        // Admin Dashboard
        $totalBooks = Book::sum('total_copies') ?: Book::count();
        $totalMembers = User::where('role', 'member')->count() ?: User::count();
        $borrowedCount = Borrow::where('status', 'Borrowed')->count();
        $overdueCount = Borrow::where('status', 'Overdue')->count();
        
        $pendingHolds = Reservation::where('status', 'Pending')->with(['user', 'book'])->latest()->get();
        $pendingReservationsCount = $pendingHolds->count();
        $approvedHoldsCount = Reservation::where('status', 'Approved')->count();

        // Activity Logs (Recent staff/manager operations)
        // Activity Logs (Recent staff/manager operations)
        $recentActivityLogs = \App\Models\ActivityLog::with('user')->latest()->take(10)->get();

        // Circulation Growth Analytics (Daily, Weekly, Monthly, Yearly)
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $todayBorrows = Borrow::whereDate('borrow_date', $today)->count();
        $yesterdayBorrows = Borrow::whereDate('borrow_date', $yesterday)->count();
        $dailyGrowth = $yesterdayBorrows > 0 ? round((($todayBorrows - $yesterdayBorrows) / $yesterdayBorrows) * 100, 1) : ($todayBorrows > 0 ? 100.0 : 0.0);

        $now = Carbon::now();
        $startThisWeek = $now->copy()->startOfWeek();
        $endThisWeek = $now->copy()->endOfWeek();
        $startLastWeek = $now->copy()->subWeek()->startOfWeek();
        $endLastWeek = $now->copy()->subWeek()->endOfWeek();
        $thisWeekBorrows = Borrow::whereBetween('borrow_date', [$startThisWeek, $endThisWeek])->count();
        $lastWeekBorrows = Borrow::whereBetween('borrow_date', [$startLastWeek, $endLastWeek])->count();
        $weeklyGrowth = $lastWeekBorrows > 0 ? round((($thisWeekBorrows - $lastWeekBorrows) / $lastWeekBorrows) * 100, 1) : ($thisWeekBorrows > 0 ? 100.0 : 0.0);

        $startThisMonth = $now->copy()->startOfMonth();
        $startLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth = $now->copy()->subMonth()->endOfMonth();
        $thisMonthBorrows = Borrow::whereBetween('borrow_date', [$startThisMonth, $now])->count();
        $lastMonthBorrows = Borrow::whereBetween('borrow_date', [$startLastMonth, $endLastMonth])->count();
        $monthlyGrowth = $lastMonthBorrows > 0 ? round((($thisMonthBorrows - $lastMonthBorrows) / $lastMonthBorrows) * 100, 1) : ($thisMonthBorrows > 0 ? 100.0 : 0.0);

        $thisYearBorrows = Borrow::whereYear('borrow_date', $now->year)->count();
        $lastYearBorrows = Borrow::whereYear('borrow_date', $now->year - 1)->count();
        $yearlyGrowth = $lastYearBorrows > 0 ? round((($thisYearBorrows - $lastYearBorrows) / $lastYearBorrows) * 100, 1) : ($thisYearBorrows > 0 ? 100.0 : 0.0);

        // Recent Borrows feed
        $recentBorrows = Borrow::with(['user', 'book'])->latest('borrow_date')->take(6)->get();

        // Khmer month names for natural Cambodian formatting
        $khmerMonths = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា',
            5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា',
            9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ',
        ];
        $isKhmer = app()->getLocale() === 'km';

        // 1. Daily Circulation Trends (Last 14 days)
        $dailyCategories = [];
        $dailyBorrows = [];
        $dailyReturns = [];
        for ($i = 13; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i);
            $monthName = $isKhmer ? ($khmerMonths[$d->month] ?? $d->format('M')) : $d->format('M');
            $dailyCategories[] = $d->format('d') . ' ' . $monthName;
            $dailyBorrows[] = Borrow::whereDate('borrow_date', $d->toDateString())->count();
            $dailyReturns[] = Borrow::whereNotNull('return_date')->whereDate('return_date', $d->toDateString())->count();
        }

        // 2. Monthly Circulation Trends (Last 6 months)
        $monthlyCategories = [];
        $monthlyBorrows = [];
        $monthlyReturns = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = Carbon::now()->subMonths($i);
            $monthlyCategories[] = $isKhmer ? ($khmerMonths[$m->month] ?? $m->format('M')) : $m->format('M');
            $monthlyBorrows[] = Borrow::whereYear('borrow_date', $m->year)->whereMonth('borrow_date', $m->month)->count();
            $monthlyReturns[] = Borrow::whereNotNull('return_date')->whereYear('return_date', $m->year)->whereMonth('return_date', $m->month)->count();
        }

        // 3. Yearly Circulation Trends (Past 5 years)
        $yearlyCategories = [];
        $yearlyBorrows = [];
        $yearlyReturns = [];
        $currYear = Carbon::now()->year;
        for ($i = 4; $i >= 0; $i--) {
            $yr = $currYear - $i;
            $yearlyCategories[] = (string) $yr;
            $yearlyBorrows[] = Borrow::whereYear('borrow_date', $yr)->count();
            $yearlyReturns[] = Borrow::whereNotNull('return_date')->whereYear('return_date', $yr)->count();
        }

        $months = $monthlyCategories;
        $borrowCounts = $monthlyBorrows;
        $returnCounts = $monthlyReturns;

        // Category Distribution for ApexCharts Donut
        $categories = Category::withCount('books')->get();
        $categoryLabels = $categories->pluck('name')->toArray();
        $categoryCounts = $categories->pluck('books_count')->toArray();
        if (empty($categoryLabels)) {
            $categoryLabels = ['General'];
            $categoryCounts = [$totalBooks];
        }

        return view('dashboard', compact(
            'totalBooks',
            'totalMembers',
            'borrowedCount',
            'overdueCount',
            'pendingReservationsCount',
            'pendingHolds',
            'approvedHoldsCount',
            'recentBorrows',
            'recentActivityLogs',
            'todayBorrows',
            'yesterdayBorrows',
            'dailyGrowth',
            'thisWeekBorrows',
            'lastWeekBorrows',
            'weeklyGrowth',
            'thisMonthBorrows',
            'lastMonthBorrows',
            'monthlyGrowth',
            'thisYearBorrows',
            'lastYearBorrows',
            'yearlyGrowth',
            'dailyCategories',
            'dailyBorrows',
            'dailyReturns',
            'monthlyCategories',
            'monthlyBorrows',
            'monthlyReturns',
            'yearlyCategories',
            'yearlyBorrows',
            'yearlyReturns',
            'months',
            'borrowCounts',
            'returnCounts',
            'categoryLabels',
            'categoryCounts'
        ));
    }
}
