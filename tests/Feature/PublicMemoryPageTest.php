<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemoryPageTest extends TestCase
{
    use RefreshDatabase;

    private const UNAVAILABLE = 'Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.';

    private function makePage(array $attrs = []): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create(array_merge([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max Mustermann',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ], $attrs));
    }

    public function test_published_link_page_is_visible(): void
    {
        $page = $this->makePage(['visibility' => 'link']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_published_public_page_is_visible(): void
    {
        $page = $this->makePage(['visibility' => 'public']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_private_page_shows_calm_unavailable_message(): void
    {
        $page = $this->makePage(['visibility' => 'private']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_unpublished_page_shows_calm_unavailable_message(): void
    {
        $page = $this->makePage(['is_published' => false]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_locked_page_shows_calm_unavailable_message(): void
    {
        $page = $this->makePage(['is_locked' => true]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_unknown_slug_shows_calm_unavailable_message_not_404(): void
    {
        $response = $this->get('/m/doesnotexist');

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
    }
}
