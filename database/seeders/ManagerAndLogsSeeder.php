<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Reservation;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerAndLogsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@elibrary.com'],
            [
                'name' => 'Sokha Chan (Super Admin)',
                'card_id' => 'LIB-ADM-001',
                'member_type' => 'Staff',
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
                'phone' => '012 889 900',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->update(['role' => 'admin']);

        // 2. Manager (ប្រធានគ្រប់គ្រងបណ្ណាល័យ)
        $manager = User::firstOrCreate(
            ['email' => 'manager@elibrary.com'],
            [
                'name' => 'Dara Vichea (ប្រធានបណ្ណាល័យ)',
                'card_id' => 'LIB-MGR-001',
                'member_type' => 'Staff',
                'status' => 'Active',
                'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
                'phone' => '012 554 433',
                'role' => 'manager',
                'password' => Hash::make('password'),
            ]
        );
        $manager->update(['role' => 'manager']);

        // 3. Demo Member
        $member = User::where('role', 'member')->first();
        if (!$member) {
            $member = User::firstOrCreate(
                ['email' => 'student@elibrary.com'],
                [
                    'name' => 'Vannak Keo (សមាជិក)',
                    'card_id' => 'LIB-STU-001',
                    'member_type' => 'Student',
                    'status' => 'Active',
                    'photo' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=150&auto=format&fit=crop&q=80',
                    'phone' => '098 776 543',
                    'role' => 'member',
                    'password' => Hash::make('password'),
                ]
            );
        }

        // 4. Default Library Policy Settings
        Setting::set('standard_loan_days', '14', 'system');
        Setting::set('daily_fine_rate_khr', '500', 'system');
        Setting::set('daily_fine_rate', '0.125', 'system'); // ~500 KHR in USD
        Setting::set('max_books_student', '3', 'system');
        Setting::set('max_books_teacher', '5', 'system');
        Setting::set('max_books_general', '3', 'system');

        // 5. Activity Logs for Manager & System
        $now = Carbon::now();
        $logs = [
            [
                'user_id' => $manager->id,
                'action' => 'borrow_issued',
                'description' => 'បានកត់ត្រាឱ្យសមាជិក Vannak Keo ខ្ចីសៀវភៅ "Clean Code" (កាលបរិច្ឆេទសង: ' . $now->copy()->addDays(14)->format('d/m/Y') . ')',
                'subject_type' => Borrow::class,
                'subject_id' => Borrow::first()?->id,
                'created_at' => $now->copy()->subHours(2),
            ],
            [
                'user_id' => $manager->id,
                'action' => 'fine_waived',
                'description' => 'បានសម្រេចលើកលែងប្រាក់ពិន័យយឺតចំនួន $2.00 (៨,០០០ ៛) ជូនសមាជិក Sreymom Pich (មូលហេតុ៖ សិស្សមានធុរៈជំងឺ មានលិខិតបញ្ជាក់ត្រឹមត្រូវ)',
                'subject_type' => Borrow::class,
                'subject_id' => Borrow::where('status', 'Overdue')->first()?->id,
                'created_at' => $now->copy()->subHours(5),
            ],
            [
                'user_id' => $manager->id,
                'action' => 'reservation_approved',
                'description' => 'បានអនុម័តការកក់សៀវភៅ "Zero to One" សម្រាប់ Dr. Rithy Seng (កំណត់យកមុន ៤៨ ម៉ោង)',
                'subject_type' => Reservation::class,
                'subject_id' => Reservation::first()?->id,
                'created_at' => $now->copy()->subHours(7),
            ],
            [
                'user_id' => $admin->id,
                'action' => 'book_added',
                'description' => 'បានបន្ថែមសៀវភៅថ្មី "Clean Code" ចូលក្នុងប្រព័ន្ធបណ្ណាល័យ',
                'subject_type' => Book::class,
                'subject_id' => Book::first()?->id,
                'created_at' => $now->copy()->subDays(1)->subHours(3),
            ],
            [
                'user_id' => $manager->id,
                'action' => 'book_returned',
                'description' => 'បានទទួលសៀវភៅ "The Great Gatsby" សងវិញពីសមាជិក Vannak Keo (ស្ថានភាពល្អ Good)',
                'subject_type' => Borrow::class,
                'subject_id' => Borrow::where('status', 'Returned')->first()?->id,
                'created_at' => $now->copy()->subDays(1)->subHours(6),
            ],
            [
                'user_id' => $manager->id,
                'action' => 'policy_updated',
                'description' => 'បានកែសម្រួលគោលការណ៍បណ្ណាល័យ៖ កំណត់អត្រាប្រាក់ពិន័យ ៥០០ ៛/ថ្ងៃ និងកូតាខ្ចី ៣ ក្បាលក្នុងពេលតែមួយ',
                'subject_type' => Setting::class,
                'subject_id' => null,
                'created_at' => $now->copy()->subDays(2),
            ],
        ];

        foreach ($logs as $logData) {
            ActivityLog::create($logData);
        }
    }
}
