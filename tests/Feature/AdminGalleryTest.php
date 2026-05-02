<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdminAndPage(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Test Person',
        ]);

        return [$admin, $page];
    }

    private function fakeJpeg(int $kilobytes = 100): UploadedFile
    {
        return UploadedFile::fake()->image('photo.jpg', 100, 100)->size($kilobytes);
    }

    private function createGalleryMedia(MemoryPage $page, int $count = 1): void
    {
        for ($i = 0; $i < $count; $i++) {
            Media::create([
                'memory_page_id'    => $page->id,
                'collection'        => 'gallery',
                'filename'          => "img{$i}.jpg",
                'original_filename' => "img{$i}.jpg",
                'path'              => "memory-pages/{$page->id}/gallery/img{$i}.jpg",
                'mime_type'         => 'image/jpeg',
                'size_bytes'        => 10000,
                'sort_order'        => $i,
            ]);
        }
    }

    // --- upload tests ---

    public function test_admin_can_upload_gallery_image_from_edit_page(): void
    {
        Storage::fake('public');
        [$admin, $page] = $this->makeAdminAndPage();

        $response = $this->actingAs($admin)->post(
            route('memory-pages.admin-gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertRedirect("/admin/memory-pages/{$page->id}/edit");
        $this->assertSame(1, $page->media()->where('collection', 'gallery')->count());

        $media = $page->media()->where('collection', 'gallery')->first();
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_admin_cannot_upload_more_than_5_gallery_images(): void
    {
        Storage::fake('public');
        [$admin, $page] = $this->makeAdminAndPage();

        $this->createGalleryMedia($page, 5);

        $response = $this->actingAs($admin)->post(
            route('memory-pages.admin-gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertSessionHasErrors('image');
        $this->assertSame(5, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_normal_user_cannot_use_admin_gallery_upload(): void
    {
        Storage::fake('public');
        [, $page] = $this->makeAdminAndPage();
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->post(
            route('memory-pages.admin-gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertForbidden();
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
    }

    // --- delete tests ---

    public function test_admin_can_remove_a_gallery_image(): void
    {
        Storage::fake('public');
        [$admin, $page] = $this->makeAdminAndPage();
        $this->createGalleryMedia($page, 1);

        $media = $page->media()->where('collection', 'gallery')->first();

        Storage::disk('public')->put($media->path, 'fake image content');

        $response = $this->actingAs($admin)->delete(
            route('memory-pages.admin-gallery.destroy', [$page, $media])
        );

        $response->assertRedirect("/admin/memory-pages/{$page->id}/edit");
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_normal_user_cannot_remove_gallery_image(): void
    {
        Storage::fake('public');
        [, $page] = $this->makeAdminAndPage();
        $this->createGalleryMedia($page, 1);
        $media = $page->media()->where('collection', 'gallery')->first();
        $user  = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->delete(
            route('memory-pages.admin-gallery.destroy', [$page, $media])
        );

        $response->assertForbidden();
        $this->assertSame(1, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_admin_cannot_delete_gallery_image_belonging_to_another_page(): void
    {
        Storage::fake('public');
        [$admin, $page] = $this->makeAdminAndPage();

        $otherOwner = User::factory()->create(['role' => 'user']);
        $otherPage  = MemoryPage::create([
            'user_id'     => $otherOwner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Other Person',
        ]);
        $this->createGalleryMedia($otherPage, 1);
        $media = $otherPage->media()->where('collection', 'gallery')->first();

        $response = $this->actingAs($admin)->delete(
            route('memory-pages.admin-gallery.destroy', [$page, $media])
        );

        $response->assertNotFound();
        $this->assertSame(1, $otherPage->media()->where('collection', 'gallery')->count());
    }

    // --- public page test ---

    public function test_uploaded_admin_gallery_image_appears_on_public_memory_page(): void
    {
        Storage::fake('public');
        [$admin, $page] = $this->makeAdminAndPage();

        $page->update([
            'is_published' => true,
            'visibility'   => 'link',
        ]);

        $this->actingAs($admin)->post(
            route('memory-pages.admin-gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $media = $page->media()->where('collection', 'gallery')->first();
        $code  = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee($media->path, false);
    }
}
