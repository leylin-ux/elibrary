<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\User;
use App\Services\BackupService;
use Tests\TestCase;

class BackupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        $this->artisan('db:seed');
    }

    public function test_member_cannot_access_backup_page(): void
    {
        $member = User::where('role', 'member')->first();

        $response = $this->actingAs($member)->get(route('backups.index'));
        $response->assertRedirect(route('dashboard'));
    }

    public function test_manager_cannot_access_backup_page(): void
    {
        $manager = User::where('role', 'manager')->first();

        $response = $this->actingAs($manager)->get(route('backups.index'));
        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_backup_page_in_khmer(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->withSession(['locale' => 'km'])
            ->get(route('backups.index'));

        $response->assertStatus(200);
        $response->assertSee('Server Drive D');
        $response->assertSee('ការបម្រុងទុក និងស្តារទិន្នន័យ');
        $response->assertSee('ធ្វើការបម្រុងទុកឥឡូវនេះ');
    }

    public function test_super_admin_can_view_backup_page_in_english(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->withSession(['locale' => 'en'])
            ->get(route('backups.index'));

        $response->assertStatus(200);
        $response->assertSee('Server Drive D');
        $response->assertSee('Data Backup & Recovery');
        $response->assertSee('Backup Now');
    }

    public function test_super_admin_can_trigger_instant_backup_to_drive_d(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('backups.run'));
        $response->assertRedirect(route('backups.index'));
        $response->assertSessionHas('success');

        $latestBackup = Backup::latest('id')->first();
        $this->assertNotNull($latestBackup);
        $this->assertEquals('manual', $latestBackup->backup_type);
        $this->assertEquals('success', $latestBackup->status);
        $this->assertFileExists($latestBackup->disk_path);
        $this->assertGreaterThan(0, $latestBackup->file_size);
    }

    public function test_super_admin_can_download_backup(): void
    {
        $admin = User::where('role', 'admin')->first();

        $service = app(BackupService::class);
        $backup = $service->createBackup('manual', $admin);

        $response = $this->actingAs($admin)->get(route('backups.download', $backup->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/zip');
    }
}
