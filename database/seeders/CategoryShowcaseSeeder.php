<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoryShowcaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories matching the reference design in user screenshot
        $categoriesData = [
            [
                'name' => 'អក្សរសិល្ប៍',
                'slug' => 'literature',
                'icon' => 'book-open',
                'description' => 'អក្សរសិល្ប៍ខ្មែរ និងប្រលោមលោកបុរាណ-សម័យ',
            ],
            [
                'name' => 'បច្ចេកវិទ្យា',
                'slug' => 'technology',
                'icon' => 'code',
                'description' => 'វិទ្យាសាស្ត្រកុំព្យូទ័រ បច្ចេកវិទ្យាព័ត៌មាន AI និងសូហ្វវែរ',
            ],
            [
                'name' => 'ស្នាដៃទូទៅ',
                'slug' => 'general-works',
                'icon' => 'sparkles',
                'description' => 'កម្រងស្នាដៃស្រាវជ្រាវ ឯកសារបោះពុម្ពផ្សាយ និងស្នាដៃទូទៅ',
            ],
            [
                'name' => 'ភូមិវិទ្យា និងប្រវត្តិវិទ្យា',
                'slug' => 'geography-history',
                'icon' => 'globe',
                'description' => 'ប្រវត្តិសាស្ត្រខ្មែរ វប្បធម៌ និងភូមិវិទ្យា',
            ],
            [
                'name' => 'ទស្សនវិជ្ជា និងចិត្តវិទ្យា',
                'slug' => 'philosophy-psychology',
                'icon' => 'brain',
                'description' => 'ទស្សនវិជ្ជា ចិត្តវិទ្យា និងការអភិវឌ្ឍផ្នត់គំនិត',
            ],
            [
                'name' => 'ភាសា',
                'slug' => 'languages',
                'icon' => 'translate',
                'description' => 'វេយ្យាករណ៍ភាសាខ្មែរ ភាសាអង់គ្លេស បារាំង ចិន និងភាសាបរទេស',
            ],
            [
                'name' => 'សាសនា',
                'slug' => 'religion',
                'icon' => 'sparkles',
                'description' => 'សាសនា ព្រះពុទ្ធសាសនា និងទស្សនវិជ្ជាសាសនា',
            ],
            [
                'name' => 'ពាណិជ្ជកម្ម និងគ្រប់គ្រង',
                'slug' => 'business-management',
                'icon' => 'briefcase',
                'description' => 'សេដ្ឋកិច្ច ហិរញ្ញវត្ថុ និងការគ្រប់គ្រងអាជីវកម្ម',
            ],
            [
                'name' => 'វិទ្យាសាស្ត្រ និងគណិតវិទ្យា',
                'slug' => 'science-math',
                'icon' => 'chart-bar',
                'description' => 'គណិតវិទ្យា រូបវិទ្យា គីមីវិទ្យា និងជីវវិទ្យា',
            ],
        ];

        $categoriesMap = [];
        foreach ($categoriesData as $c) {
            $cat = Category::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name' => $c['name'],
                    'icon' => $c['icon'],
                    'description' => $c['description'],
                ]
            );
            $categoriesMap[$c['slug']] = $cat;
        }

        // 2. Sample books under 'ភាសា' (Languages) from the user screenshot
        $langCategory = $categoriesMap['languages'] ?? Category::first();

        $booksData = [
            [
                'title' => 'ភាសាអង់គ្លេស',
                'author' => 'ក្រសួងអប់រំ យុវជន និងកីឡា',
                'isbn' => '978-99950-01-01',
                'category_id' => $langCategory->id,
                'subcategory' => 'ភាសាបរទេស',
                'education_level' => 'ឧត្តមសិក្សា',
                'subject' => 'ភាសាអង់គ្លេស',
                'grade' => 'ឆ្នាំទី ១',
                'total_copies' => 12,
                'available_copies' => 10,
                'location_shelf' => 'A-101',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => '/uploads/covers/khmer_english.jpg',
                'description' => 'សៀវភៅសិក្សាភាសាអង់គ្លេស រៀបចំដោយក្រសួងអប់រំ យុវជន និងកីឡា សម្រាប់ពង្រឹងសមត្ថភាពភាសាអង់គ្លេស និងការប្រាស្រ័យទាក់ទង។',
                'published_year' => 2024,
                'target_academic_year' => 1,
            ],
            [
                'title' => 'ភាសាបារាំង ជីវភាសាទី២',
                'author' => 'ក្រសួងអប់រំ យុវជន និងកីឡា',
                'isbn' => '978-99950-01-02',
                'category_id' => $langCategory->id,
                'subcategory' => 'ភាសាបរទេស',
                'education_level' => 'ឧត្តមសិក្សា',
                'subject' => 'ភាសាបារាំង',
                'grade' => 'ឆ្នាំទី ១',
                'total_copies' => 10,
                'available_copies' => 8,
                'location_shelf' => 'A-102',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => '/uploads/covers/khmer_french_2.jpg',
                'description' => 'សៀវភៅសិក្សាគោលភាសាបារាំង ជាភាសាទី២ សម្រាប់បណ្ដុះបណ្ដាលមូលដ្ឋានគ្រឹះ និងវេយ្យាករណ៍បារាំង។',
                'published_year' => 2024,
                'target_academic_year' => 1,
            ],
            [
                'title' => 'ភាសាបារាំង',
                'author' => 'ក្រសួងអប់រំ យុវជន និងកីឡា',
                'isbn' => '978-99950-01-03',
                'category_id' => $langCategory->id,
                'subcategory' => 'ភាសាបរទេស',
                'education_level' => 'ឧត្តមសិក្សា',
                'subject' => 'ភាសាបារាំង',
                'grade' => 'ឆ្នាំទី ២',
                'total_copies' => 8,
                'available_copies' => 6,
                'location_shelf' => 'A-103',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => '/uploads/covers/khmer_french.jpg',
                'description' => 'កម្មវិធីសិក្សាភាសាបារាំងស្តង់ដារ សម្រាប់ការសិក្សាស្រាវជ្រាវ និងការប្រាស្រ័យទាក់ទង។',
                'published_year' => 2024,
                'target_academic_year' => 2,
            ],
            [
                'title' => 'សៀវភៅវេយ្យាករណ៍ភាសាខ្មែរ',
                'author' => 'ទីស្តីការគណៈរដ្ឋមន្ត្រី ក្រុមប្រឹក្សាជាតិភាសាខ្មែរ',
                'isbn' => '978-99950-01-04',
                'category_id' => $langCategory->id,
                'subcategory' => 'ភាសាខ្មែរ',
                'education_level' => 'ទូទៅ',
                'subject' => 'ភាសាខ្មែរ',
                'grade' => 'គ្រប់កម្រិត',
                'total_copies' => 15,
                'available_copies' => 14,
                'location_shelf' => 'B-201',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => '/uploads/covers/khmer_grammar.jpg',
                'description' => 'សៀវភៅវេយ្យាករណ៍ភាសាខ្មែរ រៀបចំ និងចងក្រងដោយក្រុមប្រឹក្សាជាតិភាសាខ្មែរ នៃទីស្តីការគណៈរដ្ឋមន្ត្រី ជាឯកសារយោងស្តង់ដារ។',
                'published_year' => 2024,
                'target_academic_year' => 1,
            ],
            [
                'title' => 'ការអភិវឌ្ឍកុមារឲ្យរភ្ជាប់នឹងការអប់រំបឋមសិក្សា ថ្នាក់ទី១_៣',
                'author' => 'នាយកដ្ឋានបឋម',
                'isbn' => '978-99950-01-05',
                'category_id' => $langCategory->id,
                'subcategory' => 'គរុកោសល្យ',
                'education_level' => 'បឋមសិក្សា',
                'subject' => 'គរុកោសល្យ',
                'grade' => 'ថ្នាក់ទី១',
                'total_copies' => 20,
                'available_copies' => 18,
                'location_shelf' => 'C-301',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => '/uploads/covers/khmer_primary_edu.jpg',
                'description' => 'សៀវភៅណែនាំគរុកោសល្យ ស្តីពីការអភិវឌ្ឍកុមារភ្ជាប់នឹងការអប់រំបឋមសិក្សា សម្រាប់គ្រូបង្រៀន និងអ្នកអប់រំ។',
                'published_year' => 2024,
                'target_academic_year' => 2,
            ],
        ];

        // Also add religion category books if category exists
        $religionCat = $categoriesMap['religion'] ?? Category::where('slug', 'religion')->first();
        if ($religionCat) {
            $booksData[] = [
                'title' => 'ព្រះពុទ្ធសាសនា និងសង្គមខ្មែរ',
                'author' => 'ពុទ្ធសាសនបណ្ឌិត្យ',
                'isbn' => '978-99950-02-01',
                'category_id' => $religionCat->id,
                'subcategory' => 'ទស្សនវិជ្ជាសាសនា',
                'education_level' => 'ទូទៅ',
                'subject' => 'សាសនា',
                'grade' => 'គ្រប់កម្រិត',
                'total_copies' => 10,
                'available_copies' => 9,
                'location_shelf' => 'R-101',
                'views_count' => 0,
                'downloads_count' => 0,
                'cover_image' => 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400&auto=format&fit=crop&q=80',
                'description' => 'ការសិក្សាស្រាវជ្រាវអំពីឥទ្ធិពលនៃពុទ្ធសាសនាលើវប្បធម៌ និងការរស់នៅរបស់សង្គមខ្មែរ។',
                'published_year' => 2023,
                'target_academic_year' => 1,
            ];
        }

        foreach ($booksData as $b) {
            Book::updateOrCreate(
                ['title' => $b['title']],
                $b
            );
        }
    }
}
