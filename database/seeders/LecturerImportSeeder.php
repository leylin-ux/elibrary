<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LecturerImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maleAvatars = [
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1537368910025-700350fe46c7?w=150&auto=format&fit=crop&q=80',
        ];

        $femaleAvatars = [
            'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1580894732444-8ecded7900cd?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1573496799652-408c2ac9fe98?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1567532939604-b6b5b0db2604?w=150&auto=format&fit=crop&q=80',
        ];

        $lecturers = [
            [
                'card_id' => 'LEC-IT-001',
                'name' => 'បណ្ឌិត អ៊ឹម សុភា',
                'gender' => 'ប្រុស',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'specialization' => 'Artificial Intelligence & Data Mining',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#01',
            ],
            [
                'card_id' => 'LEC-IT-002',
                'name' => 'សាស្ត្រាចារ្យ កែវ វិបុល',
                'gender' => 'ប្រុស',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'specialization' => 'Software Architecture & Web Dev',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#02',
            ],
            [
                'card_id' => 'LEC-IT-003',
                'name' => 'អនុបណ្ឌិត សិន ស្រីនិច',
                'gender' => 'ស្រី',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'specialization' => 'Network Security & Cloud Infrastructure',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#03',
            ],
            [
                'card_id' => 'LEC-ENG-001',
                'name' => 'បណ្ឌិត ហួត ចាន់រិទ្ធ',
                'gender' => 'ប្រុស',
                'faculty' => 'វិស្វកម្ម',
                'specialization' => 'Structural Analysis & Materials',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#04',
            ],
            [
                'card_id' => 'LEC-ENG-002',
                'name' => 'អនុបណ្ឌិត លឹម គីមសួរ',
                'gender' => 'ប្រុស',
                'faculty' => 'វិស្វកម្ម',
                'specialization' => 'Electrical Circuits & Power Systems',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#05',
            ],
            [
                'card_id' => 'LEC-ENG-003',
                'name' => 'អនុបណ្ឌិត ទៀង វណ្ណារី',
                'gender' => 'ស្រី',
                'faculty' => 'វិស្វកម្ម',
                'specialization' => 'Thermodynamics & Fluid Mechanics',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#06',
            ],
            [
                'card_id' => 'LEC-ECO-001',
                'name' => 'បណ្ឌិត សុខ សុវណ្ណារ៉ា',
                'gender' => 'ប្រុស',
                'faculty' => 'សេដ្ឋកិច្ច',
                'specialization' => 'Macroeconomics & Monetary Policy',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#07',
            ],
            [
                'card_id' => 'LEC-ECO-002',
                'name' => 'អនុបណ្ឌិត គិន សុជាលីស',
                'gender' => 'ស្រី',
                'faculty' => 'សេដ្ឋកិច្ច',
                'specialization' => 'Financial Markets & Risk Management',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#08',
            ],
            [
                'card_id' => 'LEC-ECO-003',
                'name' => 'អនុបណ្ឌិត ម៉ម សុភ័ក្ត្រ',
                'gender' => 'ប្រុស',
                'faculty' => 'សេដ្ឋកិច្ច',
                'specialization' => 'Auditing & Corporate Accounting',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#09',
            ],
            [
                'card_id' => 'LEC-MGT-001',
                'name' => 'បណ្ឌិត ឈាង រតនា',
                'gender' => 'ស្រី',
                'faculty' => 'គ្រប់គ្រង',
                'specialization' => 'Strategic Management & Leadership',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#10',
            ],
            [
                'card_id' => 'LEC-MGT-002',
                'name' => 'អនុបណ្ឌិត អ៊ុច ទ្រី',
                'gender' => 'ប្រុស',
                'faculty' => 'គ្រប់គ្រង',
                'specialization' => 'Digital Marketing & Consumer Behavior',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#11',
            ],
            [
                'card_id' => 'LEC-LAN-001',
                'name' => 'បណ្ឌិត John Miller',
                'gender' => 'ប្រុស',
                'faculty' => 'ភាសាបរទេស',
                'specialization' => 'Academic English & Applied Linguistics',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#12',
            ],
            [
                'card_id' => 'LEC-LAN-002',
                'name' => 'អនុបណ្ឌិត ចេង ស៊ីវឡេង',
                'gender' => 'ស្រី',
                'faculty' => 'ភាសាបរទេស',
                'specialization' => 'Business Chinese & Translation',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#13',
            ],
            [
                'card_id' => 'LEC-LAW-001',
                'name' => 'បណ្ឌិត ប៉ែន សម្បត្តិ',
                'gender' => 'ប្រុស',
                'faculty' => 'នីតិសាស្ត្រ',
                'specialization' => 'Commercial Law & Intellectual Property',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#14',
            ],
            [
                'card_id' => 'LEC-LAW-002',
                'name' => 'អនុបណ្ឌិត រ៉ាំ សុម៉ាលី',
                'gender' => 'ស្រី',
                'faculty' => 'នីតិសាស្ត្រ',
                'specialization' => 'Constitutional & Administrative Law',
                'privilege' => 'VIP (ខ្ចីបាន ១០ ក្បាល / ៣០ ថ្ងៃ)',
                'password' => 'Lec@2026#15',
            ],
        ];

        $mIdx = 0;
        $fIdx = 0;

        foreach ($lecturers as $data) {
            $emailCode = strtolower(str_replace('-', '', $data['card_id']));
            $email = $emailCode . '@lecturer.edu.kh';

            if ($data['gender'] === 'ស្រី') {
                $photo = $femaleAvatars[$fIdx % count($femaleAvatars)];
                $fIdx++;
            } else {
                $photo = $maleAvatars[$mIdx % count($maleAvatars)];
                $mIdx++;
            }

            User::updateOrCreate(
                ['card_id' => $data['card_id']],
                [
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make($data['password']),
                    'member_type' => 'Teacher',
                    'academic_year' => null,
                    'major' => $data['specialization'] . ' (' . $data['faculty'] . ')',
                    'status' => 'Active',
                    'role' => 'member',
                    'photo' => $photo,
                    'phone' => '0' . rand(11, 98) . ' ' . rand(200, 899) . ' ' . rand(100, 999),
                    'notes' => 'មហាវិទ្យាល័យ: ' . $data['faculty'] . ' | មុខវិជ្ជាបង្រៀន: ' . $data['specialization'] . ' | ភេទ: ' . $data['gender'] . ' | កម្រិតសិទ្ធិ: ' . $data['privilege'] . ' | DefaultPwd: ' . $data['password'],
                    'last_login_at' => null,
                    'first_login_at' => null,
                ]
            );
        }
    }
}
