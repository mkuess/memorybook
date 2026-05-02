<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
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

    public function test_public_memory_page_shows_published_stories(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Veröffentlichte Erinnerung',
            'content'      => 'Inhalt der Erinnerung.',
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertSee('Veröffentlichte Erinnerung');
        $response->assertSee('Inhalt der Erinnerung.');
    }

    public function test_public_memory_page_does_not_show_unpublished_stories(): void
    {
        $page = $this->makeVisiblePage();
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Unveröffentlichte Erinnerung',
            'content'      => 'Geheimer Inhalt.',
            'is_published' => false,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $response->assertDontSee('Unveröffentlichte Erinnerung');
        $response->assertDontSee('Geheimer Inhalt.');
    }

    public function test_stories_are_ordered_by_sort_order(): void
    {
        $page = $this->makeVisiblePage();

        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Zweite Geschichte',
            'content'      => 'Inhalt B.',
            'sort_order'   => 2,
            'is_published' => true,
        ]);
        $page->stories()->create([
            'user_id'      => $page->user_id,
            'title'        => 'Erste Geschichte',
            'content'      => 'Inhalt A.',
            'sort_order'   => 1,
            'is_published' => true,
        ]);

        $response = $this->get("/m/{$page->qrCode->short_code}");

        $response->assertOk();
        $content = $response->getContent();
        $this->assertLessThan(
            strpos($content, 'Zweite Geschichte'),
            strpos($content, 'Erste Geschichte'),
            'Erste Geschichte should appear before Zweite Geschichte'
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
                'title'        => 'Versteckte Erinnerung',
                'content'      => 'Versteckter Inhalt.',
                'is_published' => true,
            ]);

            $response = $this->get("/m/{$page->qrCode->short_code}");

            $response->assertOk();
            $response->assertDontSee('Versteckte Erinnerung');
        }
    }
}
