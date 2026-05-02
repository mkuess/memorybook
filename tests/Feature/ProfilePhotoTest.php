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

    // --- customer route tests ---

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

        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($other)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertForbidden();
        $this->assertCount(0, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_customer_profile_photo_upload_still_works(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('customer.jpg', 150, 150);

        $response = $this->actingAs($owner)
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

        $this->assertCount(1, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    // --- upload page (GET) tests ---

    public function test_admin_can_access_profile_photo_upload_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($admin)
            ->get(route('memory-pages.profile-photo.create', $page));

        $response->assertOk();
        $response->assertSee('Profilfoto hochladen');
    }

    public function test_owner_can_access_profile_photo_upload_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($owner)
            ->get(route('memory-pages.profile-photo.create', $page));

        $response->assertOk();
    }

    public function test_non_owner_cannot_access_profile_photo_upload_page(): void
    {
        $owner = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($other)
            ->get(route('memory-pages.profile-photo.create', $page));

        $response->assertForbidden();
    }

    public function test_upload_page_has_correct_form_attributes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($admin)
            ->get(route('memory-pages.profile-photo.create', $page) . '?from=admin');

        $response->assertOk();
        $response->assertSee('method="POST"', false);
        $response->assertSee('enctype="multipart/form-data"', false);
        $response->assertSee('name="photo"', false);
    }

    public function test_upload_page_shows_no_photo_text_when_none_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($admin)
            ->get(route('memory-pages.profile-photo.create', $page));

        $response->assertOk();
        $response->assertSee('Noch kein Profilfoto hochgeladen.');
    }

    // --- admin upload (POST) tests ---

    public function test_admin_can_upload_profile_photo_via_admin_route(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $this->actingAs($admin)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
                'from'  => 'admin',
            ]);

        $this->assertCount(1, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    public function test_admin_upload_redirects_to_filament_edit_page(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($admin)
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
                'from'  => 'admin',
            ]);

        $response->assertRedirect("/admin/memory-pages/{$page->id}/edit");
    }

    public function test_customer_upload_without_from_redirects_back(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);
        $file  = UploadedFile::fake()->image('photo.jpg', 200, 200);

        $response = $this->actingAs($owner)
            ->from(route('memory-pages.profile-photo.create', $page))
            ->post(route('memory-pages.profile-photo.store', $page), [
                'photo' => $file,
            ]);

        $response->assertRedirect(route('memory-pages.profile-photo.create', $page));
    }

    public function test_replacing_profile_photo_via_admin_route_keeps_only_one_record(): void
    {
        Storage::fake('public');

        $admin  = User::factory()->create(['role' => 'admin']);
        $owner  = User::factory()->create(['role' => 'user']);
        $page   = $this->makeMemoryPage($owner);
        $first  = UploadedFile::fake()->image('first.jpg', 100, 100);
        $second = UploadedFile::fake()->image('second.jpg', 100, 100);

        $this->actingAs($admin)
            ->post(route('memory-pages.profile-photo.store', $page), ['photo' => $first, 'from' => 'admin']);

        $this->actingAs($admin)
            ->post(route('memory-pages.profile-photo.store', $page), ['photo' => $second, 'from' => 'admin']);

        $this->assertCount(1, $page->fresh()->media()->where('collection', 'profile')->get());
    }

    // --- Filament edit page integration ---

    public function test_filament_edit_page_shows_profilfoto_hochladen_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}/edit");

        $response->assertOk();
        $response->assertSee('Profilfoto hochladen');
    }

    public function test_filament_edit_page_upload_button_links_to_upload_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}/edit");

        $response->assertOk();
        $response->assertSee(route('memory-pages.profile-photo.create', $page), false);
    }

    // --- public page test ---

    public function test_public_memory_page_shows_profile_photo_when_available(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'      => $owner->id,
            'slug'         => 'testslug1',
            'person_name'  => 'Test Person',
            'is_published' => true,
            'visibility'   => 'link',
        ]);

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

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee(Storage::disk('public')->url($fakePath), false);
    }
}
