<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemoryPageEditTest extends TestCase
{
    use RefreshDatabase;

    private function createPageForUser(User $user, array $attrs = []): MemoryPage
    {
        return MemoryPage::create(array_merge([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ], $attrs));
    }

    // --- access ---

    public function test_owner_can_access_edit_page(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Erinnerungsseite bearbeiten');
        $response->assertSee('Max Mustermann');
    }

    public function test_non_owner_gets_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->actingAs($other)->get(route('memory-pages.edit', $page));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_edit_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->get(route('memory-pages.edit', $page));

        $response->assertRedirect(route('login'));
    }

    // --- sections visible ---

    public function test_owner_sees_profile_photo_section(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Profilfoto');
        $response->assertSee('Noch kein Profilfoto hochgeladen.');
    }

    public function test_owner_sees_existing_profile_photo_on_edit_page(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'profile',
            'filename'          => 'photo.jpg',
            'original_filename' => 'photo.jpg',
            'path'              => "memory-pages/{$page->id}/profile/photo.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 5000,
            'sort_order'        => 0,
        ]);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee("memory-pages/{$page->id}/profile/photo.jpg", false);
        $response->assertDontSee('Noch kein Profilfoto hochgeladen.');
    }

    public function test_owner_sees_gallery_section(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Galerie');
        $response->assertSee('Noch keine Galeriebilder hochgeladen.');
    }

    public function test_owner_sees_basisdaten_section(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Basisdaten');
        $response->assertSee('Name der Person');
        $response->assertSee('Geburtsdatum');
        $response->assertSee('Sterbedatum');
        $response->assertSee('Kurze Biografie');
    }

    public function test_owner_sees_visibility_and_publish_section(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Sichtbarkeit und Veröffentlichung');
        $response->assertSee('Privat');
        $response->assertSee('Nur per Link');
        $response->assertSee('Öffentlich');
    }

    public function test_story_management_link_is_visible(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Stories verwalten');
        $response->assertSee(route('memory-pages.stories.index', $page), false);
    }

    public function test_qr_code_link_is_visible(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('QR-Code anzeigen');
        $response->assertSee(route('memory-pages.qr-code', $page), false);
    }

    public function test_admin_lock_controls_are_not_shown_on_customer_edit_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertDontSee('is_locked');
        $response->assertDontSee('Sperren');
    }

    // --- gallery thumbnail list ---

    public function test_customer_edit_page_lists_gallery_images_in_compact_rows(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'myfoto.jpg',
            'original_filename' => 'myfoto.jpg',
            'path'              => "memory-pages/{$page->id}/gallery/myfoto.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 51200,
            'sort_order'        => 0,
        ]);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee("memory-pages/{$page->id}/gallery/myfoto.jpg", false);
        $response->assertSee('myfoto.jpg');
        $response->assertSee('width:64px', false);
        $response->assertSee('height:64px', false);
        $response->assertSee('object-fit:cover', false);
    }

    public function test_gallery_thumbnails_open_in_new_tab_on_customer_edit_page(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'photo.jpg',
            'original_filename' => 'photo.jpg',
            'path'              => "memory-pages/{$page->id}/gallery/photo.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 51200,
            'sort_order'        => 0,
        ]);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    // --- uploads ---

    public function test_owner_can_upload_profile_photo_from_customer_edit_page(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(
            route('memory-pages.profile-photo.store', $page),
            ['photo' => UploadedFile::fake()->image('photo.jpg', 200, 200)->size(100)]
        );

        $response->assertRedirect();
        $this->assertSame(1, $page->media()->where('collection', 'profile')->count());
    }

    public function test_owner_can_upload_gallery_image_from_customer_edit_page(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(
            route('memory-pages.gallery.store', $page),
            ['image' => UploadedFile::fake()->image('gallery.jpg', 200, 200)->size(100)]
        );

        $response->assertRedirect();
        $this->assertSame(1, $page->media()->where('collection', 'gallery')->count());
    }

    // --- basisdaten update ---

    public function test_owner_can_update_basic_data(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
            'birth_date'  => '1950-03-15',
            'short_bio'   => 'Eine kurze Biografie.',
        ]);

        $response->assertRedirect(route('memory-pages.edit', $page));

        $page->refresh();
        $this->assertSame('Erika Musterfrau', $page->person_name);
        $this->assertSame('1950-03-15', $page->birth_date->toDateString());
        $this->assertSame('Eine kurze Biografie.', $page->short_bio);
    }

    // --- visibility update ---

    public function test_owner_can_update_visibility_separately(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user, ['visibility' => 'private']);

        $response = $this->actingAs($user)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => 'link']
        );

        $response->assertRedirect(route('memory-pages.edit', $page));
        $this->assertSame('link', $page->fresh()->visibility);
    }

    public function test_non_owner_cannot_update_visibility(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner, ['visibility' => 'private']);

        $response = $this->actingAs($other)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => 'public']
        );

        $response->assertForbidden();
        $this->assertSame('private', $page->fresh()->visibility);
    }

    public function test_invalid_visibility_value_is_rejected(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user, ['visibility' => 'private']);

        $response = $this->actingAs($user)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => 'hidden']
        );

        $response->assertSessionHasErrors('visibility');
        $this->assertSame('private', $page->fresh()->visibility);
    }

    // --- slug protection ---

    public function test_slug_cannot_be_changed_through_request_data(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $slug = $page->slug;

        $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
            'slug'        => 'hackedsl',
        ]);

        $this->assertSame($slug, $page->fresh()->slug);
    }

    // --- dashboard link ---

    public function test_dashboard_links_to_edit_page(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.edit', $page));
    }

    // --- publish labels ---

    public function test_unpublished_page_shows_veröffentlichen_button(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user, ['is_published' => false]);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Veröffentlichen');
    }

    public function test_published_page_shows_nicht_mehr_veröffentlichen_button(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user, ['is_published' => true]);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Nicht mehr veröffentlichen');
    }
}
