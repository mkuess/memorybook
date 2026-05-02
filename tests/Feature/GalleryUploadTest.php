<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryUploadTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnerAndPage(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Test Person',
        ]);

        return [$owner, $page];
    }

    private function fakeJpeg(int $kilobytes = 100): UploadedFile
    {
        return UploadedFile::fake()->image('photo.jpg', 100, 100)->size($kilobytes);
    }

    public function test_owner_can_upload_a_gallery_image(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertRedirect();
        $this->assertSame(1, $page->media()->where('collection', 'gallery')->count());
        Storage::disk('public')->assertExists(
            $page->media()->where('collection', 'gallery')->first()->path
        );
    }

    public function test_non_owner_cannot_upload_a_gallery_image(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $other = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($other)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertForbidden();
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        $gif = UploadedFile::fake()->create('anim.gif', 50, 'image/gif');

        $response = $this->actingAs($owner)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => $gif]
        );

        $response->assertSessionHasErrors('image');
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_file_larger_than_5_mb_is_rejected(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        $big = UploadedFile::fake()->image('big.jpg', 100, 100)->size(5121);

        $response = $this->actingAs($owner)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => $big]
        );

        $response->assertSessionHasErrors('image');
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_a_memory_page_can_have_up_to_5_gallery_images(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($owner)->post(
                route('memory-pages.gallery.store', $page),
                ['image' => $this->fakeJpeg()]
            )->assertRedirect();
        }

        $this->assertSame(5, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_uploading_a_6th_gallery_image_is_rejected(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($owner)->post(
                route('memory-pages.gallery.store', $page),
                ['image' => $this->fakeJpeg()]
            );
        }

        $response = $this->actingAs($owner)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => $this->fakeJpeg()]
        );

        $response->assertSessionHasErrors('image');
        $this->assertSame(5, $page->media()->where('collection', 'gallery')->count());
    }

    private function createGalleryMedia(MemoryPage $page, string $filename = 'test.jpg'): \App\Models\Media
    {
        return Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => $filename,
            'original_filename' => $filename,
            'path'              => "memory-pages/{$page->id}/gallery/{$filename}",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 102400,
            'sort_order'        => 0,
        ]);
    }

    public function test_uploaded_gallery_images_are_listed_on_the_customer_edit_page(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $this->createGalleryMedia($page, 'test.jpg');

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee("memory-pages/{$page->id}/gallery/test.jpg", false);
    }

    public function test_gallery_thumbnail_has_constrained_size_styles(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $this->createGalleryMedia($page, 'photo.jpg');

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        // inline style forces 64 × 64 px — prevents overflow from large intrinsic images
        $response->assertSee('width:64px', false);
        $response->assertSee('height:64px', false);
        $response->assertSee('max-width:64px', false);
        $response->assertSee('object-fit:cover', false);
    }

    public function test_gallery_thumbnail_link_opens_in_new_tab(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $this->createGalleryMedia($page, 'photo.jpg');

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_gallery_filename_is_visible_on_edit_page(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $this->createGalleryMedia($page, 'mein-foto.jpg');

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('mein-foto.jpg');
    }

    public function test_gallery_delete_button_shows_loeschen_text(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $this->createGalleryMedia($page, 'photo.jpg');

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Löschen');
    }

    public function test_owner_can_delete_own_gallery_image(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $media = $this->createGalleryMedia($page, 'photo.jpg');

        $response = $this->actingAs($owner)->delete(
            route('memory-pages.gallery.destroy', [$page, $media])
        );

        $response->assertRedirect();
        $this->assertSame(0, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_non_owner_cannot_delete_gallery_image(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $media = $this->createGalleryMedia($page, 'photo.jpg');
        $other = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($other)->delete(
            route('memory-pages.gallery.destroy', [$page, $media])
        );

        $response->assertForbidden();
        $this->assertSame(1, $page->media()->where('collection', 'gallery')->count());
    }

    public function test_deleting_gallery_image_removes_media_record(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();
        $media = $this->createGalleryMedia($page, 'photo.jpg');
        $mediaId = $media->id;

        $this->actingAs($owner)->delete(
            route('memory-pages.gallery.destroy', [$page, $media])
        );

        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
    }

    public function test_public_memory_page_shows_gallery_images(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        $page->update([
            'is_published' => true,
            'visibility'   => 'link',
        ]);

        Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'gallery1.jpg',
            'original_filename' => 'gallery1.jpg',
            'path'              => "memory-pages/{$page->id}/gallery/gallery1.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 10000,
            'sort_order'        => 0,
        ]);

        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee("memory-pages/{$page->id}/gallery/gallery1.jpg", false);
    }
}
