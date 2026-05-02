<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create(['role' => 'user']);

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Test Person',
        ]);
    }

    public function test_owner_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertRedirect();

        $media = $page->fresh()->media()->where('collection', 'profile')->first();
        $this->assertNotNull($media);
        $this->assertSame('profile', $media->collection);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_non_owner_cannot_upload_profile_photo(): void
    {
        Storage::fake('public');

        $owner   = User::factory()->create(['role' => 'user']);
        $other   = User::factory()->create(['role' => 'user']);
        $page    = $this->makeMemoryPage($owner);
        $file    = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($other)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertForbidden();
        $this->assertCount(0, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_admin_can_upload_profile_photo_in_filament(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('admin_photo.jpg', 100, 100);

        $response = $this->actingAs($admin)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertRedirect();
        $this->assertCount(1, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_invalid_file_type_is_rejected(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
        $this->assertCount(0, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_file_larger_than_5_mb_is_rejected(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->create('large.jpg', 6000, 'image/jpeg');

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertSessionHasErrors('photo');
        $this->assertCount(0, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_replacing_profile_photo_keeps_only_one_profile_media_record(): void
    {
        Storage::fake('public');

        $owner  = User::factory()->create(['role' => 'user']);
        $page   = $this->makeMemoryPage($owner);
        $first  = UploadedFile::fake()->image('first.jpg', 100, 100);
        $second = UploadedFile::fake()->image('second.jpg', 100, 100);

        $this->actingAs($owner)
            ->post(route('memory-pages.profile-photo.store', $page), ['photo' => $first]);

        $this->actingAs($owner)
            ->post(route('memory-pages.profile-photo.store', $page), ['photo' => $second]);

        $profileMedia = $page->fresh()->media()->where('collection', 'profile')->get();
        $this->assertCount(1, $profileMedia);
    }

    public function test_public_memory_page_shows_profile_photo_when_available(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => 'testslug1',
            'person_name' => 'Test Person',
            'is_published' => true,
            'visibility'   => 'link',
        ]);

        // Create a fake file on the public disk
        $fakePath = "memory-pages/{$page->id}/profile/photo.jpg";
        Storage::disk('public')->put($fakePath, 'fake-image-data');

        $page->media()->create([
            'collection'        => 'profile',
            'filename'          => 'photo.jpg',
            'original_filename' => 'photo.jpg',
            'path'              => $fakePath,
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 100,
            'sort_order'        => 0,
        ]);

        $shortCode = $page->qrCode->short_code;

        $response = $this->get("/m/{$shortCode}");

        $response->assertOk();
        $response->assertSee(Storage::disk('public')->url($fakePath), false);
    }
}
