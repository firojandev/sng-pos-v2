<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Modules\Core\DataTables\BackupDataTable;
use Modules\Core\Services\DatabaseBackupService;
use Modules\Core\Support\Permissions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseBackupFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $regularUser;

    protected DatabaseBackupService $backupService;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (Permissions::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $this->superAdmin = User::create([
            'name' => 'Super Admin User',
            'email' => 'superadmin@test.com',
            'password' => bcrypt('password123'),
        ]);
        $this->superAdmin->assignRole($superAdminRole);

        $this->regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@test.com',
            'password' => bcrypt('password123'),
        ]);

        $this->backupService = app(DatabaseBackupService::class);
    }

    protected function tearDown(): void
    {
        // Clean up any test backups created during tests
        $backups = $this->backupService->getBackups();
        foreach ($backups as $b) {
            $this->backupService->deleteBackup($b['filename']);
        }

        parent::tearDown();
    }

    public function test_super_admin_can_view_backup_page(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('backup.index'));

        $response->assertStatus(200);
        $response->assertSee('ডাটাবেজ ব্যাকআপ');
        $response->assertSee('backup-data-table');
    }

    public function test_unauthenticated_user_cannot_access_backup(): void
    {
        $response = $this->get(route('backup.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_cannot_access_backup(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('backup.index'));

        $response->assertStatus(403);
    }

    public function test_shop_admin_cannot_access_database_backup(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $shopAdmin = User::create([
            'name' => 'Shop Admin',
            'email' => 'shopadmin@test.com',
            'password' => bcrypt('password123'),
        ]);
        $shopAdmin->assignRole($adminRole);

        $response = $this->actingAs($shopAdmin)->get(route('backup.index'));
        $response->assertStatus(403);

        $storeResponse = $this->actingAs($shopAdmin)->postJson(route('backup.store'));
        $storeResponse->assertStatus(403);
    }

    public function test_super_admin_can_create_backup_manually(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('backup.store'));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $filename = $response->json('backup.filename');
        $this->assertNotEmpty($filename);
        $this->assertNotNull($this->backupService->findBackup($filename));
    }

    public function test_datatable_ajax_returns_backups_list(): void
    {
        // Create a backup first
        $backup = $this->backupService->createBackup('test');

        $dataTable = app(BackupDataTable::class);
        request()->headers->set('X-Requested-With', 'XMLHttpRequest');

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('backup.index'), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'draw',
            'recordsTotal',
            'recordsFiltered',
            'data',
        ]);
        $response->assertSee($backup['filename']);
    }

    public function test_super_admin_can_download_backup_file(): void
    {
        $backup = $this->backupService->createBackup('download_test');

        $response = $this->actingAs($this->superAdmin)
            ->get(route('backup.download', ['filename' => $backup['filename']]));

        $response->assertStatus(200);
        $response->assertHeader('content-disposition', 'attachment; filename='.$backup['filename']);
    }

    public function test_super_admin_can_delete_backup_file(): void
    {
        $backup = $this->backupService->createBackup('delete_test');

        $response = $this->actingAs($this->superAdmin)
            ->deleteJson(route('backup.destroy', ['filename' => $backup['filename']]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertNull($this->backupService->findBackup($backup['filename']));
    }

    public function test_super_admin_can_inspect_backup_manifest(): void
    {
        $backup = $this->backupService->createBackup('inspect_test');

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('backup.inspect', ['filename' => $backup['filename']]));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'filename',
                'size',
                'size_formatted',
                'created_at',
                'type',
                'can_restore_shops',
                'shops',
            ],
        ]);
    }

    public function test_super_admin_can_restore_full_database(): void
    {
        $backup = $this->backupService->createBackup('restore_full_test');

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('backup.restore', ['filename' => $backup['filename']]), [
                'mode' => 'full',
                'confirm_text' => 'RESTORE',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_super_admin_can_restore_specific_shops(): void
    {
        $shopId = DB::table('shops')->insertGetId([
            'name' => 'Test Shop 1',
            'slug' => 'test-shop-1',
            'phone' => '01700000000',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $backup = $this->backupService->createBackup('restore_shop_test');

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('backup.restore', ['filename' => $backup['filename']]), [
                'mode' => 'shops',
                'shop_ids' => [$shopId],
                'confirm_text' => 'RESTORE',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_restore_fails_with_invalid_confirmation(): void
    {
        $backup = $this->backupService->createBackup('restore_fail_test');

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('backup.restore', ['filename' => $backup['filename']]), [
                'mode' => 'full',
                'confirm_text' => 'WRONG_TEXT',
            ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
    }
}
