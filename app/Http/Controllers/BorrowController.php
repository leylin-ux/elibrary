<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BorrowController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        $isAdmin = $user ? $user->isAdmin() : true;

        $query = Borrow::with(['user', 'book']);

        // Data Privacy: If logged in as Member, restrict to only their own loans
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('book_id')) {
            $query->where('book_id', $request->book_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereHas('user', function ($uq) use ($s) {
                    $uq->where('name', 'like', "%{$s}%")
                       ->orWhere('card_id', 'like', "%{$s}%");
                })->orWhereHas('book', function ($bq) use ($s) {
                    $bq->where('title', 'like', "%{$s}%")
                       ->orWhere('isbn', 'like', "%{$s}%");
                });
            });
        }

        $borrows = $query->latest('id')->paginate(15)->withQueryString();
        
        $users = $isAdmin ? User::where('status', 'Active')->orderBy('name')->get() : collect([$user]);
        $books = Book::where('available_copies', '>', 0)->orderBy('title')->get();

        // Circulation Stats
        $stats = [
            'total' => $isAdmin ? Borrow::count() : Borrow::where('user_id', $user->id)->count(),
            'borrowed' => $isAdmin ? Borrow::where('status', 'Borrowed')->count() : Borrow::where('user_id', $user->id)->where('status', 'Borrowed')->count(),
            'overdue' => $isAdmin ? Borrow::where('status', 'Overdue')->count() : Borrow::where('user_id', $user->id)->where('status', 'Overdue')->count(),
            'returned' => $isAdmin ? Borrow::where('status', 'Returned')->count() : Borrow::where('user_id', $user->id)->where('status', 'Returned')->count(),
        ];

        return view('borrows.index', compact('borrows', 'users', 'books', 'isAdmin', 'stats'));
    }

    /**
     * Pre-flight Patron Verification (Borrowing Flow Step 2)
     */
    public function checkPatron(Request $request)
    {
        $query = trim($request->query('query', ''));
        if (empty($query)) {
            return response()->json(['success' => false, 'message' => __('Please enter a Card ID or Member Name.')], 400);
        }

        // Search by Card ID, ID, or reservation code
        $reservation = null;
        if (str_starts_with(strtoupper($query), 'RES-')) {
            $reservation = Reservation::where('reservation_code', strtoupper($query))
                ->whereIn('status', ['Pending', 'Approved'])
                ->with(['user', 'book'])
                ->first();
            $patron = $reservation?->user;
        } else {
            $patron = User::where('card_id', $query)
                ->orWhere('id', $query)
                ->orWhere('name', 'like', "%{$query}%")
                ->with(['activeBorrows.book'])
                ->first();
        }

        if (! $patron) {
            return response()->json([
                'success' => false,
                'message' => __('No patron found matching ":query".', ['query' => $query]),
            ], 404);
        }

        $activeBorrows = $patron->activeBorrows()->with('book')->get();
        $hasOverdue = $activeBorrows->where('status', 'Overdue')->isNotEmpty();
        $unpaidFines = $patron->unpaidFinesTotal();
        $maxLimit = $patron->maxAllowedLoans();
        $activeCount = $activeBorrows->count();
        $quotaReached = $activeCount >= $maxLimit;

        $checkResult = $patron->canBorrowNewBook();

        return response()->json([
            'success' => true,
            'patron' => [
                'id' => $patron->id,
                'name' => $patron->name,
                'card_id' => $patron->card_id,
                'member_type' => $patron->member_type,
                'academic_year' => $patron->academic_year,
                'academic_year_label' => $patron->academic_year_label,
                'major' => $patron->major,
                'status' => $patron->status,
                'photo' => $patron->photo,
                'active_count' => $activeCount,
                'max_limit' => $maxLimit,
                'has_overdue' => $hasOverdue,
                'unpaid_fines' => $unpaidFines,
                'quota_reached' => $quotaReached,
                'allowed' => $checkResult['allowed'],
                'reason' => $checkResult['reason'],
            ],
            'reservation' => $reservation ? [
                'id' => $reservation->id,
                'code' => $reservation->reservation_code,
                'book_id' => $reservation->book_id,
                'book_title' => $reservation->book?->title,
                'status' => $reservation->status,
            ] : null,
        ]);
    }

    /**
     * Borrowing Flow Step 3: Issue Book Loan
     */
    public function store(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can issue physical book loans.'));
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'borrow_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:borrow_date',
            'reservation_id' => 'nullable|exists:reservations,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $patron = User::findOrFail($validated['user_id']);
        $book = Book::findOrFail($validated['book_id']);

        // Policy Check
        $check = $patron->canBorrowNewBook();
        if (! $check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        if ($book->available_copies <= 0) {
            return back()->with('error', __('Book is not currently available for borrowing.'));
        }

        DB::transaction(function () use ($validated, $patron, $book, $user) {
            Borrow::create([
                'user_id' => $patron->id,
                'book_id' => $book->id,
                'borrow_date' => $validated['borrow_date'],
                'due_date' => $validated['due_date'],
                'status' => 'Borrowed',
                'fine_amount' => 0.00,
                'fine_paid' => true,
                'book_condition' => 'Good',
                'notes' => $validated['notes'] ?? 'Issued at Circulation Desk',
            ]);

            $book->decrement('available_copies');

            // If this loan was originated from a reservation
            if (!empty($validated['reservation_id'])) {
                $resObj = Reservation::find($validated['reservation_id']);
                if ($resObj) {
                    $resObj->update(['status' => 'Fulfilled']);

                    $dueFormatted = Carbon::parse($validated['due_date'])->format('d M Y');

                    // 1. Notification to User: បានទទួល
                    \App\Models\AdminNotification::create([
                        'user_id' => $patron->id,
                        'book_id' => $book->id,
                        'reservation_id' => $resObj->id,
                        'type' => 'book_issued',
                        'recipient_role' => 'user',
                        'title' => '📖 បានទទួល (Book Received)',
                        'message' => 'លោកអ្នកបានទទួលសៀវភៅ "' . $book->title . '" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ ' . $dueFormatted . '។',
                        'action_url' => route('borrows.index'),
                        'is_read' => false,
                    ]);

                    // 2. Notification to Admin & Manager: ទទួលរួចហើយ
                    \App\Models\AdminNotification::create([
                        'user_id' => $patron->id,
                        'book_id' => $book->id,
                        'reservation_id' => $resObj->id,
                        'type' => 'book_issued_admin',
                        'recipient_role' => 'admin',
                        'title' => '✅ ទទួលរួចហើយ (Book Handed Over)',
                        'message' => 'សមាជិក ' . $patron->name . ' បានមកទទួលសៀវភៅ "' . $book->title . '" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ ' . $dueFormatted . '។',
                        'action_url' => route('borrows.index'),
                        'is_read' => false,
                    ]);
                }
            }

            \App\Models\ActivityLog::log(
                'borrow_issued',
                __(':admin បានកត់ត្រាការឱ្យសមាជិក :patron ខ្ចីសៀវភៅ ":book"', [
                    'admin' => $user->name,
                    'patron' => $patron->name,
                    'book' => $book->title,
                ]),
                $book
            );
        });

        return redirect()->route('borrows.index')->with('success', __('Book ":title" borrowed successfully by :patron!', ['title' => $book->title, 'patron' => $patron->name]));
    }

    /**
     * Return Flow (Step 2 & 3): Return Book & Settle Fines
     */
    public function returnBook(Request $request, Borrow $borrow)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can process book returns.'));
        }

        if ($borrow->status === 'Returned') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('This book has already been returned.')], 422);
            }
            return back()->with('info', __('This book has already been returned.'));
        }

        $now = Carbon::now();
        $today = Carbon::today();
        $condition = $request->input('book_condition', 'Good');
        $dueDate = Carbon::parse($borrow->due_date)->startOfDay();

        // 1. Calculate late return fee (full calendar days overdue)
        $lateFine = 0.00;
        if ($today->greaterThan($dueDate)) {
            $daysLate = (int) abs($dueDate->diffInDays($today));
            $lateFine = max(0.00, $daysLate * 0.50); // $0.50/day standard rate
        }

        // 2. Penalty fee for Lost or Damaged book
        $penaltyFee = 0.00;
        if ($condition === 'Lost') {
            $penaltyFee = (float) $request->input('penalty_fee', 15.00); // Default $15.00 replacement fee
        } elseif ($condition === 'Damaged') {
            $penaltyFee = (float) $request->input('penalty_fee', 5.00);  // Default $5.00 repair fee
        }

        // 3. Custom fine override if specified
        if ($request->filled('custom_fine_amount')) {
            $fine = max(0.00, (float) $request->input('custom_fine_amount'));
        } else {
            $fine = $lateFine + $penaltyFee;
        }

        $fineWaived = $request->boolean('fine_waived', false);
        $fineWaivedReason = $request->input('fine_waived_reason', null);
        $finePaid = $request->boolean('fine_paid', true);
        $notes = $request->input('return_notes', $borrow->notes);

        if ($fineWaived && $user->canWaiveFines()) {
            $fine = 0.00;
            $finePaid = true;
        }

        DB::transaction(function () use ($borrow, $now, $fine, $finePaid, $fineWaived, $fineWaivedReason, $condition, $notes, $user) {
            $newStatus = ($condition === 'Lost') ? 'Lost' : 'Returned';

            $borrow->update([
                'return_date' => $now->toDateString(),
                'status' => $newStatus,
                'fine_amount' => $fine,
                'fine_paid' => $finePaid,
                'fine_waived' => $fineWaived,
                'fine_waived_reason' => $fineWaived ? ($fineWaivedReason ?: __('លើកលែងប្រាក់ពិន័យចំពោះករណីពិសេស')) : null,
                'waived_by' => $fineWaived ? $user->id : null,
                'book_condition' => $condition,
                'notes' => $notes,
            ]);

            if ($condition === 'Lost') {
                // Book is lost: do not increment available_copies, decrement total_copies
                if ($borrow->book && $borrow->book->total_copies > 0) {
                    $borrow->book->decrement('total_copies');
                }
            } else {
                // Book is returned: increment shelf availability
                if ($borrow->book) {
                    $borrow->book->increment('available_copies');
                }
            }

            // If patron account was suspended or temporary and all fines are settled, restore account to Active
            $patron = $borrow->user;
            if ($patron && in_array($patron->status, ['Suspended', 'Temporary']) && $patron->unpaidFinesTotal() == 0 && ! $patron->hasOverdueBooks()) {
                $patron->update(['status' => 'Active']);
            }

            $logAction = ($condition === 'Lost') ? 'book_lost' : 'book_returned';
            $logMsg = ($condition === 'Lost')
                ? __(':admin បានកត់ត្រាការបាត់បង់សៀវភៅ ":book" ដោយសមាជិក :patron (ប្រាក់ពិន័យសង: $:fine)', [
                    'admin' => $user->name,
                    'book' => $borrow->book?->title ?? 'Book',
                    'patron' => $patron?->name ?? 'Patron',
                    'fine' => number_format($fine, 2),
                ])
                : __(':admin បានទទួលសៀវភៅ ":book" សងវិញពីសមាជិក :patron (ស្ថានភាព: :condition, ប្រាក់ពិន័យ: $:fine)', [
                    'admin' => $user->name,
                    'book' => $borrow->book?->title ?? 'Book',
                    'patron' => $patron?->name ?? 'Patron',
                    'condition' => __($condition),
                    'fine' => number_format($fine, 2),
                ]);

            \App\Models\ActivityLog::log($logAction, $logMsg, $borrow);
        });

        $fineMsg = '';
        if ($fineWaived) {
            $fineMsg = ' ' . __('(ប្រាក់ពិន័យត្រូវបានលើកលែង)');
        } elseif ($fine > 0) {
            $paidStatus = $finePaid ? __('Paid') : __('Unpaid (Blocked until paid)');
            $fineMsg = ' ' . __('Fee: $:amount (:status)', ['amount' => number_format($fine, 2), 'status' => $paidStatus]);
        }

        $message = ($condition === 'Lost') 
            ? __('សៀវភៅត្រូវបានកត់ត្រាថាបាត់បង់!') . $fineMsg
            : __('Book returned successfully!') . $fineMsg;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => ($condition === 'Lost') ? 'Lost' : 'Returned',
                'fine_amount' => number_format($fine, 2),
                'fine_paid' => $finePaid,
                'fine_waived' => $fineWaived,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Settle an Outstanding Fine for a patron
     */
    public function settleFine(Request $request, Borrow $borrow)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can settle fines.'));
        }

        $borrow->update([
            'fine_paid' => true,
            'notes' => ($borrow->notes ? $borrow->notes . ' | ' : '') . __('Fine paid on :date', ['date' => now()->format('Y-m-d H:i')]),
        ]);

        $patron = $borrow->user;
        if ($patron && in_array($patron->status, ['Suspended', 'Temporary']) && $patron->unpaidFinesTotal() == 0 && ! $patron->hasOverdueBooks()) {
            $patron->update(['status' => 'Active']);
        }

        \App\Models\ActivityLog::log(
            'fine_settled',
            __(':admin បានកត់ត្រាការទូទាត់ប្រាក់ពិន័យចំនួន $:amount ពីសមាជិក :patron', [
                'admin' => $user->name,
                'amount' => number_format($borrow->fine_amount, 2),
                'patron' => $patron?->name ?? 'Patron',
            ]),
            $borrow
        );

        return back()->with('success', __('ប្រាក់ពិន័យ $:amount ត្រូវបានទូទាត់ជោគជ័យ! គណនីសមាជិកត្រូវបានធ្វើបច្ចុប្បន្នភាព។', ['amount' => number_format($borrow->fine_amount, 2)]));
    }

    /**
     * Reservation Flow: Reservations List
     */
    public function reservations(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        $isAdmin = $user ? $user->isAdmin() : true;

        $query = Reservation::with(['user', 'book']);

        // Data Privacy: If Member, see only own reservations
        if (! $isAdmin) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('reservation_code', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$s}%")->orWhere('card_id', 'like', "%{$s}%"))
                  ->orWhereHas('book', fn($bq) => $bq->where('title', 'like', "%{$s}%")->orWhere('isbn', 'like', "%{$s}%"));
            });
        }

        $reservations = $query->latest('id')->paginate(15)->withQueryString();
        $availableBooks = Book::where('available_copies', '>', 0)->get();

        return view('reservations.index', compact('reservations', 'isAdmin', 'availableBooks'));
    }

    /**
     * Reservation Flow Step 1: User Request Reserve Book
     */
    public function reserve(Request $request, ?Book $book = null)
    {
        $user = Auth::user() ?? User::where('role', 'student')->first() ?? User::first();
        if (! $user) {
            return redirect()->route('login')->with('error', __('Please login to place a book reservation.'));
        }

        // Support both route parameter {book} and request body book_id
        if (! $book) {
            $bookId = $request->input('book_id') ?? $request->route('book');
            if ($bookId instanceof Book) {
                $book = $bookId;
            } elseif ($bookId) {
                $book = Book::find($bookId);
            }
        }

        if (! $book) {
            return back()->with('error', __('Invalid book selected for reservation.'));
        }

        // Check if book has available copies
        if ($book->available_copies <= 0) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Sorry, this book is currently out of stock.'),
                ], 422);
            }
            return back()->with('error', __('Sorry, this book is currently out of stock.'));
        }

        // Check if user already has an active reservation for this book
        $existing = Reservation::where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->first();

        if ($existing) {
            $msg = __('You already have an active hold for this book (Code: :code).', ['code' => $existing->reservation_code]);
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'code' => $existing->reservation_code,
                ], 422);
            }
            return back()->with('info', $msg);
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reservation_date' => now()->toDateString(),
            'status' => 'Pending',
            'notes' => $request->input('notes') ?? 'Member reservation placed online',
        ]);

        // 🔔 Create Real Message / Notification for Admin
        \App\Models\AdminNotification::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'reservation_id' => $reservation->id,
            'type' => 'reservation_request',
            'recipient_role' => 'admin',
            'title' => 'សំណើសុំកក់សៀវភៅថ្មី (New Reservation Request)',
            'message' => 'សមាជិក ' . $user->name . ($user->card_id ? ' (' . $user->card_id . ')' : '') . ' បានស្នើសុំកក់សៀវភៅ "' . $book->title . '" (កូដកក់៖ ' . $reservation->reservation_code . ')។',
            'action_url' => route('reservations.index', ['status' => 'Pending']),
            'is_read' => false,
        ]);

        $successMsg = __('Reservation message sent to Admin! Please wait for Admin to accept your reservation.', ['code' => $reservation->reservation_code]);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'status' => 'pending',
                'message' => $successMsg,
                'code' => $reservation->reservation_code,
                'book_id' => $book->id,
            ]);
        }

        return back()->with('success', $successMsg);
    }

    /**
     * Real-time status check for logged-in user's reservations
     */
    public function checkUserReservationStatus(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'student')->first();
        if (! $user) {
            return response()->json(['success' => false]);
        }

        $reservations = Reservation::where('user_id', $user->id)
            ->whereIn('status', ['Pending', 'Approved'])
            ->with('book')
            ->get();

        $pendingBookIds = $reservations->where('status', 'Pending')->pluck('book_id')->values()->all();
        $approvedBookIds = $reservations->where('status', 'Approved')->pluck('book_id')->values()->all();

        $approvedItems = $reservations->where('status', 'Approved')->map(function ($r) {
            return [
                'id' => $r->id,
                'book_id' => $r->book_id,
                'book_title' => $r->book?->title ?? '',
                'code' => $r->reservation_code,
                'deadline' => $r->pickup_deadline ? $r->pickup_deadline->format('d/m/Y H:i') : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'pending_book_ids' => $pendingBookIds,
            'approved_book_ids' => $approvedBookIds,
            'approved_items' => $approvedItems,
        ]);
    }

    /**
     * Get unread notifications stream for live real-time alert polling
     */
    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'latest' => []]);
        }

        $query = \App\Models\AdminNotification::query()->where('is_read', false);
        if ($user->isAdmin()) {
            $query->where('recipient_role', 'admin');
        } else {
            $query->where('recipient_role', 'user')->where('user_id', $user->id);
        }

        $unreadCount = $query->count();
        $latest = $query->with(['user', 'book'])->latest()->take(5)->get();

        return response()->json([
            'unreadCount' => $unreadCount,
            'latest' => $latest,
        ]);
    }

    /**
     * Mark all admin/user notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && $user->isAdmin()) {
            \App\Models\AdminNotification::where('recipient_role', 'admin')->update(['is_read' => true]);
        } elseif ($user) {
            \App\Models\AdminNotification::where('user_id', $user->id)->where('recipient_role', 'user')->update(['is_read' => true]);
        }
        return back()->with('success', __('All notifications marked as read.'));
    }

    /**
     * Clear all notifications so they disappear completely
     */
    public function clearAllNotifications(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && $user->isAdmin()) {
            \App\Models\AdminNotification::where('recipient_role', 'admin')->delete();
        } elseif ($user) {
            \App\Models\AdminNotification::where('user_id', $user->id)->where('recipient_role', 'user')->delete();
        }
        return back()->with('success', __('All notifications cleared successfully.'));
    }

    /**
     * Dismiss / delete single notification
     */
    public function dismissNotification(Request $request, \App\Models\AdminNotification $notification)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ($user->isAdmin() || $notification->user_id === $user->id)) {
            $notification->delete();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('Notification removed.'));
    }

    /**
     * Mark single notification as read and redirect to its action URL
     */
    public function readNotification(\App\Models\AdminNotification $notification)
    {
        $notification->update(['is_read' => true]);
        return redirect($notification->action_url ?: route('reservations.index'));
    }

    /**
     * Reservation Flow Step 2: Admin Approve / Accept Reservation (ទទួលការកក់)
     */
    public function approveReservation(Request $request, Reservation $reservation)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can approve book reservations.'));
        }

        if ($reservation->status !== 'Pending') {
            return back()->with('info', __('This reservation is already :status.', ['status' => __($reservation->status)]));
        }

        $hours = (int) $request->input('pickup_hours', 48);

        DB::transaction(function () use ($reservation, $hours) {
            $reservation->update([
                'status' => 'Approved',
                'pickup_deadline' => Carbon::now()->addHours($hours),
            ]);

            // Hold a copy from available pool
            if ($reservation->book && $reservation->book->available_copies > 0) {
                $reservation->book->decrement('available_copies');
            }
        });

        // 🔔 Create Message / Notification for User: Reservation Accepted by Admin!
        if ($reservation->user_id) {
            $bookTitle = $reservation->book?->title ?? __('Book');
            $deadline = Carbon::now()->addHours($hours)->format('d/m/Y H:i');
            \App\Models\AdminNotification::create([
                'user_id' => $reservation->user_id,
                'book_id' => $reservation->book_id,
                'reservation_id' => $reservation->id,
                'type' => 'reservation_approved',
                'recipient_role' => 'user',
                'title' => '🎉 បានកក់ជោគជ័យ (Reservation Approved)',
                'message' => 'Admin បានទទួលការកក់សៀវភៅ "' . $bookTitle . '" របស់អ្នករួចរាល់ហើយ! សូមមកទទួលយកនៅបណ្ណាល័យមុនម៉ោង ' . $deadline . ' (កូដកក់៖ ' . $reservation->reservation_code . ')។',
                'action_url' => route('reservations.index'),
                'is_read' => false,
            ]);
        }

        return back()->with('success', __('Reservation :code approved! Held on shelf with a :hours-hour pickup deadline.', ['code' => $reservation->reservation_code, 'hours' => $hours]));
    }

    /**
     * Reservation Flow Step 3: Fulfill reservation directly and issue loan
     */
    public function fulfillReservation(Request $request, Reservation $reservation)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if ($user && ! $user->isAdmin()) {
            return back()->with('error', __('Only library administrators can issue physical book loans.'));
        }

        if ($reservation->status !== 'Approved') {
            return back()->with('error', __('Only approved held books can be issued directly.'));
        }

        $patron = $reservation->user;
        $book = $reservation->book;

        if (! $patron || ! $book) {
            return back()->with('error', __('Patron or Book record not found.'));
        }

        // Check if patron can borrow
        $check = $patron->canBorrowNewBook();
        if (! $check['allowed']) {
            return back()->with('error', $check['reason']);
        }

        $loanDays = (int) $request->input('loan_days', 14);
        $dueFormatted = Carbon::now()->addDays($loanDays)->format('d M Y');

        DB::transaction(function () use ($reservation, $patron, $book, $loanDays, $dueFormatted) {
            Borrow::create([
                'user_id' => $patron->id,
                'book_id' => $book->id,
                'borrow_date' => Carbon::now()->toDateString(),
                'due_date' => Carbon::now()->addDays($loanDays)->toDateString(),
                'status' => 'Borrowed',
                'fine_amount' => 0.00,
                'fine_paid' => true,
                'book_condition' => 'Good',
                'notes' => 'Fulfilled from Hold: ' . $reservation->reservation_code,
            ]);

            $reservation->update([
                'status' => 'Fulfilled',
            ]);

            // 1. Notification to User: បានទទួល
            \App\Models\AdminNotification::create([
                'user_id' => $patron->id,
                'book_id' => $book->id,
                'reservation_id' => $reservation->id,
                'type' => 'book_issued',
                'recipient_role' => 'user',
                'title' => '📖 បានទទួល (Book Received)',
                'message' => 'លោកអ្នកបានទទួលសៀវភៅ "' . $book->title . '" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ ' . $dueFormatted . '។',
                'action_url' => route('borrows.index'),
                'is_read' => false,
            ]);

            // 2. Notification to Admin & Manager: ទទួលរួចហើយ
            \App\Models\AdminNotification::create([
                'user_id' => $patron->id,
                'book_id' => $book->id,
                'reservation_id' => $reservation->id,
                'type' => 'book_issued_admin',
                'recipient_role' => 'admin',
                'title' => '✅ ទទួលរួចហើយ (Book Handed Over)',
                'message' => 'សមាជិក ' . $patron->name . ' បានមកទទួលសៀវភៅ "' . $book->title . '" រួចរាល់ហើយ! ថ្ងៃត្រូវសងគឺ ' . $dueFormatted . '។',
                'action_url' => route('borrows.index'),
                'is_read' => false,
            ]);
        });

        return back()->with('success', __('ទទួលរួចហើយ! សៀវភៅ ":book" ត្រូវបានឱ្យទៅកាន់ :patron រួចរាល់។ ថ្ងៃត្រូវសង៖ :due', [
            'book' => $book->title,
            'patron' => $patron->name,
            'due' => $dueFormatted,
        ]));
    }

    /**
     * Reservation Flow: Reject or Cancel Reservation
     */
    public function cancelReservation(Request $request, Reservation $reservation)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        $isAdmin = $user ? $user->isAdmin() : true;

        // User can only cancel their own reservation unless Admin
        if (! $isAdmin && $reservation->user_id !== $user->id) {
            return back()->with('error', __('You can only cancel your own reservation requests.'));
        }

        DB::transaction(function () use ($reservation, $user, $isAdmin) {
            $wasApproved = $reservation->status === 'Approved';

            $reservation->update(['status' => 'Cancelled']);

            // If a copy was previously held, return it to stock
            if ($wasApproved && $reservation->book) {
                $reservation->book->increment('available_copies');
            }

            // 🔔 Create Notification for Admin & Manager (លូតមកប្រាប់ Admin and Manager ថាបោះបង់ហើយ)
            $bookTitle = $reservation->book?->title ?? __('Book');
            $patron = $reservation->user ?? $user;
            $patronName = $patron ? $patron->name : __('Member');
            $patronCard = $patron && $patron->card_id ? ' (' . $patron->card_id . ')' : '';

            \App\Models\AdminNotification::create([
                'user_id' => $reservation->user_id ?? $user->id,
                'book_id' => $reservation->book_id,
                'reservation_id' => $reservation->id,
                'type' => 'reservation_cancelled',
                'recipient_role' => 'admin',
                'title' => '❌ សមាជិកបានបោះបង់ការកក់សៀវភៅ (Reservation Cancelled)',
                'message' => 'សមាជិក ' . $patronName . $patronCard . ' បានសម្រេចបោះបង់ការកក់សៀវភៅ "' . $bookTitle . '" (កូដកក់៖ ' . $reservation->reservation_code . ')។ ចំនួនសៀវភៅក្នុងស្តុកត្រូវបានដាក់ត្រឡប់មកវិញដោយស្វ័យប្រវត្តិ។',
                'action_url' => route('reservations.index', ['status' => 'Cancelled']),
                'is_read' => false,
            ]);

            // Activity Log
            \App\Models\ActivityLog::log(
                'reservation_cancelled',
                __('សមាជិក :patron បានបោះបង់ការកក់សៀវភៅ ":book" (កូដ៖ :code)', [
                    'patron' => $patronName . $patronCard,
                    'book' => $bookTitle,
                    'code' => $reservation->reservation_code,
                ]),
                $reservation
            );
        });

        return back()->with('success', __('Reservation :code has been cancelled.', ['code' => $reservation->reservation_code]));
    }

    /**
     * Waive Overdue Fine (សម្រេចលើការលើកលែងពិន័យ - Manager/Super Admin Exclusive)
     */
    public function waiveFine(Request $request, Borrow $borrow)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if (!$user || !$user->canWaiveFines()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => __('មានតែ Manager ឬ Super Admin ទើបមានសិទ្ធិលើកលែងប្រាក់ពិន័យ។')], 403);
            }
            return back()->with('error', __('មានតែ Manager ឬ Super Admin ទើបមានសិទ្ធិលើកលែងប្រាក់ពិន័យ។'));
        }

        $reason = trim($request->input('reason', ''));
        if (empty($reason)) {
            $reason = __('សិស្សមានធុរៈឈឺ មានលិខិតបញ្ជាក់ត្រឹមត្រូវ (ករណីពិសេស)');
        }

        $originalFine = (float)$borrow->fine_amount;

        DB::transaction(function () use ($borrow, $reason, $user, $originalFine) {
            $borrow->update([
                'fine_waived' => true,
                'fine_waived_reason' => $reason,
                'waived_by' => $user->id,
                'fine_amount' => 0.00,
                'fine_paid' => true,
            ]);

            // Reactivate patron account if suspended or temporary
            $patron = $borrow->user;
            if ($patron && in_array($patron->status, ['Suspended', 'Temporary']) && $patron->unpaidFinesTotal() == 0 && !$patron->hasOverdueBooks()) {
                $patron->update(['status' => 'Active']);
            }

            \App\Models\ActivityLog::log(
                'fine_waived',
                __(':admin បានសម្រេចលើកលែងប្រាក់ពិន័យ $:amount ជូនសមាជិក :patron (មូលហេតុ៖ :reason)', [
                    'admin' => $user->name,
                    'amount' => number_format($originalFine, 2),
                    'patron' => $patron?->name ?? 'Member',
                    'reason' => $reason,
                ]),
                $borrow
            );
        });

        $msg = __('បានលើកលែងប្រាក់ពិន័យយឺត $:amount ដោយជោគជ័យ! មូលហេតុ៖ :reason', [
            'amount' => number_format($originalFine, 2),
            'reason' => $reason,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'fine_amount' => '0.00',
                'fine_waived' => true,
                'fine_waived_reason' => $reason,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Force Cancel Reservation (លុបចោលការកក់ពិសេស - Manager/Super Admin Exclusive)
     */
    public function forceCancelReservation(Request $request, Reservation $reservation)
    {
        $user = Auth::user() ?? User::where('role', 'admin')->first();
        if (!$user || !$user->canForceCancelReservations()) {
            return back()->with('error', __('មានតែ Manager ឬ Super Admin ទើបមានសិទ្ធិលុបចោលការកក់បន្ទាន់។'));
        }

        $reason = trim($request->input('reason', ''));
        if (empty($reason)) {
            $reason = __('សៀវភៅត្រូវការប្រើប្រាស់ជាបន្ទាន់ក្នុងកម្មវិធីសាលា');
        }

        DB::transaction(function () use ($reservation, $reason, $user) {
            $wasApproved = $reservation->status === 'Approved';

            $reservation->update([
                'status' => 'Cancelled',
                'notes' => ($reservation->notes ? $reservation->notes . ' | ' : '') . 'Force cancelled by Manager: ' . $reason,
            ]);

            // Restock book if it was held
            if ($wasApproved && $reservation->book) {
                $reservation->book->increment('available_copies');
            }

            // Create notification for the patron
            if ($reservation->user_id) {
                \App\Models\AdminNotification::create([
                    'user_id' => $reservation->user_id,
                    'book_id' => $reservation->book_id,
                    'reservation_id' => $reservation->id,
                    'type' => 'reservation_force_cancelled',
                    'recipient_role' => 'user',
                    'title' => '⚠️ ការកក់ត្រូវបានលុបចោលពិសេស (Reservation Force Cancelled)',
                    'message' => 'ប្រធានគ្រប់គ្រងបានសម្រេចលុបចោលការកក់សៀវភៅ "' . ($reservation->book?->title ?? 'Book') . '" របស់អ្នក ដោយសារមូលហេតុ៖ ' . $reason . '។ សូមអភ័យទោសចំពោះការរអាក់រអួល!',
                    'action_url' => route('reservations.index'),
                    'is_read' => false,
                ]);
            }

            \App\Models\ActivityLog::log(
                'reservation_force_cancelled',
                __(':admin បានលុបចោលការកក់ពិសេសកូដ :code របស់សមាជិក :patron (មូលហេតុ៖ :reason)', [
                    'admin' => $user->name,
                    'code' => $reservation->reservation_code,
                    'patron' => $reservation->user?->name ?? 'Member',
                    'reason' => $reason,
                ]),
                $reservation
            );
        });

        return back()->with('success', __('ការកក់ :code ត្រូវបានលុបចោលបន្ទាន់ដោយជោគជ័យ! សៀវភៅត្រូវបានដាក់ចូលស្តុកវិញ។', ['code' => $reservation->reservation_code]));
    }
}
