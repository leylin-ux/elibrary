<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the main Library Reports & Analytics dashboard.
     */
    public function index(Request $request)
    {
        // 1. Synchronize overdue statuses for real-time accuracy
        Borrow::where('status', 'Borrowed')
            ->where('due_date', '<', Carbon::today())
            ->update(['status' => 'Overdue']);

        // 2. High-level Statistics / Summary Metrics
        $totalBorrowsCount = Borrow::count();
        $activeBorrowsCount = Borrow::whereIn('status', ['Borrowed', 'Overdue'])->count();
        $overdueBorrowsCount = Borrow::where('status', 'Overdue')->count();
        $returnedBorrowsCount = Borrow::where('status', 'Returned')->count();

        $totalBooksCount = Book::sum('total_copies') ?: Book::count();
        $totalMembersCount = User::where('role', '!=', 'admin')->count() ?: User::count();

        // 3. Feature 1: Top Borrowed Books (សៀវភៅដែលមានអ្នកខ្ចីច្រើនជាងគេ)
        $topBooks = Book::with('category')
            ->withCount('borrows')
            ->orderByDesc('borrows_count')
            ->take(10)
            ->get();

        $maxBookBorrows = $topBooks->max('borrows_count') ?: 1;

        // 4. Feature 2: Most Active Members (សមាជិកដែលសកម្មបំផុត)
        $topMembers = User::where('role', '!=', 'admin')
            ->withCount([
                'borrows as total_borrows_count',
                'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
                'borrows as completed_borrows_count' => fn($q) => $q->where('status', 'Returned'),
                'borrows as overdue_borrows_count' => fn($q) => $q->where('status', 'Overdue'),
            ])
            ->orderByDesc('total_borrows_count')
            ->take(10)
            ->get();

        // If no members have loans yet, fallback to all users ordered by borrows
        if ($topMembers->isEmpty()) {
            $topMembers = User::withCount([
                'borrows as total_borrows_count',
                'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
                'borrows as completed_borrows_count' => fn($q) => $q->where('status', 'Returned'),
                'borrows as overdue_borrows_count' => fn($q) => $q->where('status', 'Overdue'),
            ])
            ->orderByDesc('total_borrows_count')
            ->take(10)
            ->get();
        }

        // 5. Feature 3: Monthly Borrowing Statistics (ស្ថិតិការខ្ចីប្រចាំខែ)
        // 5. Feature 3: Borrowing Statistics (Day, Month, Year - ស្ថិតិការខ្ចីប្រចាំថ្ងៃ ខែ ឆ្នាំ)
        $circulationPeriod = $request->input('circulation_period', 'monthly'); // 'daily', 'monthly', 'yearly'
        $selectedYear = (int) $request->input('year', Carbon::now()->year);
        $selectedMonth = (int) $request->input('month', Carbon::now()->month);

        $availableYears = Borrow::whereNotNull('borrow_date')
            ->pluck('borrow_date')
            ->map(fn($d) => Carbon::parse($d)->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();

        if (!in_array(Carbon::now()->year, $availableYears)) {
            array_unshift($availableYears, Carbon::now()->year);
        }

        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $khmerMonthNames = [
            1 => 'មករា', 2 => 'កុម្ភៈ', 3 => 'មីនា', 4 => 'មេសា',
            5 => 'ឧសភា', 6 => 'មិថុនា', 7 => 'កក្កដា', 8 => 'សីហា',
            9 => 'កញ្ញា', 10 => 'តុលា', 11 => 'វិច្ឆិកា', 12 => 'ធ្នូ',
        ];
        $isKhmer = app()->getLocale() === 'km';

        $breakdownData = [];
        $breakdownLabels = [];
        $breakdownBorrows = [];
        $breakdownReturns = [];
        $breakdownOverdue = [];

        $totalPeriodBorrows = 0;
        $totalPeriodReturns = 0;
        $totalPeriodOverdue = 0;

        if ($circulationPeriod === 'daily') {
            // Daily breakdown for the selected month and year
            $daysInMonth = Carbon::createFromDate($selectedYear, $selectedMonth, 1)->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateStr = sprintf('%04d-%02d-%02d', $selectedYear, $selectedMonth, $d);
                $bCount = Borrow::whereDate('borrow_date', $dateStr)->count();
                $rCount = Borrow::whereNotNull('return_date')->whereDate('return_date', $dateStr)->count();
                $oCount = Borrow::whereDate('due_date', $dateStr)->where('status', 'Overdue')->count();

                $dayLabel = sprintf('%02d', $d);
                $fullDayLabel = $isKhmer 
                    ? ($dayLabel . ' ' . ($khmerMonthNames[$selectedMonth] ?? '')) 
                    : ($dayLabel . ' ' . ($monthLabels[$selectedMonth - 1] ?? ''));

                $breakdownLabels[] = $dayLabel;
                $breakdownBorrows[] = $bCount;
                $breakdownReturns[] = $rCount;
                $breakdownOverdue[] = $oCount;

                $totalPeriodBorrows += $bCount;
                $totalPeriodReturns += $rCount;
                $totalPeriodOverdue += $oCount;

                $breakdownData[] = [
                    'label' => $fullDayLabel,
                    'short_label' => $dayLabel,
                    'borrows' => $bCount,
                    'returns' => $rCount,
                    'overdue' => $oCount,
                    'return_rate' => $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0,
                ];
            }
            $avgBorrows = round($totalPeriodBorrows / $daysInMonth, 1);
        } elseif ($circulationPeriod === 'yearly') {
            // Multi-year breakdown across all active years
            $sortedYears = array_reverse($availableYears);
            foreach ($sortedYears as $yr) {
                $bCount = Borrow::whereYear('borrow_date', $yr)->count();
                $rCount = Borrow::whereNotNull('return_date')->whereYear('return_date', $yr)->count();
                $oCount = Borrow::whereYear('due_date', $yr)->where('status', 'Overdue')->count();

                $breakdownLabels[] = (string) $yr;
                $breakdownBorrows[] = $bCount;
                $breakdownReturns[] = $rCount;
                $breakdownOverdue[] = $oCount;

                $totalPeriodBorrows += $bCount;
                $totalPeriodReturns += $rCount;
                $totalPeriodOverdue += $oCount;

                $breakdownData[] = [
                    'label' => (string) $yr,
                    'short_label' => (string) $yr,
                    'borrows' => $bCount,
                    'returns' => $rCount,
                    'overdue' => $oCount,
                    'return_rate' => $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0,
                ];
            }
            $avgBorrows = count($sortedYears) > 0 ? round($totalPeriodBorrows / count($sortedYears), 1) : 0;
        } else {
            // Monthly breakdown for selected year (Default)
            $circulationPeriod = 'monthly';
            for ($m = 1; $m <= 12; $m++) {
                $bCount = Borrow::whereYear('borrow_date', $selectedYear)->whereMonth('borrow_date', $m)->count();
                $rCount = Borrow::whereNotNull('return_date')->whereYear('return_date', $selectedYear)->whereMonth('return_date', $m)->count();
                $oCount = Borrow::whereYear('due_date', $selectedYear)->whereMonth('due_date', $m)->where('status', 'Overdue')->count();

                $mLabel = $isKhmer ? ($khmerMonthNames[$m] ?? $monthLabels[$m - 1]) : $monthLabels[$m - 1];

                $breakdownLabels[] = $mLabel;
                $breakdownBorrows[] = $bCount;
                $breakdownReturns[] = $rCount;
                $breakdownOverdue[] = $oCount;

                $totalPeriodBorrows += $bCount;
                $totalPeriodReturns += $rCount;
                $totalPeriodOverdue += $oCount;

                $breakdownData[] = [
                    'label' => $mLabel,
                    'short_label' => $mLabel,
                    'month_num' => $m,
                    'borrows' => $bCount,
                    'returns' => $rCount,
                    'overdue' => $oCount,
                    'return_rate' => $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0,
                ];
            }
            $avgBorrows = round($totalPeriodBorrows / 12, 1);
        }

        // Calculate KPI summaries for current period
        $peakLabel = null;
        $maxBorrows = 0;
        foreach ($breakdownData as $row) {
            if ($row['borrows'] > $maxBorrows) {
                $maxBorrows = $row['borrows'];
                $peakLabel = $row['label'];
            }
        }
        $overallReturnRate = $totalPeriodBorrows > 0 ? round(($totalPeriodReturns / $totalPeriodBorrows) * 100, 1) : 0;

        // Legacy compatibility aliases
        $monthlyData = $breakdownData;
        $monthLabels = $breakdownLabels;
        $monthlyBorrows = $breakdownBorrows;
        $monthlyReturns = $breakdownReturns;
        $monthlyOverdue = $breakdownOverdue;
        $totalYearBorrows = $totalPeriodBorrows;
        $totalYearReturns = $totalPeriodReturns;
        $totalYearOverdue = $totalPeriodOverdue;
        $peakMonth = $peakLabel;
        $maxMonthBorrows = $maxBorrows;
        $avgMonthlyBorrows = $avgBorrows;

        // 5.1 Feature 3.1: Monthly Fines & Waived Fees Report (របាយការណ៍ប្រាក់ពិន័យប្រចាំខែ)
        $standardMonthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthlyFinesData = [];
        $totalCollectedFinesYear = 0;
        $totalWaivedFinesYear = 0;
        $totalPendingFinesYear = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthBorrows = Borrow::whereYear('borrow_date', $selectedYear)->whereMonth('borrow_date', $m)->get();
            $collected = (float) $monthBorrows->where('fine_paid', true)->where('fine_waived', false)->sum('fine_amount');
            $waivedCount = Borrow::where('fine_waived', true)->whereYear('updated_at', $selectedYear)->whereMonth('updated_at', $m)->count();
            $waivedAmount = $waivedCount * 2.00; // estimated $2 per waived case
            $pending = (float) $monthBorrows->where('fine_paid', false)->where('fine_waived', false)->sum('fine_amount');

            $monthlyFinesData[] = [
                'month' => $standardMonthLabels[$m - 1],
                'month_num' => $m,
                'collected' => $collected,
                'collected_khr' => $collected * 4000,
                'waived' => $waivedAmount,
                'waived_khr' => $waivedAmount * 4000,
                'waived_count' => $waivedCount,
                'pending' => $pending,
                'pending_khr' => $pending * 4000,
            ];

            $totalCollectedFinesYear += $collected;
            $totalWaivedFinesYear += $waivedAmount;
            $totalPendingFinesYear += $pending;
        }

        // 6. Feature 4: Overdue & Lost Books Report (របាយការណ៍សៀវភៅហួសកំណត់ និងសៀវភៅបាត់បង់)
        $overdueBorrows = Borrow::with(['user', 'book.category', 'waivedBy'])
            ->where(function ($q) {
                $q->where('status', 'Overdue')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'Borrowed')
                         ->where('due_date', '<', Carbon::today());
                  });
            })
            ->orderBy('due_date', 'asc')
            ->get();

        $totalEstimatedFines = 0;
        foreach ($overdueBorrows as $item) {
            $daysLate = Carbon::today()->diffInDays(Carbon::parse($item->due_date));
            $item->days_overdue = $daysLate;
            $item->calculated_fine = max((float)$item->fine_amount, $daysLate * 0.50);
            $totalEstimatedFines += $item->calculated_fine;
        }

        // Lost or Zero Available Books
        $lostBooks = Book::with('category')
            ->where('available_copies', '<=', 0)
            ->withCount(['borrows as active_loans_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue'])])
            ->get();

        // 7. Feature 5: Member History Lookup by Full Name (ប្រវត្តសមាជិក)
        $searchMemberName = trim($request->input('search_member', ''));
        $memberId = $request->input('member_id');
        $selectedMember = null;
        $matchedMembers = collect();

        if ($memberId) {
            $selectedMember = User::with([
                'borrows' => fn($q) => $q->with(['book.category'])->orderByDesc('borrow_date'),
            ])->find($memberId);
        } elseif (!empty($searchMemberName)) {
            $matches = User::where('name', 'like', "%{$searchMemberName}%")
                ->orWhere('card_id', 'like', "%{$searchMemberName}%")
                ->orWhere('email', 'like', "%{$searchMemberName}%")
                ->withCount([
                    'borrows as total_borrows_count',
                    'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
                    'borrows as overdue_borrows_count' => fn($q) => $q->where('status', 'Overdue'),
                ])
                ->get();

            if ($matches->count() === 1) {
                $selectedMember = $matches->first();
                $selectedMember->load([
                    'borrows' => fn($q) => $q->with(['book.category'])->orderByDesc('borrow_date'),
                ]);
            } else {
                $matchedMembers = $matches;
            }
        }

        // Quick patron chips for easy demonstration
        $quickMembers = User::where('role', 'member')
            ->whereHas('borrows')
            ->take(6)
            ->get(['id', 'name', 'card_id', 'photo']);

        if ($quickMembers->isEmpty()) {
            $quickMembers = User::whereHas('borrows')->take(6)->get(['id', 'name', 'card_id', 'photo']);
        }

        // Membership Fee & Subscription Revenue Analytics (របាយការណ៍ចំណូលថ្លៃសមាជិកភាព)
        $totalMembershipRevenue = (float) \App\Models\MembershipPayment::where('status', 'Paid')->sum('amount');
        $totalActivePaidSubscribers = User::where('member_type', 'General')->where('membership_expires_at', '>=', Carbon::now())->count();
        $totalExpiredSubscribers = User::where('member_type', 'General')->where(function ($q) {
            $q->whereNull('membership_expires_at')->orWhere('membership_expires_at', '<', Carbon::now());
        })->count();
        $recentMembershipPayments = \App\Models\MembershipPayment::with(['user', 'recordedBy'])->latest('id')->take(15)->get();

        // Default active tab
        $activeTab = $request->input('tab', 'overview');
        if (!empty($searchMemberName) || $memberId) {
            $activeTab = 'member_history';
        }

        return view('reports.index', compact(
            'activeTab',
            'totalBorrowsCount',
            'activeBorrowsCount',
            'overdueBorrowsCount',
            'returnedBorrowsCount',
            'totalBooksCount',
            'totalMembersCount',
            'totalMembershipRevenue',
            'totalActivePaidSubscribers',
            'totalExpiredSubscribers',
            'recentMembershipPayments',
            'topBooks',
            'maxBookBorrows',
            'topMembers',
            'circulationPeriod',
            'selectedMonth',
            'breakdownData',
            'breakdownLabels',
            'breakdownBorrows',
            'breakdownReturns',
            'breakdownOverdue',
            'totalPeriodBorrows',
            'totalPeriodReturns',
            'totalPeriodOverdue',
            'peakLabel',
            'maxBorrows',
            'avgBorrows',
            'selectedYear',
            'availableYears',
            'monthLabels',
            'monthlyBorrows',
            'monthlyReturns',
            'monthlyOverdue',
            'monthlyData',
            'totalYearBorrows',
            'totalYearReturns',
            'totalYearOverdue',
            'peakMonth',
            'maxMonthBorrows',
            'avgMonthlyBorrows',
            'overallReturnRate',
            'monthlyFinesData',
            'totalCollectedFinesYear',
            'totalWaivedFinesYear',
            'totalPendingFinesYear',
            'overdueBorrows',
            'lostBooks',
            'totalEstimatedFines',
            'searchMemberName',
            'selectedMember',
            'matchedMembers',
            'quickMembers'
        ));
    }

    /**
     * Export reports to CSV (with UTF-8 BOM for Khmer & Excel support).
     */
    public function exportCsv(Request $request, ?string $type = null)
    {
        $type = $type ?: $request->input('type', 'monthly_stats');
        $filename = "elibrary_report_{$type}_" . date('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($type, $request) {
            $handle = fopen('php://output', 'w');
            
            // Output UTF-8 BOM so Excel opens Khmer and unicode characters without garbling
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($type === 'overdue' || $type === 'overdue_lost') {
                fputcsv($handle, ['#', 'Patron Name', 'Card ID', 'Phone', 'Book Title', 'ISBN', 'Borrow Date', 'Due Date', 'Days Overdue', 'Estimated Fine ($)', 'Fine Waived?']);
                
                $overdues = Borrow::with(['user', 'book'])
                    ->where('status', 'Overdue')
                    ->orWhere(fn($q) => $q->whereNull('return_date')->where('due_date', '<', Carbon::today()))
                    ->orderBy('due_date', 'asc')
                    ->get();

                foreach ($overdues as $idx => $row) {
                    $daysLate = Carbon::today()->diffInDays(Carbon::parse($row->due_date));
                    $fine = max((float)$row->fine_amount, $daysLate * 0.50);
                    fputcsv($handle, [
                        $idx + 1,
                        $row->user->name ?? 'N/A',
                        $row->user->card_id ?? 'N/A',
                        $row->user->phone ?? 'N/A',
                        $row->book->title ?? 'N/A',
                        $row->book->isbn ?? 'N/A',
                        $row->borrow_date ? $row->borrow_date->format('Y-m-d') : 'N/A',
                        $row->due_date ? $row->due_date->format('Y-m-d') : 'N/A',
                        $daysLate,
                        number_format($fine, 2),
                        $row->fine_waived ? 'Yes (Waived)' : 'No',
                    ]);
                }
            } elseif ($type === 'monthly_fines') {
                $year = (int) $request->input('year', Carbon::now()->year);
                fputcsv($handle, ["Monthly Fines & Waived Fees Report ({$year})"]);
                fputcsv($handle, ['Month', 'Collected Fines ($)', 'Collected Fines (KHR)', 'Waived Fines ($)', 'Waived Fines (KHR)', 'Pending Fines ($)']);

                $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                for ($m = 1; $m <= 12; $m++) {
                    $monthBorrows = Borrow::whereYear('borrow_date', $year)->whereMonth('borrow_date', $m)->get();
                    $collected = (float) $monthBorrows->where('fine_paid', true)->where('fine_waived', false)->sum('fine_amount');
                    $waivedCount = Borrow::where('fine_waived', true)->whereYear('updated_at', $year)->whereMonth('updated_at', $m)->count();
                    $waivedAmount = $waivedCount * 2.00;
                    $pending = (float) $monthBorrows->where('fine_paid', false)->where('fine_waived', false)->sum('fine_amount');

                    fputcsv($handle, [
                        $monthNames[$m - 1],
                        number_format($collected, 2),
                        number_format($collected * 4000) . ' KHR',
                        number_format($waivedAmount, 2),
                        number_format($waivedAmount * 4000) . ' KHR',
                        number_format($pending, 2),
                    ]);
                }
            } elseif ($type === 'top_books') {
                fputcsv($handle, ['Rank', 'Book Title', 'Author', 'Category', 'ISBN', 'Times Borrowed', 'Available Copies', 'Total Copies']);
                
                $topBooks = Book::with('category')
                    ->withCount('borrows')
                    ->orderByDesc('borrows_count')
                    ->get();

                foreach ($topBooks as $idx => $book) {
                    fputcsv($handle, [
                        $idx + 1,
                        $book->title,
                        $book->author,
                        $book->category->name ?? 'General',
                        $book->isbn,
                        $book->borrows_count,
                        $book->available_copies,
                        $book->total_copies,
                    ]);
                }
            } elseif ($type === 'top_members') {
                fputcsv($handle, ['Rank', 'Patron Name', 'Card ID', 'Member Type', 'Status', 'Total Loans', 'Active Loans', 'Returned Loans']);
                
                $topMembers = User::where('role', 'member')
                    ->withCount([
                        'borrows as total_borrows_count',
                        'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
                        'borrows as completed_borrows_count' => fn($q) => $q->where('status', 'Returned'),
                    ])
                    ->orderByDesc('total_borrows_count')
                    ->get();

                foreach ($topMembers as $idx => $member) {
                    fputcsv($handle, [
                        $idx + 1,
                        $member->name,
                        $member->card_id,
                        $member->member_type,
                        $member->status,
                        $member->total_borrows_count,
                        $member->active_borrows_count,
                        $member->completed_borrows_count,
                    ]);
                }
            } elseif ($type === 'monthly_stats' || $type === 'circulation_stats') {
                $period = $request->input('circulation_period', 'monthly');
                $year = (int) $request->input('year', Carbon::now()->year);
                $month = (int) $request->input('month', Carbon::now()->month);

                if ($period === 'daily') {
                    $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
                    fputcsv($handle, ["Daily Circulation Statistics ({$year}-{$month})"]);
                    fputcsv($handle, ['Day', 'Date', 'Total Borrows', 'Total Returns', 'Overdue Loans', 'Return Rate (%)']);
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $bCount = Borrow::whereDate('borrow_date', $dateStr)->count();
                        $rCount = Borrow::whereNotNull('return_date')->whereDate('return_date', $dateStr)->count();
                        $oCount = Borrow::whereDate('due_date', $dateStr)->where('status', 'Overdue')->count();
                        $rate = $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0;
                        fputcsv($handle, [sprintf('Day %02d', $d), $dateStr, $bCount, $rCount, $oCount, $rate . '%']);
                    }
                } elseif ($period === 'yearly') {
                    fputcsv($handle, ["Yearly Circulation Statistics (Multi-Year)"]);
                    fputcsv($handle, ['Year', 'Total Borrows', 'Total Returns', 'Overdue Loans', 'Return Rate (%)']);
                    $allYears = Borrow::whereNotNull('borrow_date')
                        ->pluck('borrow_date')
                        ->map(fn($d) => Carbon::parse($d)->year)
                        ->unique()
                        ->sort()
                        ->values()
                        ->toArray();
                    if (!in_array(Carbon::now()->year, $allYears)) {
                        $allYears[] = Carbon::now()->year;
                    }
                    foreach ($allYears as $yr) {
                        $bCount = Borrow::whereYear('borrow_date', $yr)->count();
                        $rCount = Borrow::whereNotNull('return_date')->whereYear('return_date', $yr)->count();
                        $oCount = Borrow::whereYear('due_date', $yr)->where('status', 'Overdue')->count();
                        $rate = $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0;
                        fputcsv($handle, [$yr, $bCount, $rCount, $oCount, $rate . '%']);
                    }
                } else {
                    fputcsv($handle, ["Monthly Borrowing Statistics ({$year})"]);
                    fputcsv($handle, ['Month', 'Total Borrows', 'Total Returns', 'Overdue Loans', 'Return Rate (%)']);

                    $monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    for ($m = 1; $m <= 12; $m++) {
                        $bCount = Borrow::whereYear('borrow_date', $year)->whereMonth('borrow_date', $m)->count();
                        $rCount = Borrow::whereNotNull('return_date')->whereYear('return_date', $year)->whereMonth('return_date', $m)->count();
                        $oCount = Borrow::whereYear('due_date', $year)->whereMonth('due_date', $m)->where('status', 'Overdue')->count();
                        $rate = $bCount > 0 ? round(($rCount / $bCount) * 100, 1) : 0;
                        fputcsv($handle, [$monthNames[$m - 1], $bCount, $rCount, $oCount, $rate . '%']);
                    }
                }
            } elseif ($type === 'member_history') {
                $memberId = $request->input('member_id');
                $member = User::find($memberId);

                fputcsv($handle, ['Patron Report: ' . ($member ? $member->name . ' (' . $member->card_id . ')' : 'All Members')]);
                fputcsv($handle, ['#', 'Book Title', 'Author', 'Category', 'Borrow Date', 'Due Date', 'Return Date', 'Status', 'Fine Amount ($)', 'Notes']);

                $loansQuery = Borrow::with(['book.category', 'user'])->latest('borrow_date');
                if ($memberId) {
                    $loansQuery->where('user_id', $memberId);
                }

                $loans = $loansQuery->get();
                foreach ($loans as $idx => $loan) {
                    fputcsv($handle, [
                        $idx + 1,
                        $loan->book->title ?? 'N/A',
                        $loan->book->author ?? 'N/A',
                        $loan->book->category->name ?? 'N/A',
                        $loan->borrow_date ? $loan->borrow_date->format('Y-m-d') : 'N/A',
                        $loan->due_date ? $loan->due_date->format('Y-m-d') : 'N/A',
                        $loan->return_date ? $loan->return_date->format('Y-m-d') : 'Not Returned',
                        $loan->status,
                        number_format((float)$loan->fine_amount, 2),
                        $loan->notes ?? '',
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }
}
