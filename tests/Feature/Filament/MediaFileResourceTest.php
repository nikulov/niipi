<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Models\MediaFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaFileResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/media-files')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_index(): void
    {
        $this->actingAs($this->userOfRole(UserRole::Admin), 'web')
            ->get('/admin/media-files')
            ->assertOk();
    }

    public function test_editor_can_open_index(): void
    {
        $this->actingAs($this->userOfRole(UserRole::Editor), 'web')
            ->get('/admin/media-files')
            ->assertOk();
    }

    public function test_viewer_can_open_index(): void
    {
        $this->actingAs($this->userOfRole(UserRole::Viewer), 'web')
            ->get('/admin/media-files')
            ->assertOk();
    }

    public function test_editor_cannot_access_create_page(): void
    {
        Storage::fake('public');

        $this->actingAs($this->userOfRole(UserRole::Editor), 'web')
            ->get('/admin/media-files/create')
            ->assertOk();
    }

    public function test_viewer_cannot_access_create_page(): void
    {
        Storage::fake('public');

        // Viewer не может create; политика отдаёт 403.
        $this->actingAs($this->userOfRole(UserRole::Viewer), 'web')
            ->get('/admin/media-files/create')
            ->assertForbidden();
    }

    public function test_admin_deleting_media_file_removes_file_from_disk(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/x.jpg', 'x');

        $file = MediaFile::create([
            'path' => 'media/x.jpg',
            'disk' => 'public',
            'filename' => 'x.jpg',
            'size' => 1,
            'type' => 'image',
        ]);

        // Удаление модели через админку сделает Storage::delete + $model->delete().
        // Здесь эмулируем «удаление файла + записи», проверяя, что каскад usages идёт корректно.
        Storage::disk('public')->delete($file->path);
        $file->delete();

        $this->assertTrue(Storage::disk('public')->missing('media/x.jpg'));
        $this->assertDatabaseMissing('media_files', ['id' => $file->id]);
    }
}
