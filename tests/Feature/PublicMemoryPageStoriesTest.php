<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemoryPageStoriesTest extends TestCase
{
    use RefreshDatabase;

    private function makeVisiblePage(): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max Mustermann',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);
    }

    private function makeEligiblePage(): MemoryPage
    {
        $page = $this->makeVisiblePage();
        Order::create([
            'user_id'                                => $page->user_id,
            'memory_page_id'                         => $page->id,
            'package'                                => 'basic',
            'status'                                 => 'paid',
            'billing_name'                           => 'Test',
            'billing_email'                          => 'test@example.com',
            'billing_address'                        => 'Str. 1',
            'billing_postal_code'                    => '1010',
            'billing_city'                           => 'Wien',
            'billing_country'                        => 'Österreich',
            'consent_confirmed_at'                   => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);

        return $page;
    }

    public function test_public_memory_page_shows_published_stories(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Auto',
            'content'      => 'Inhalt der Erinnerung.',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Inhalt der Erinnerung.');
    }

    public function test_public_memory_page_does_not_show_unpublished_stories(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Auto',
            'content'      => 'Geheimer Inhalt.',
            'is_published' => false,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertDontSee('Geheimer Inhalt.');
    }

    public function test_stories_are_ordered_by_sort_order(): void
    {
        $page = $this->makeVisiblePage();

        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Auto',
            'content'      => 'Inhalt B.',
            'sort_order'   => 2,
            'is_published' => true,
        ]);
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Auto',
            'content'      => 'Inhalt A.',
            'sort_order'   => 1,
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Inhalt B.'),
            strpos($content, 'Inhalt A.'),
            'Inhalt A. should appear before Inhalt B.'
        );
    }

    public function test_unavailable_pages_do_not_show_stories(): void
    {
        $user = User::factory()->create();

        foreach ([
            ['visibility' => 'private', 'is_published' => true,  'is_locked' => false],
            ['visibility' => 'link',    'is_published' => false, 'is_locked' => false],
            ['visibility' => 'link',    'is_published' => true,  'is_locked' => true],
        ] as $attrs) {
            $page = MemoryPage::create(array_merge([
                'user_id'     => $user->id,
                'slug'        => substr(md5(uniqid()), 0, 8),
                'person_name' => 'Max Mustermann',
            ], $attrs));

            $page->stories()->create([
                'user_id'      => $user->id,
                'title'        => 'Auto',
                'content'      => 'Versteckter Inhalt.',
                'is_published' => true,
            ]);

            $response = $this->get("/m/{$page->qrCode->short_code}");

            $response->assertOk();
            $response->assertDontSee('Versteckter Inhalt.');
        }
    }

    // --- "Erinnerung hinterlassen" button ---

    public function test_public_page_shows_erinnerung_hinterlassen_button_when_eligible(): void
    {
        $page = $this->makeEligiblePage();

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Erinnerung hinterlassen');
    }

    public function test_public_page_shows_button_even_without_paid_order(): void
    {
        $page = $this->makeVisiblePage();

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Erinnerung hinterlassen');
    }

    public function test_owner_preview_of_private_page_shows_button(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'private',
        ]);

        $response = $this->actingAs($user)->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Erinnerung hinterlassen');
    }

    // --- erinnerung image display ---

    public function test_erinnerung_image_is_rendered_on_public_page(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Mit Bild',
            'content'      => 'Schöne Erinnerung.',
            'image_path'   => 'story-images/portrait.jpg',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('story-images/portrait.jpg', false);
    }

    public function test_erinnerung_image_uses_object_contain(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Mit Bild',
            'content'      => 'Schöne Erinnerung.',
            'image_path'   => 'story-images/portrait.jpg',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('object-contain', false);
    }

    public function test_erinnerung_image_does_not_use_object_cover(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Mit Bild',
            'content'      => 'Schöne Erinnerung.',
            'image_path'   => 'story-images/portrait.jpg',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $html = $response->getContent();
        // Find the story image tag and verify it doesn't use object-cover
        $imgPos = strpos($html, 'story-images/portrait.jpg');
        $this->assertNotFalse($imgPos);
        $imgTagStart = strrpos(substr($html, 0, $imgPos), '<img');
        $imgTagEnd   = strpos($html, '>', $imgTagStart);
        $imgTag      = substr($html, $imgTagStart, $imgTagEnd - $imgTagStart + 1);
        $this->assertStringNotContainsString('object-cover', $imgTag);
    }

    public function test_erinnerung_text_still_renders_with_image(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Mit Bild',
            'content'      => 'Text bleibt sichtbar.',
            'image_path'   => 'story-images/portrait.jpg',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Text bleibt sichtbar.');
    }

    public function test_gallery_images_still_use_object_cover(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $page = $this->makeVisiblePage();

        \App\Models\Media::create([
            'memory_page_id'    => $page->id,
            'collection'        => 'gallery',
            'filename'          => 'gallery0.jpg',
            'original_filename' => 'gallery0.jpg',
            'path'              => "memory-pages/{$page->id}/gallery/gallery0.jpg",
            'mime_type'         => 'image/jpeg',
            'size_bytes'        => 10000,
            'sort_order'        => 0,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $html       = $response->getContent();
        $galleryPos = strpos($html, "memory-pages/{$page->id}/gallery/gallery0.jpg");
        $this->assertNotFalse($galleryPos);
        $imgStart = strrpos(substr($html, 0, $galleryPos), '<img');
        $imgEnd   = strpos($html, '>', $imgStart);
        $imgTag   = substr($html, $imgStart, $imgEnd - $imgStart + 1);
        $this->assertStringContainsString('object-cover', $imgTag);
    }

    // --- confirmed visitor memories ---

    public function test_public_page_shows_confirmed_visitor_memory(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'               => $page->user_id,
            'title'                 => 'Auto',
            'content'               => 'Ich war dabei, das war wunderbar.',
            'is_visitor_submission' => true,
            'is_published'          => true,
            'visitor_email'         => 'v@example.com',
            'visitor_token'         => null,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Ich war dabei, das war wunderbar.');
    }

    public function test_public_page_does_not_show_unconfirmed_visitor_memory(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'               => $page->user_id,
            'title'                 => 'Auto',
            'content'               => 'Unveröffentlichter Besucher-Inhalt.',
            'is_visitor_submission' => true,
            'is_published'          => false,
            'visitor_email'         => 'v@example.com',
            'visitor_token'         => 'sometoken',
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertDontSee('Unveröffentlichter Besucher-Inhalt.');
    }

    public function test_existing_published_customer_stories_still_render(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Auto',
            'content'      => 'Kundenseitig erstellte Erinnerung.',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Kundenseitig erstellte Erinnerung.');
    }
}
