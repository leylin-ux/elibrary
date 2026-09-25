<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Borrow;
use App\Models\Category;
use App\Models\MembershipPayment;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User
        $admin = User::create([
            'name' => 'Sokha Chan (Super Admin)',
            'email' => 'admin@elibrary.com',
            'card_id' => 'LIB-ADM-001',
            'member_type' => 'Staff',
            'status' => 'Active',
            'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            'phone' => '012 889 900',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 1.1 Manager User (ប្រធានគ្រប់គ្រងបណ្ណាល័យ)
        $manager = User::create([
            'name' => 'Dara Vichea (ប្រធានបណ្ណាល័យ)',
            'email' => 'manager@elibrary.com',
            'card_id' => 'LIB-MGR-001',
            'member_type' => 'Staff',
            'status' => 'Active',
            'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            'phone' => '012 554 433',
            'role' => 'manager',
            'password' => Hash::make('password'),
        ]);

        // 2. Members / Students & Teachers & External General Members
        $membersData = [
            [
                'name' => 'Vannak Keo',
                'email' => 'vannak.keo@student.edu.kh',
                'card_id' => 'LIB-2025-010',
                'member_type' => 'Student',
                'academic_year' => 2,
                'major' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ (Computer Science)',
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                'phone' => '098 776 543',
                'role' => 'member',
            ],
            [
                'name' => 'Sreymom Pich',
                'email' => 'sreymom.pich@student.edu.kh',
                'card_id' => 'LIB-2025-011',
                'member_type' => 'Student',
                'academic_year' => 3,
                'major' => 'គ្រប់គ្រងពាណិជ្ជកម្ម (Business Administration)',
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
                'phone' => '077 345 678',
                'role' => 'member',
            ],
            [
                'name' => 'Dr. Rithy Seng',
                'email' => 'rithy.seng@faculty.edu.kh',
                'card_id' => 'LIB-2025-002',
                'member_type' => 'Teacher',
                'academic_year' => null,
                'major' => 'ដេប៉ាតឺម៉ង់វិទ្យាសាស្ត្រកុំព្យូទ័រ (Faculty of CS)',
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'phone' => '015 990 123',
                'role' => 'member',
            ],
            [
                'name' => 'Bopha Chea',
                'email' => 'bopha.chea@student.edu.kh',
                'card_id' => 'LIB-2025-015',
                'member_type' => 'Student',
                'academic_year' => 1,
                'major' => 'ប្រវត្តិវិទ្យា និងវប្បធម៌ (History & Culture)',
                'status' => 'Suspended',
                'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80',
                'phone' => '089 443 210',
                'role' => 'member',
            ],
            [
                'name' => 'Dara Samnang',
                'email' => 'dara.samnang@gmail.com',
                'card_id' => 'LIB-2025-019',
                'member_type' => 'General',
                'academic_year' => null,
                'major' => 'អ្នកស្រាវជ្រាវឯករាជ្យ (Independent Researcher)',
                'membership_expires_at' => Carbon::now()->addDays(45),
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
                'phone' => '011 223 344',
                'role' => 'member',
            ],
            [
                'name' => 'Kosal Heng (Expired Sub)',
                'email' => 'kosal.heng@gmail.com',
                'card_id' => 'LIB-2025-025',
                'member_type' => 'General',
                'academic_year' => null,
                'major' => 'សាធារណជន (Public Member)',
                'membership_expires_at' => Carbon::now()->subDays(5),
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
                'phone' => '012 334 455',
                'role' => 'member',
            ],
        ];

        $users = [$admin];
        foreach ($membersData as $data) {
            $data['password'] = Hash::make('password');
            $users[] = User::create($data);
        }

        // 3. Categories
        $categories = [
            Category::create([
                'name' => 'Computer Science & IT',
                'slug' => 'computer-science',
                'icon' => 'code',
                'description' => 'Programming, Web Development, AI, Algorithms',
            ]),
            Category::create([
                'name' => 'Business & Management',
                'slug' => 'business-management',
                'icon' => 'briefcase',
                'description' => 'Entrepreneurship, Marketing, Leadership, Finance',
            ]),
            Category::create([
                'name' => 'Literature & Novels',
                'slug' => 'literature-novels',
                'icon' => 'book-open',
                'description' => 'World Classics, Fiction, Poetry, Khmer Literature',
            ]),
            Category::create([
                'name' => 'History & Culture',
                'slug' => 'history-culture',
                'icon' => 'landmark',
                'description' => 'Cambodian History, Civilization, Archaeology',
            ]),
            Category::create([
                'name' => 'Science & Mathematics',
                'slug' => 'science-math',
                'icon' => 'atom',
                'description' => 'Physics, Biology, Chemistry, Applied Mathematics',
            ]),
        ];

        // 4. Books
        $booksData = [
            [
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'isbn' => '978-0132350884',
                'category_id' => $categories[0]->id,
                'target_academic_year' => 2,
                'recommended_major' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ (Computer Science)',
                'cover_image' => 'https://images.unsplash.com/photo-1532012164546-f432f2e3777a?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 8,
                'available_copies' => 5,
                'location_shelf' => 'Shelf CS-01',
                'published_year' => 2008,
                'description' => 'Even bad code can function. But if code isn\'t clean, it can bring a development organization to its knees.',
            ],
            [
                'title' => 'Designing Data-Intensive Applications',
                'author' => 'Martin Kleppmann',
                'isbn' => '978-1449373320',
                'category_id' => $categories[0]->id,
                'target_academic_year' => 3,
                'recommended_major' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ (Computer Science)',
                'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 5,
                'available_copies' => 2,
                'location_shelf' => 'Shelf CS-03',
                'published_year' => 2017,
                'description' => 'The definitive guide to architecture and principles for big data systems and scalable applications.',
            ],
            [
                'title' => 'The Lean Startup',
                'author' => 'Eric Ries',
                'isbn' => '978-0307887894',
                'category_id' => $categories[1]->id,
                'target_academic_year' => 3,
                'recommended_major' => 'គ្រប់គ្រងពាណិជ្ជកម្ម (Business Administration)',
                'cover_image' => 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 6,
                'available_copies' => 4,
                'location_shelf' => 'Shelf BM-02',
                'published_year' => 2011,
                'description' => 'How today\'s entrepreneurs use continuous innovation to create radically successful businesses.',
            ],
            [
                'title' => 'Zero to One: Notes on Startups',
                'author' => 'Peter Thiel, Blake Masters',
                'isbn' => '978-0804139298',
                'category_id' => $categories[1]->id,
                'target_academic_year' => 4,
                'recommended_major' => 'គ្រប់គ្រងពាណិជ្ជកម្ម (Business Administration)',
                'cover_image' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 4,
                'available_copies' => 1,
                'location_shelf' => 'Shelf BM-04',
                'published_year' => 2014,
                'description' => 'How to build companies that create new things, moving the world from 0 to 1.',
            ],
            [
                'title' => 'A History of Cambodia',
                'author' => 'David P. Chandler',
                'isbn' => '978-0813343631',
                'category_id' => $categories[3]->id,
                'target_academic_year' => 1,
                'recommended_major' => 'ប្រវត្តិវិទ្យា និងវប្បធម៌ (History & Culture)',
                'cover_image' => 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 7,
                'available_copies' => 6,
                'location_shelf' => 'Shelf HC-01',
                'published_year' => 2007,
                'description' => 'In this clear and authoritative account of Cambodia\'s history from prehistoric times to the modern era.',
            ],
            [
                'title' => 'Atomic Habits',
                'author' => 'James Clear',
                'isbn' => '978-0735211292',
                'category_id' => $categories[1]->id,
                'target_academic_year' => null,
                'recommended_major' => 'ទូទៅ (General)',
                'cover_image' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 10,
                'available_copies' => 0,
                'location_shelf' => 'Shelf BM-01',
                'published_year' => 2018,
                'description' => 'An easy & proven way to build good habits and break bad ones.',
            ],
            [
                'title' => 'Brief Answers to the Big Questions',
                'author' => 'Stephen Hawking',
                'isbn' => '978-1984819192',
                'category_id' => $categories[4]->id,
                'target_academic_year' => 1,
                'recommended_major' => 'វិទ្យាសាស្ត្រ និងគណិត (Science & Mathematics)',
                'cover_image' => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 5,
                'available_copies' => 3,
                'location_shelf' => 'Shelf SM-02',
                'published_year' => 2018,
                'description' => 'The world-famous cosmologist and bestselling author leaves us with his final thoughts on the universe\'s biggest questions.',
            ],
            [
                'title' => 'The Great Gatsby',
                'author' => 'F. Scott Fitzgerald',
                'isbn' => '978-0743273565',
                'category_id' => $categories[2]->id,
                'target_academic_year' => 2,
                'recommended_major' => 'អក្សរសាស្ត្រ និងភាសា (Literature)',
                'cover_image' => 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&auto=format&fit=crop&q=80',
                'total_copies' => 6,
                'available_copies' => 4,
                'location_shelf' => 'Shelf LN-05',
                'published_year' => 1925,
                'description' => 'A portrait of the Jazz Age in all its decadence and excess.',
            ],
        ];

        $books = [];
        foreach ($booksData as $data) {
            $books[] = Book::create($data);
        }

        // 5. Borrows (recent transactions with various statuses)
        $now = Carbon::now();
        Borrow::create([
            'user_id' => $users[1]->id, // Vannak Keo
            'book_id' => $books[0]->id, // Clean Code
            'borrow_date' => $now->copy()->subDays(5)->toDateString(),
            'due_date' => $now->copy()->addDays(9)->toDateString(),
            'return_date' => null,
            'fine_amount' => 0.00,
            'status' => 'Borrowed',
            'notes' => 'For term project research',
        ]);

        Borrow::create([
            'user_id' => $users[2]->id, // Sreymom Pich
            'book_id' => $books[5]->id, // Atomic Habits
            'borrow_date' => $now->copy()->subDays(18)->toDateString(),
            'due_date' => $now->copy()->subDays(4)->toDateString(),
            'return_date' => null,
            'fine_amount' => 2.00,
            'status' => 'Overdue',
            'notes' => 'Notification reminder sent to email',
        ]);

        Borrow::create([
            'user_id' => $users[3]->id, // Dr. Rithy Seng
            'book_id' => $books[1]->id, // Designing Data-Intensive
            'borrow_date' => $now->copy()->subDays(2)->toDateString(),
            'due_date' => $now->copy()->addDays(12)->toDateString(),
            'return_date' => null,
            'fine_amount' => 0.00,
            'status' => 'Borrowed',
            'notes' => 'Course syllabus preparation',
        ]);

        Borrow::create([
            'user_id' => $users[4]->id, // Bopha Chea
            'book_id' => $books[4]->id, // History of Cambodia
            'borrow_date' => $now->copy()->subDays(25)->toDateString(),
            'due_date' => $now->copy()->subDays(11)->toDateString(),
            'return_date' => null,
            'fine_amount' => 5.50,
            'status' => 'Overdue',
            'notes' => 'Account temporarily suspended due to late fine',
        ]);

        Borrow::create([
            'user_id' => $users[5]->id, // Dara Samnang
            'book_id' => $books[2]->id, // The Lean Startup
            'borrow_date' => $now->copy()->subDays(14)->toDateString(),
            'due_date' => $now->copy()->subDays(2)->toDateString(),
            'return_date' => $now->copy()->subDays(1)->toDateString(),
            'fine_amount' => 0.50,
            'status' => 'Returned',
            'notes' => 'Book returned in good condition',
        ]);

        Borrow::create([
            'user_id' => $users[1]->id, // Vannak Keo
            'book_id' => $books[7]->id, // The Great Gatsby
            'borrow_date' => $now->copy()->subDays(30)->toDateString(),
            'due_date' => $now->copy()->subDays(16)->toDateString(),
            'return_date' => $now->copy()->subDays(17)->toDateString(),
            'fine_amount' => 0.00,
            'status' => 'Returned',
            'notes' => 'Returned on time',
        ]);

        // 6. Reservations
        Reservation::create([
            'user_id' => $users[5]->id,
            'book_id' => $books[5]->id, // Atomic Habits (all copies checked out)
            'reservation_date' => $now->copy()->subDays(1)->toDateString(),
            'status' => 'Pending',
            'notes' => 'First in line for next return',
        ]);

        Reservation::create([
            'user_id' => $users[2]->id,
            'book_id' => $books[3]->id, // Zero to One
            'reservation_date' => $now->copy()->subDays(3)->toDateString(),
            'status' => 'Approved',
            'notes' => 'Ready for pick-up at counter',
        ]);

        // 6.1 Membership Payments (Fee Management for Non-Student Patrons)
        // Dara Samnang (Active Paid Subscription)
        MembershipPayment::create([
            'user_id' => $users[5]->id,
            'plan_type' => 'Quarterly',
            'amount' => 12.00,
            'payment_method' => 'ABA KHQR',
            'reference_no' => 'MEM-2026-001',
            'payment_date' => $now->copy()->subDays(45)->toDateString(),
            'start_date' => $now->copy()->subDays(45)->toDateString(),
            'expires_at' => $now->copy()->addDays(45),
            'status' => 'Paid',
            'notes' => 'Quarterly subscription paid via ABA KHQR at the library counter.',
            'recorded_by' => $admin->id,
        ]);

        // Kosal Heng (Expired Subscription)
        MembershipPayment::create([
            'user_id' => $users[6]->id,
            'plan_type' => 'Monthly',
            'amount' => 5.00,
            'payment_method' => 'Cash',
            'reference_no' => 'MEM-2026-002',
            'payment_date' => $now->copy()->subDays(35)->toDateString(),
            'start_date' => $now->copy()->subDays(35)->toDateString(),
            'expires_at' => $now->copy()->subDays(5),
            'status' => 'Expired',
            'notes' => 'Monthly subscription paid in cash. Currently expired and needs renewal.',
            'recorded_by' => $admin->id,
        ]);

        // 7. Manager and Initial Activity Logs
        require_once __DIR__ . '/ManagerAndLogsSeeder.php';
        (new ManagerAndLogsSeeder)->run();

        // 8. Student Patrons (20 Enrolled University Students)
        require_once __DIR__ . '/StudentImportSeeder.php';
        (new StudentImportSeeder)->run();

        // 9. University Academic Books and Disciplines Showcase
        (new UniversityBooksSeeder)->run();
    }
}

