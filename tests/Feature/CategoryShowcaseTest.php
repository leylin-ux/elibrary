<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategoryShowcaseSeeder;
use Tests\TestCase;

class CategoryShowcaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');
        $this->seed(CategoryShowcaseSeeder::class);
    }

    public function test_category_pills_and_book_showcase_match_screenshot_design(): void
    {
        $user = User::first() ?? User::factory()->create(['role' => 'Student']);

        $langCat = Category::where('slug', 'languages')->first();
        $this->assertNotNull($langCat);

        $response = $this->actingAs($user)->get(route('books.index', ['category_id' => $langCat->id]));

        $response->assertStatus(200);

        // 1. Verify Category Navigation Pills
        $response->assertSee('ទាំងអស់');
        $response->assertSee('អក្សរសិល្ប៍');
        $response->assertSee('បច្ចេកវិទ្យា');
        $response->assertSee('ស្នាដៃទូទៅ');
        $response->assertSee('ភូមិវិទ្យា និងប្រវត្តិវិទ្យា');
        $response->assertSee('ទស្សនវិជ្ជា និងចិត្តវិទ្យា');
        $response->assertSee('ភាសា');

        // 2. Verify Right Controls: Sort & Filter
        $response->assertSee('ថ្មីៗ');
        $response->assertSee('តម្រង');

        // 3. Verify the 5 Books in the Language Category
        $response->assertSee('ភាសាអង់គ្លេស');
        $response->assertSee('ភាសាបារាំង ជីវភាសាទី២');
        $response->assertSee('ភាសាបារាំង');
        $response->assertSee('សៀវភៅវេយ្យាករណ៍ភាសាខ្មែរ');
        $response->assertSee('ការអភិវឌ្ឍកុមារឲ្យរភ្ជាប់នឹងការអប់រំបឋមសិក្សា ថ្នាក់ទី១_៣');

        // 4. Verify Stats (Views & Downloads)
        $response->assertSee('81,709');
        $response->assertSee('230');
    }

    public function test_slideover_filter_drawer_contains_five_fields_and_actions(): void
    {
        $user = User::first() ?? User::factory()->create(['role' => 'Student']);

        $response = $this->actingAs($user)->get(route('books.index'));

        $response->assertStatus(200);

        // 1. Filter Drawer Title & Subtitle
        $response->assertSee('តម្រង');
        $response->assertSee('រួមបញ្ចូលគ្នាដើម្បីបង្រួមលទ្ធផល');

        // 2. The 5 Filter Fields (Exact match with user screenshot)
        $response->assertSee('ប្រភេទ');
        $response->assertSee('ប្រភេទរង');
        $response->assertSee('កម្រិតអប់រំ');
        $response->assertSee('មុខវិជ្ជា');
        $response->assertSee('ថ្នាក់');

        // 3. Action Buttons
        $response->assertSee('សម្អាតតម្រង');
        $response->assertSee('អនុវត្ត');

        // 4. Test Filtering by Subcategory, Education Level, and Subject
        $filterResponse = $this->actingAs($user)->get(route('books.index', ['subcategory' => 'ភាសាបរទេស', 'education_level' => 'ឧត្តមសិក្សា', 'subject' => 'ភាសាបារាំង']));
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('ភាសាបារាំង');
    }

    public function test_book_bundles_page_matches_screenshot_design(): void
    {
        $user = User::first() ?? User::factory()->create(['role' => 'Student']);

        // 1. Check index page link to bundles
        $indexResponse = $this->actingAs($user)->get(route('books.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee(route('books.bundles'));
        $indexResponse->assertSee('បណ្តុំសៀវភៅ');
        $indexResponse->assertSee('មើលទាំងអស់');

        // 2. Check bundles page
        $response = $this->actingAs($user)->get(route('books.bundles'));
        $response->assertStatus(200);

        // Verify breadcrumb & header
        $response->assertSee('បណ្ណាល័យ');
        $response->assertSee('បណ្តុំសៀវភៅ');
        $response->assertSee('សៀវភៅដែលបានរៀបជាក្រុមតាមថ្នាក់ និងតាមមុខវិជ្ជា ដើម្បីងាយស្រួលស្វែងរក។');
        $response->assertSee('ត្រឡប់ទៅបណ្ណាល័យ');

        // Verify key bundles shown in screenshot
        $response->assertSee('Teacher training');
        $response->assertSee('សៀវភៅ 4 ក្បាល');
        $response->assertSee('គណិត១០');
        $response->assertSee('សៀវភៅ 1 ក្បាល');
        $response->assertSee('IT');
        $response->assertSee('កិច្ចការផ្ទះ');
        $response->assertSee('សៀវភៅថ្នាក់ទី៣');
        $response->assertSee('សៀវភៅ 10 ក្បាល');
        $response->assertSee('English');
    }

    public function test_teacher_training_bundle_show_page_matches_screenshot(): void
    {
        $user = User::first() ?? User::factory()->create(['role' => 'Student']);

        // 1. Visit Teacher Training bundle page
        $response = $this->actingAs($user)->get(route('books.bundles.show', 'teacher-training'));
        $response->assertStatus(200);

        // 2. Verify Breadcrumb & Header
        $response->assertSee('បណ្ណាល័យ');
        $response->assertSee('បណ្តុំសៀវភៅ');
        $response->assertSee('Teacher training');
        $response->assertSee('សៀវភៅ 4 ក្បាល');
        $response->assertSee('ត្រឡប់ទៅបណ្តុំសៀវភៅ');

        // 3. Verify Book 1
        $response->assertSee('ស្តង់ដាសាលាបឋមសិក្សា');
        $response->assertSee('នាយកដ្ឋានបឋមសិក្សា');
        $response->assertSee('68,343');
        $response->assertSee('2,207');

        // 4. Verify Book 2
        $response->assertSee('សេចក្តីណែនាំអនុវត្តការធ្វើតេស្តស្តង់ដាកម្រិតសាលារៀន');
        $response->assertSee('សុខ សូត្រ និង កែវ សាវ៉ាត់');
        $response->assertSee('32,565');
        $response->assertSee('1,432');

        // 5. Verify Book 3
        $response->assertSee('ស្តង់ដាសាលាមធ្យមសិក្សា');
        $response->assertSee('នាយកដ្ឋានមធ្យមសិក្សាចំណេះទូទៅ');
        $response->assertSee('35,308');
        $response->assertSee('1,543');

        // 6. Verify Book 4
        $response->assertSee('សៀវភៅ កុំព្យូទ័ររដ្ឋបាល');
        $response->assertSee('២០២៣');
        $response->assertSee('31,726');
        $response->assertSee('1,669');
    }
}


