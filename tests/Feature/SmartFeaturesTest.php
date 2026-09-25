<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Borrow;
use Carbon\Carbon;
use Tests\TestCase;

class SmartFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    public function test_student_dashboard_displays_academic_year_recommendations()
    {
        $student = User::where('email', 'vannak.keo@student.edu.kh')->first();
        $this->actingAs($student);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('សៀវភៅណែនាំឆ្លាតវៃសម្រាប់និស្សិត');
    }

    public function test_books_catalog_can_filter_by_academic_year()
    {
        $student = User::where('email', 'vannak.keo@student.edu.kh')->first();
        $this->actingAs($student);

        $response = $this->get(route('books.index', ['academic_year' => 2]));
        $response->assertStatus(200);
        $response->assertSee('Clean Code');
    }

    public function test_external_patrons_can_borrow_books_for_free_without_subscription()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        // General / external member (Kosal Heng)
        $kosal = User::where('email', 'kosal.heng@gmail.com')->first();
        $this->assertEquals('General', $kosal->member_type);
        $this->assertEquals(2, $kosal->maxAllowedLoans());

        // Kosal has no unpaid fines initially
        $this->assertTrue($kosal->canBorrowNewBook()['allowed']);

        // Issue a book to Kosal
        $book = Book::where('available_copies', '>', 0)->first();
        $initialAvailable = $book->available_copies;

        $response = $this->post(route('borrows.store'), [
            'user_id' => $kosal->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::today()->toDateString(),
            'due_date' => Carbon::today()->addDays(14)->toDateString(),
            'notes' => 'Free loan for external community patron',
        ]);

        $response->assertRedirect();
        $book->refresh();
        $this->assertEquals($initialAvailable - 1, $book->available_copies);

        $this->assertDatabaseHas('borrows', [
            'user_id' => $kosal->id,
            'book_id' => $book->id,
            'status' => 'Borrowed',
            'fine_amount' => 0.00,
        ]);
    }

    public function test_returning_book_late_assesses_overdue_fine()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $patron = User::where('role', 'member')->first();
        $book = Book::where('available_copies', '>', 0)->first();

        // Create a borrow that is 6 days overdue
        $borrow = Borrow::create([
            'user_id' => $patron->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::today()->subDays(20)->toDateString(),
            'due_date' => Carbon::today()->subDays(6)->toDateString(),
            'status' => 'Overdue',
            'fine_amount' => 0.00,
        ]);
        $book->decrement('available_copies');

        // Return book late with fine unpaid (e.g. cash not yet received)
        $response = $this->post(route('borrows.return', $borrow), [
            'book_condition' => 'Good',
            'fine_paid' => '0',
            'return_notes' => 'Returned 6 days late',
        ]);

        $response->assertRedirect();
        $borrow->refresh();

        $this->assertEquals('Returned', $borrow->status);
        // 6 days * $0.50 = $3.00
        $this->assertEquals(3.00, (float) $borrow->fine_amount);
        $this->assertFalse((bool) $borrow->fine_paid);

        // Patron now has $3.00 unpaid fines and is blocked from borrowing
        $patron->refresh();
        $this->assertEquals(3.00, $patron->unpaidFinesTotal());
        $this->assertFalse($patron->canBorrowNewBook()['allowed']);
    }

    public function test_returning_book_as_lost_assesses_replacement_fee_and_updates_inventory()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $patron = User::where('role', 'member')->first();
        $book = Book::where('available_copies', '>', 0)->first();
        $initialTotal = $book->total_copies;
        $initialAvailable = $book->available_copies;

        $borrow = Borrow::create([
            'user_id' => $patron->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::today()->subDays(10)->toDateString(),
            'due_date' => Carbon::today()->addDays(4)->toDateString(),
            'status' => 'Borrowed',
            'fine_amount' => 0.00,
        ]);
        $book->decrement('available_copies');

        // Report as Lost with $15 replacement fee, fine unpaid
        $response = $this->post(route('borrows.return', $borrow), [
            'book_condition' => 'Lost',
            'penalty_fee' => 15.00,
            'fine_paid' => '0',
            'return_notes' => 'Patron reported book lost while traveling',
        ]);

        $response->assertRedirect();
        $borrow->refresh();
        $book->refresh();

        // Borrow record is marked Lost with $15.00 fine
        $this->assertEquals('Lost', $borrow->status);
        $this->assertEquals('Lost', $borrow->book_condition);
        $this->assertEquals(15.00, (float) $borrow->fine_amount);
        $this->assertFalse((bool) $borrow->fine_paid);
        $this->assertTrue($borrow->isLost());

        // Inventory: total_copies decremented, available_copies not restocked
        $this->assertEquals($initialTotal - 1, $book->total_copies);
        $this->assertEquals($initialAvailable - 1, $book->available_copies);

        // Patron is blocked due to lost book debt
        $patron->refresh();
        $this->assertEquals(15.00, $patron->unpaidFinesTotal());
        $this->assertFalse($patron->canBorrowNewBook()['allowed']);
    }

    public function test_settling_unpaid_fine_unblocks_patron()
    {
        $admin = User::where('role', 'admin')->first();
        $this->actingAs($admin);

        $patron = User::where('role', 'member')->first();
        $book = Book::first();

        // Create an unpaid fine borrow
        $borrow = Borrow::create([
            'user_id' => $patron->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::today()->subDays(30)->toDateString(),
            'due_date' => Carbon::today()->subDays(16)->toDateString(),
            'return_date' => Carbon::today()->toDateString(),
            'status' => 'Returned',
            'fine_amount' => 7.00,
            'fine_paid' => false,
            'fine_waived' => false,
        ]);

        $patron->refresh();
        $this->assertFalse($patron->canBorrowNewBook()['allowed']);

        // Settle fine
        $response = $this->post(route('borrows.settle-fine', $borrow));
        $response->assertRedirect();

        $borrow->refresh();
        $patron->refresh();

        $this->assertTrue((bool) $borrow->fine_paid);
        $this->assertEquals(0.00, $patron->unpaidFinesTotal());
        $this->assertTrue($patron->canBorrowNewBook()['allowed']);
    }
}
