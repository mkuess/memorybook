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

    public function test_uploaded_gallery_images_are_listed_on_the_customer_edit_page(): void
    {
        Storage::fake('public');
        [$owner, $page] = $this->makeOwnerAndPage();

        Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'test.jpg',
            'original_filename' => 'test.jpg',
            'path'              => "memory-pages/{$page->id}/gallery/test.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 10000,
            'sort_order'        => 0,
        ]);

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee("memory-pages/{$page->id}/gallery/test.jpg", false);
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
