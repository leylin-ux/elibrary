<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\MembershipPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with([
            'activeBorrows.book',
            'borrows' => fn($q) => $q->latest('borrow_date')->with('book')->take(5),
        ])->withCount([
            'borrows as total_borrows_count',
            'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
            'reservations as active_reservations_count' => fn($q) => $q->whereIn('status', ['Pending', 'Approved']),
        ]);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('card_id', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('major', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        if ($request->filled('member_type')) {
            $query->where('member_type', $request->member_type);
        }

        if ($request->filled('academic_year')) {
            $query->where('academic_year', $request->academic_year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('fine_status')) {
            if ($request->fine_status === 'unpaid') {
                $query->whereHas('borrows', fn($q) => $q->where('fine_paid', false)->where('fine_waived', false)->where('fine_amount', '>', 0));
            } elseif ($request->fine_status === 'paid') {
                $query->whereHas('borrows', fn($q) => $q->where('fine_paid', true)->where('fine_amount', '>', 0));
            }
        }

        if ($request->filled('loan_filter')) {
            if ($request->loan_filter === 'active_loans') {
                $query->whereHas('borrows', fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']));
            } elseif ($request->loan_filter === 'no_loans') {
                $query->whereDoesntHave('borrows', fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']));
            }
        }

        $counts = [
            'total' => User::count(),
            'active' => User::where('status', 'Active')->count(),
            'students' => User::where('member_type', 'Student')->count(),
            'teachers' => User::where('member_type', 'Teacher')->count(),
            'general' => User::where('member_type', 'General')->count(),
            'with_unpaid_fines' => User::whereHas('borrows', fn($q) => $q->where('fine_paid', false)->where('fine_waived', false)->where('fine_amount', '>', 0))->count(),
            'temporary' => User::where('status', 'Temporary')->count(),
            'suspended' => User::where('status', 'Suspended')->count(),
            'active_loans' => User::whereHas('borrows', fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']))->count(),
        ];

        $members = $query->latest('id')->paginate(12)->withQueryString();
        $availableBooks = Book::where('available_copies', '>', 0)->orderBy('title')->get();

        return view('members.index', compact('members', 'counts', 'availableBooks'));
    }

    public function show(Request $request, User $member)
    {
        $member->load([
            'activeBorrows.book.category',
            'completedBorrows' => fn($q) => $q->latest('return_date')->with('book.category'),
            'reservations' => fn($q) => $q->latest('reservation_date')->with('book'),
        ])->loadCount([
            'borrows as total_borrows_count',
            'borrows as active_borrows_count' => fn($q) => $q->whereIn('status', ['Borrowed', 'Overdue']),
            'reservations as active_reservations_count' => fn($q) => $q->whereIn('status', ['Pending', 'Approved']),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'member' => $member,
            ]);
        }

        $availableBooks = Book::where('available_copies', '>', 0)->orderBy('title')->get();

        // Fines & Penalties History (Late returns & Lost book fees)
        $finesHistory = $member->borrows()
            ->where(function ($q) {
                $q->where('fine_amount', '>', 0)
                  ->orWhere('status', 'Lost')
                  ->orWhere('book_condition', 'Lost');
            })
            ->latest('updated_at')
            ->with('book')
            ->get();

        $totalUnpaidFines = $member->unpaidFinesTotal();
        $totalPaidFines = (float) $member->borrows()->where('fine_paid', true)->where('fine_waived', false)->sum('fine_amount');
        $totalWaivedFines = (float) $member->borrows()->where('fine_waived', true)->sum('fine_amount');

        return view('members.show', compact('member', 'availableBooks', 'finesHistory', 'totalUnpaidFines', 'totalPaidFines', 'totalWaivedFines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'card_id' => 'required|string|max:50|unique:users,card_id',
            'member_type' => 'required|string|in:Student,Teacher,General,Staff',
            'academic_year' => 'nullable|integer|min:1|max:4',
            'major' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|string|in:Active,Temporary,Suspended',
            'photo' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'password' => 'nullable|string|min:6',
            
            // Optional Initial Book Loan
            'issue_initial_book' => 'nullable',
            'initial_book_id' => 'nullable|required_if:issue_initial_book,1|exists:books,id',
            'initial_borrow_date' => 'nullable|date',
            'initial_due_date' => 'nullable|date',
            'initial_loan_notes' => 'nullable|string|max:500',
        ]);

        $validated['password'] = Hash::make($validated['password'] ?? 'password123');
        $validated['role'] = 'member';

        if (empty($validated['photo'])) {
            $validated['photo'] = 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80';
        }

        DB::transaction(function () use ($validated, &$member) {
            $member = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'card_id' => $validated['card_id'],
                'member_type' => $validated['member_type'],
                'academic_year' => $validated['member_type'] === 'Student' ? ($validated['academic_year'] ?? 1) : null,
                'major' => $validated['major'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'photo' => $validated['photo'],
                'notes' => $validated['notes'] ?? null,
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            // If an initial book loan was requested
            if (!empty($validated['issue_initial_book']) && !empty($validated['initial_book_id'])) {
                $book = Book::where('id', $validated['initial_book_id'])
                    ->where('available_copies', '>', 0)
                    ->lockForUpdate()
                    ->first();

                if ($book) {
                    $borrowDate = !empty($validated['initial_borrow_date']) 
                        ? Carbon::parse($validated['initial_borrow_date'])->toDateString() 
                        : Carbon::today()->toDateString();

                    $dueDate = !empty($validated['initial_due_date']) 
                        ? Carbon::parse($validated['initial_due_date'])->toDateString() 
                        : Carbon::parse($borrowDate)->addDays(14)->toDateString();

                    Borrow::create([
                        'user_id' => $member->id,
                        'book_id' => $book->id,
                        'borrow_date' => $borrowDate,
                        'due_date' => $dueDate,
                        'status' => 'Borrowed',
                        'fine_amount' => 0.00,
                        'notes' => $validated['initial_loan_notes'] ?? 'Initial loan issued upon member registration',
                    ]);

                    $book->decrement('available_copies');
                }
            }
        });

        $message = !empty($validated['issue_initial_book'])
            ? __('Member registered and initial book loan issued successfully!')
            : __('Member added successfully!');

        return redirect()->route('members.index')->with('success', $message);
    }

    public function update(Request $request, User $member)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email,' . $member->id,
            'card_id' => 'required|string|max:50|unique:users,card_id,' . $member->id,
            'member_type' => 'required|string|in:Student,Teacher,General,Staff',
            'academic_year' => 'nullable|integer|min:1|max:4',
            'major' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|string|in:Active,Temporary,Suspended',
            'photo' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        if ($validated['member_type'] !== 'Student') {
            $validated['academic_year'] = null;
        }

        $member->update($validated);

        if ($request->has('redirect_to') && $request->redirect_to === 'show') {
            return redirect()->route('members.show', $member)->with('success', __('Member updated successfully!'));
        }

        return redirect()->route('members.index')->with('success', __('Member updated successfully!'));
    }

    public function toggleStatus(Request $request, User $member)
    {
        if ($request->filled('status') && in_array($request->status, ['Active', 'Temporary', 'Suspended'])) {
            $newStatus = $request->status;
        } else {
            // Cycle: Active -> Temporary (ផ្អាកបណ្តោះអាសន្ន) -> Suspended (បានផ្អាក) -> Active (សកម្ម)
            if ($member->status === 'Active') {
                $newStatus = 'Temporary';
            } elseif ($member->status === 'Temporary') {
                $newStatus = 'Suspended';
            } else {
                $newStatus = 'Active';
            }
        }

        $member->update(['status' => $newStatus]);

        $statusLabel = match($newStatus) {
            'Active' => __('Active'),
            'Temporary' => __('Temporary'),
            'Suspended' => __('Suspended'),
        };
        $buttonLabel = $statusLabel;

        \App\Models\ActivityLog::log(
            'member_moderated',
            __(':admin បានប្តូរស្ថានភាពសមាជិក :member ទៅជា :status', [
                'admin' => auth()->user()?->name ?? 'Admin',
                'member' => $member->name,
                'status' => $statusLabel,
            ]),
            $member
        );

        $message = __('Member status changed to :status!', ['status' => $statusLabel]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $newStatus,
                'status_label' => $statusLabel,
                'button_label' => $buttonLabel,
            ]);
        }

        return back()->with('success', $message);
    }

    public function destroy(User $member)
    {
        if ($member->activeBorrows()->exists()) {
            return redirect()->route('members.index')->with('error', __('Cannot remove this member because they currently have active book loans. Please return all borrowed books first.'));
        }

        $name = $member->name;
        $member->delete();

        \App\Models\ActivityLog::log(
            'member_deleted',
            __(':admin បានលុបគណនីសមាជិក :member ចេញពីប្រព័ន្ធ', [
                'admin' => auth()->user()?->name ?? 'Admin',
                'member' => $name,
            ]),
            null
        );

        return redirect()->route('members.index')->with('success', __('Member deleted successfully!'));
    }
}
