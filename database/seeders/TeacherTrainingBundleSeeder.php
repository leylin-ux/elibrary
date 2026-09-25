<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class TeacherTrainingBundleSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'literature')->orWhere('slug', 'general-works')->first() 
                    ?? Category::first();

        $books = [
            [
                'title' => 'ស្តង់ដាសាលាបឋមសិក្សា',
                'author' => 'នាយកដ្ឋានបឋមសិក្សា',
                'isbn' => '978-99950-01-01-1',
                'category_id' => $category?->id ?? 1,
                'subcategory' => 'គរុកោសល្យ',
                'education_level' => 'បឋមសិក្សា',
                'subject' => 'គរុកោសល្យ',
                'grade' => 'ទូទៅ',
                'cover_image' => '/uploads/covers/bundle_tt1.jpg',
                'total_copies' => 10,
                'available_copies' => 8,
                'location_shelf' => 'Shelf-TT-01',
                'published_year' => 2021,
                'description' => 'ស្តង់ដាសាលារៀនបឋមសិក្សា សម្រាប់ការបណ្តុះបណ្តាល និងពង្រឹងគុណភាពអប់រំ។',
                'views_count' => 0,
                'downloads_count' => 0,
                'is_featured' => true,
            ],
            [
                'title' => 'សេចក្តីណែនាំអនុវត្តការធ្វើតេស្តស្តង់ដាកម្រិតសាលារៀន',
                'author' => 'សុខ សូត្រ និង កែវ សាវ៉ាត់',
                'isbn' => '978-99950-01-02-8',
                'category_id' => $category?->id ?? 1,
                'subcategory' => 'គរុកោសល្យ',
                'education_level' => 'បឋមសិក្សា',
                'subject' => 'គរុកោសល្យ',
                'grade' => 'ទូទៅ',
                'cover_image' => '/uploads/covers/bundle_tt2.jpg',
                'total_copies' => 8,
                'available_copies' => 6,
                'location_shelf' => 'Shelf-TT-02',
                'published_year' => 2022,
                'description' => 'សេចក្តីណែនាំស្តីពីការអនុវត្តការធ្វើតេស្តស្តង់ដានៅតាមគ្រឹះស្ថានបឋមសិក្សា។',
                'views_count' => 0,
                'downloads_count' => 0,
                'is_featured' => true,
            ],
            [
                'title' => 'ស្តង់ដាសាលាមធ្យមសិក្សា',
                'author' => 'នាយកដ្ឋានមធ្យមសិក្សាចំណេះទូទៅ',
                'isbn' => '978-99950-01-03-5',
                'category_id' => $category?->id ?? 1,
                'subcategory' => 'គរុកោសល្យ',
                'education_level' => 'អនុវិទ្យាល័យ',
                'subject' => 'គរុកោសល្យ',
                'grade' => 'ទូទៅ',
                'cover_image' => '/uploads/covers/bundle_tt3.jpg',
                'total_copies' => 12,
                'available_copies' => 9,
                'location_shelf' => 'Shelf-TT-03',
                'published_year' => 2021,
                'description' => 'ស្តង់ដាសាលារៀនមធ្យមសិក្សាចំណេះទូទៅ សម្រាប់ការអភិវឌ្ឍសមត្ថភាពគរុនិស្សិត និងគ្រូបង្រៀន។',
                'views_count' => 0,
                'downloads_count' => 0,
                'is_featured' => true,
            ],
            [
                'title' => 'សៀវភៅ កុំព្យូទ័ររដ្ឋបាល',
                'author' => '២០២៣',
                'isbn' => '978-99950-01-04-2',
                'category_id' => Category::where('slug', 'technology')->value('id') ?? ($category?->id ?? 1),
                'subcategory' => 'ព័ត៌មានវិទ្យា',
                'education_level' => 'វិទ្យាល័យ',
                'subject' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ',
                'grade' => 'ទូទៅ',
                'cover_image' => '/uploads/covers/bundle_tt4.jpg',
                'total_copies' => 15,
                'available_copies' => 12,
                'location_shelf' => 'Shelf-IT-01',
                'published_year' => 2023,
                'description' => 'សៀវភៅជំនាញកុំព្យូទ័ររដ្ឋបាល (Word, Excel, PowerPoint, G Suite) វិទ្យាល័យ ពេជ្រចិន្តា។',
                'views_count' => 0,
                'downloads_count' => 0,
                'is_featured' => true,
            ],
        ];

        foreach ($books as $data) {
            Book::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
