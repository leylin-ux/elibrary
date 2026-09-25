<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentLoginAndDirectoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }
    public function test_unlogged_students_are_hidden_from_directory_until_login(): void
    {
        // 1. Create Admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin_test@elibrary.com',
            'role' => 'admin',
            'member_type' => 'Staff',
            'card_id' => 'LIB-ADM-TEST',
            'last_login_at' => now(),
            'first_login_at' => now(),
            'status' => 'Active',
        ]);

        // 2. Create Pre-seeded Student with un-logged status (last_login_at = null)
        $student = User::factory()->create([
            'name' => 'Chantha Student',
            'email' => 'chantha@student.edu.kh',
            'card_id' => 'ST-2026-999',
            'role' => 'member',
            'member_type' => 'Student',
            'password' => Hash::make('Stu@2026#999'),
            'last_login_at' => null,
            'first_login_at' => null,
            'status' => 'Active',
        ]);

        // 3. Admin visits Settings > Users & Roles Directory
        $response = $this->actingAs($admin)->get(route('settings.index', ['tab' => 'users']));
        $response->assertStatus(200);

        // Admin should be visible, but un-logged-in student must NOT be visible in the directory
        $response->assertSee('Admin User');
        $response->assertDontSee('Chantha Student');
        $response->assertDontSee('ST-2026-999');

        // 4. Admin checks the pending_login filter
        $pendingResponse = $this->actingAs($admin)->get(route('settings.index', [
            'tab' => 'users',
            'status_filter' => 'pending_login',
        ]));
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('Chantha Student');
        $pendingResponse->assertSee('ST-2026-999');

        // 5. Student logs in with their Card ID and password
        Auth::logout();
        $loginResponse = $this->post('/login', [
            'email' => 'ST-2026-999',
            'password' => 'Stu@2026#999',
        ]);
        $loginResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student);

        // Refresh student from DB and assert last_login_at is now populated
        $student->refresh();
        $this->assertNotNull($student->last_login_at);

        // 6. Admin visits directory again: student MUST NOW be visible!
        $responseAfterLogin = $this->actingAs($admin)->get(route('settings.index', ['tab' => 'users']));
        $responseAfterLogin->assertStatus(200);
        $responseAfterLogin->assertSee('Chantha Student');
        $responseAfterLogin->assertSee('ST-2026-999');
    }

    public function test_student_can_also_login_with_card_id_as_default_password(): void
    {
        $student = User::factory()->create([
            'name' => 'Default Pwd Student',
            'email' => 'default@student.edu.kh',
            'card_id' => 'ST-2026-888',
            'role' => 'member',
            'member_type' => 'Student',
            'password' => Hash::make('secret_unrelated_hash'),
            'last_login_at' => null,
            'status' => 'Active',
        ]);

        // Login using Card ID as password (fallback supported in AuthController)
        $response = $this->post('/login', [
            'email' => 'st-2026-888', // lowercase input test
            'password' => 'ST-2026-888',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student);
        $student->refresh();
        $this->assertNotNull($student->last_login_at);
    }

    public function test_lecturer_can_login_and_has_vip_ten_book_quota(): void
    {
        $this->artisan('db:seed', ['--class' => 'LecturerImportSeeder']);

        $lecturer = User::where('card_id', 'LEC-IT-001')->first();
        $this->assertNotNull($lecturer);
        $this->assertEquals('បណ្ឌិត អ៊ឹម សុភា', $lecturer->name);
        $this->assertEquals('Teacher', $lecturer->member_type);
        $this->assertEquals(10, $lecturer->maxAllowedLoans());

        // Test login
        $response = $this->post('/login', [
            'email' => 'LEC-IT-001',
            'password' => 'Lec@2026#01',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($lecturer);
        $lecturer->refresh();
        $this->assertNotNull($lecturer->last_login_at);
    }
}
