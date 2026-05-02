<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMemoryPageLayoutTest extends TestCase
{
    use RefreshDatabase;

    private const UNAVAILABLE = 'Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.';

    private function makeVisiblePage(array $attrs = []): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create(array_merge([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Maria Muster',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ], $attrs));
    }

    private function addGalleryImage(MemoryPage $page, int $n = 0): Media
    {
        return Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => "gallery{$n}.jpg",
            'original_filename' => "gallery{$n}.jpg",
            'path'              => "memory-pages/{$page->id}/gallery/gallery{$n}.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 10000,
            'sort_order'        => $n,
        ]);
    }

    // --- profile header layout ---

    public function test_person_name_is_centered(): void
    {
        $page = $this->makeVisiblePage();
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        // h1 carries text-center class
        $response->assertSee('text-center', false);
        $response->assertSee('Maria Muster');
    }

    public function test_dates_are_centered_when_present(): void
    {
        $page = $this->makeVisiblePage([
            'birth_date' => '1940-03-15',
            'death_date' => '2020-11-01',
        ]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('15.03.1940');
        $response->assertSee('01.11.2020');
        // date paragraph carries text-center
        $response->assertSee('text-center', false);
    }

    public function test_profile_photo_container_is_centered(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage();
        $code = $page->qrCode->short_code;

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

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        // photo container uses flex justify-center
        $response->assertSee('justify-center', false);
    }

    // --- short bio ---

    public function test_short_bio_is_rendered_on_public_page(): void
    {
        $page = $this->makeVisiblePage(['short_bio' => 'Eine kurze Biografie über Maria.']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Eine kurze Biografie über Maria.');
    }

    public function test_short_bio_is_not_shown_when_empty(): void
    {
        $page = $this->makeVisiblePage(['short_bio' => null]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertDontSee('leading-relaxed', false);
    }

    // --- gallery ---

    public function test_gallery_images_are_shown_on_public_page(): void
    {
        Storage::fake('public');
        $page  = $this->makeVisiblePage();
        $media = $this->addGalleryImage($page, 0);
        $code  = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee($media->path, false);
    }

    public function test_no_gallery_heading_shown_when_no_gallery_images(): void
    {
        $page = $this->makeVisiblePage();
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        // No gallery images → gallery grid wrapper must not appear
        $response->assertDontSee('grid-cols-2', false);
    }

    public function test_multiple_gallery_images_are_all_shown(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage();

        $images = [];
        for ($i = 0; $i < 3; $i++) {
            $images[] = $this->addGalleryImage($page, $i);
        }

        $code     = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertOk();
        foreach ($images as $image) {
            $response->assertSee($image->path, false);
        }
    }

    // --- stories below gallery ---

    public function test_published_stories_render_below_gallery(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage();
        $this->addGalleryImage($page, 0);

        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Geschichte nach Galerie',
            'content'      => 'Inhalt hier.',
            'is_published' => true,
        ]);

        $code     = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Geschichte nach Galerie');

        // Gallery must appear before stories in page output
        $content      = $response->getContent();
        $galleryPos   = strpos($content, "memory-pages/{$page->id}/gallery/gallery0.jpg");
        $storyPos     = strpos($content, 'Geschichte nach Galerie');
        $this->assertLessThan($storyPos, $galleryPos, 'Gallery must appear before stories');
    }

    // --- unavailable pages: gallery and profile not leaked ---

    public function test_private_page_does_not_show_gallery_or_profile(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage(['visibility' => 'private']);
        $this->addGalleryImage($page, 0);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Maria Muster');
        $response->assertDontSee("memory-pages/{$page->id}/gallery/gallery0.jpg", false);
    }

    public function test_unpublished_page_does_not_show_gallery_or_profile(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage(['is_published' => false]);
        $this->addGalleryImage($page, 0);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Maria Muster');
        $response->assertDontSee("memory-pages/{$page->id}/gallery/gallery0.jpg", false);
    }

    public function test_locked_page_does_not_show_gallery_or_profile(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage(['is_locked' => true]);
        $this->addGalleryImage($page, 0);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Maria Muster');
        $response->assertDontSee("memory-pages/{$page->id}/gallery/gallery0.jpg", false);
    }

    // --- share button ---

    public function test_public_page_shows_profil_teilen_button(): void
    {
        $page = $this->makeVisiblePage();
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Profil teilen');
    }

    public function test_share_button_appears_after_person_name_and_before_short_bio(): void
    {
        $page = $this->makeVisiblePage(['short_bio' => 'Kurzbiografie hier.']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $content   = $response->getContent();
        $namePos   = strpos($content, 'Maria Muster');
        $sharePos  = strpos($content, 'Profil teilen');
        $bioPos    = strpos($content, 'Kurzbiografie hier.');

        $this->assertLessThan($sharePos, $namePos,  'Person name must appear before share button');
        $this->assertLessThan($bioPos,   $sharePos, 'Share button must appear before short_bio');
    }

    public function test_public_page_includes_share_javascript(): void
    {
        $page = $this->makeVisiblePage();
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('shareProfile', false);
        $response->assertSee('navigator.share', false);
        $response->assertSee('In die Zwischenablage kopiert.', false);
    }

    public function test_private_page_does_not_show_profil_teilen_button(): void
    {
        $page = $this->makeVisiblePage(['visibility' => 'private']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Profil teilen');
    }

    public function test_unpublished_page_does_not_show_profil_teilen_button(): void
    {
        $page = $this->makeVisiblePage(['is_published' => false]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Profil teilen');
    }

    public function test_locked_page_does_not_show_profil_teilen_button(): void
    {
        $page = $this->makeVisiblePage(['is_locked' => true]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Profil teilen');
    }

    public function test_public_page_still_shows_profile_photo_short_bio_gallery_and_stories(): void
    {
        Storage::fake('public');
        $page = $this->makeVisiblePage(['short_bio' => 'Biografie Text.']);

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

        $this->addGalleryImage($page, 0);

        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Eine Geschichte',
            'content'      => 'Geschichte Inhalt.',
            'is_published' => true,
        ]);

        $code     = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Maria Muster');
        $response->assertSee('Biografie Text.');
        $response->assertSee("memory-pages/{$page->id}/gallery/gallery0.jpg", false);
        $response->assertSee('Eine Geschichte');
        $response->assertSee('Profil teilen');
    }
}
