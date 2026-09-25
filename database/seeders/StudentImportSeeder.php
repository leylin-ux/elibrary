<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maleAvatars = [
            'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150&auto=format&fit=crop&q=80',
        ];

        $femaleAvatars = [
            'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&auto=format&fit=crop&q=80',
            'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
        ];

        $students = [
            [
                'card_id' => 'ST-2023-001',
                'name' => 'ចាន់ សុខា',
                'gender' => 'ប្រុស',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Software Engineering',
                'entry_year' => 2023,
                'academic_year' => 4,
                'password' => 'Stu@2026#01',
            ],
            [
                'card_id' => 'ST-2023-002',
                'name' => 'កែវ ធីតា',
                'gender' => 'ស្រី',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Cybersecurity',
                'entry_year' => 2023,
                'academic_year' => 4,
                'password' => 'Stu@2026#02',
            ],
            [
                'card_id' => 'ST-2023-003',
                'name' => 'ឡុង វិបុល',
                'gender' => 'ប្រុស',
                'faculty' => 'វិស្វកម្ម',
                'major' => 'Civil Engineering',
                'entry_year' => 2023,
                'academic_year' => 4,
                'password' => 'Stu@2026#03',
            ],
            [
                'card_id' => 'ST-2023-004',
                'name' => 'ម៉ៅ សុផល',
                'gender' => 'ស្រី',
                'faculty' => 'សេដ្ឋកិច្ច',
                'major' => 'Banking & Finance',
                'entry_year' => 2023,
                'academic_year' => 4,
                'password' => 'Stu@2026#04',
            ],
            [
                'card_id' => 'ST-2023-005',
                'name' => 'ហេង ពិសិដ្ឋ',
                'gender' => 'ប្រុស',
                'faculty' => 'គ្រប់គ្រង',
                'major' => 'Marketing',
                'entry_year' => 2023,
                'academic_year' => 4,
                'password' => 'Stu@2026#05',
            ],
            [
                'card_id' => 'ST-2024-001',
                'name' => 'សេង ដានី',
                'gender' => 'ស្រី',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Data Science',
                'entry_year' => 2024,
                'academic_year' => 3,
                'password' => 'Stu@2026#06',
            ],
            [
                'card_id' => 'ST-2024-002',
                'name' => 'អ៊ុក សំណាង',
                'gender' => 'ប្រុស',
                'faculty' => 'វិស្វកម្ម',
                'major' => 'Electrical Engineering',
                'entry_year' => 2024,
                'academic_year' => 3,
                'password' => 'Stu@2026#07',
            ],
            [
                'card_id' => 'ST-2024-003',
                'name' => 'ជា ស្រីមុំ',
                'gender' => 'ស្រី',
                'faculty' => 'សេដ្ឋកិច្ច',
                'major' => 'Accounting',
                'entry_year' => 2024,
                'academic_year' => 3,
                'password' => 'Stu@2026#08',
            ],
            [
                'card_id' => 'ST-2024-004',
                'name' => 'ឃឹម ចំរើន',
                'gender' => 'ប្រុស',
                'faculty' => 'ភាសាបរទេស',
                'major' => 'English Literature',
                'entry_year' => 2024,
                'academic_year' => 3,
                'password' => 'Stu@2026#09',
            ],
            [
                'card_id' => 'ST-2024-005',
                'name' => 'វ៉ាន់ វណ្ណា',
                'gender' => 'ប្រុស',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Network Engineering',
                'entry_year' => 2024,
                'academic_year' => 3,
                'password' => 'Stu@2026#10',
            ],
            [
                'card_id' => 'ST-2025-001',
                'name' => 'ព្រំ សុជាតា',
                'gender' => 'ស្រី',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Artificial Intelligence',
                'entry_year' => 2025,
                'academic_year' => 2,
                'password' => 'Stu@2026#11',
            ],
            [
                'card_id' => 'ST-2025-002',
                'name' => 'តាំង គីមហុង',
                'gender' => 'ប្រុស',
                'faculty' => 'វិស្វកម្ម',
                'major' => 'Mechanical Engineering',
                'entry_year' => 2025,
                'academic_year' => 2,
                'password' => 'Stu@2026#12',
            ],
            [
                'card_id' => 'ST-2025-003',
                'name' => 'លីន ម៉ានី',
                'gender' => 'ស្រី',
                'faculty' => 'គ្រប់គ្រង',
                'major' => 'International Business',
                'entry_year' => 2025,
                'academic_year' => 2,
                'password' => 'Stu@2026#13',
            ],
            [
                'card_id' => 'ST-2025-004',
                'name' => 'ស៊ឹម វឌ្ឍនា',
                'gender' => 'ប្រុស',
                'faculty' => 'សេដ្ឋកិច្ច',
                'major' => 'Economics',
                'entry_year' => 2025,
                'academic_year' => 2,
                'password' => 'Stu@2026#14',
            ],
            [
                'card_id' => 'ST-2025-005',
                'name' => 'ឈិត មុន្នី',
                'gender' => 'ស្រី',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Cloud Computing',
                'entry_year' => 2025,
                'academic_year' => 2,
                'password' => 'Stu@2026#15',
            ],
            [
                'card_id' => 'ST-2026-001',
                'name' => 'នុត សុភ័ក្ត្រ',
                'gender' => 'ប្រុស',
                'faculty' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'major' => 'Software Engineering',
                'entry_year' => 2026,
                'academic_year' => 1,
                'password' => 'Stu@2026#16',
            ],
            [
                'card_id' => 'ST-2026-002',
                'name' => 'អ៊ឹង ម៉ាលីស',
                'gender' => 'ស្រី',
                'faculty' => 'វិស្វកម្ម',
                'major' => 'Civil Engineering',
                'entry_year' => 2026,
                'academic_year' => 1,
                'password' => 'Stu@2026#17',
            ],
            [
                'card_id' => 'ST-2026-003',
                'name' => 'យុន រ៉ាវី',
                'gender' => 'ប្រុស',
                'faculty' => 'គ្រប់គ្រង',
                'major' => 'General Management',
                'entry_year' => 2026,
                'academic_year' => 1,
                'password' => 'Stu@2026#18',
            ],
            [
                'card_id' => 'ST-2026-004',
                'name' => 'ឡៀង សុវណ្ណ',
                'gender' => 'ប្រុស',
                'faculty' => 'សេដ្ឋកិច្ច',
                'major' => 'Finance & Banking',
                'entry_year' => 2026,
                'academic_year' => 1,
                'password' => 'Stu@2026#19',
            ],
            [
                'card_id' => 'ST-2026-005',
                'name' => 'ម៉ែន ចរិយា',
                'gender' => 'ស្រី',
                'faculty' => 'ភាសាបរទេស',
                'major' => 'Chinese Language',
                'entry_year' => 2026,
                'academic_year' => 1,
                'password' => 'Stu@2026#20',
            ],
        ];

        $mCount = 0;
        $fCount = 0;

        foreach ($students as $data) {
            $email = strtolower(str_replace('-', '', $data['card_id'])) . '@student.edu.kh';
            
            if ($data['gender'] === 'ស្រី') {
                $photo = $femaleAvatars[$fCount % count($femaleAvatars)];
                $fCount++;
            } else {
                $photo = $maleAvatars[$mCount % count($maleAvatars)];
                $mCount++;
            }

            User::updateOrCreate(
                ['card_id' => $data['card_id']],
                [
                    'name' => $data['name'],
                    'email' => $email,
                    'password' => Hash::make($data['password']),
                    'member_type' => 'Student',
                    'academic_year' => $data['academic_year'],
                    'major' => $data['major'] . ' (' . $data['faculty'] . ')',
                    'status' => 'Active',
                    'role' => 'member',
                    'photo' => $photo,
                    'phone' => '0' . rand(10, 99) . ' ' . rand(100, 999) . ' ' . rand(100, 999),
                    'notes' => 'មហាវិទ្យាល័យ: ' . $data['faculty'] . ' | ជំនាញ: ' . $data['major'] . ' | ឆ្នាំចូលរៀន: ' . $data['entry_year'] . ' | ភេទ: ' . $data['gender'] . ' | DefaultPwd: ' . $data['password'],
                ]
            );
        }
    }
}
