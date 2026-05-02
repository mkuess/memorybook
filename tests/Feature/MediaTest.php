<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function mediaAttributes(MemoryPage $page, array $overrides = []): array
    {
        return array_merge([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'photo.jpg',
            'original_filename' => 'My Photo.jpg',
            'path'              => 'uploads/photo.jpg',
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 204800,
        ], $overrides);
    }

    public function test_memory_page_can_have_media(): void
    {
        $page = $this->makeMemoryPage();

        Media::create($this->mediaAttributes($page, ['filename' => 'a.jpg', 'path' => 'uploads/a.jpg']));
        Media::create($this->mediaAttributes($page, ['filename' => 'b.jpg', 'path' => 'uploads/b.jpg']));

        $this->assertCount(2, $page->media);
    }

    public function test_media_belongs_to_memory_page(): void
    {
        $page  = $this->makeMemoryPage();
        $media = Media::create($this->mediaAttributes($page));

        $this->assertInstanceOf(MemoryPage::class, $media->memoryPage);
        $this->assertEquals($page->id, $media->memoryPage->id);
    }

    public function test_default_sort_order_is_0(): void
    {
        $page  = $this->makeMemoryPage();
        $media = Media::create($this->mediaAttributes($page));

        $this->assertSame(0, $media->sort_order);
    }

    public function test_collection_can_store_profile(): void
    {
        $page  = $this->makeMemoryPage();
        $media = Media::create($this->mediaAttributes($page, ['collection' => 'profile']));

        $this->assertSame('profile', $media->collection);
    }

    public function test_collection_can_store_cover(): void
    {
        $page  = $this->makeMemoryPage();
        $media = Media::create($this->mediaAttributes($page, ['collection' => 'cover']));

        $this->assertSame('cover', $media->collection);
    }

    public function test_collection_can_store_gallery(): void
    {
        $page  = $this->makeMemoryPage();
        $media = Media::create($this->mediaAttributes($page, ['collection' => 'gallery']));

        $this->assertSame('gallery', $media->collection);
    }
}
