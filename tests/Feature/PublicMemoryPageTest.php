<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemoryPageTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(array $attrs = []): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create(array_merge([
            'user_id'      => $user->id,
            'slug'         => 'testslug',
            'person_name'  => 'Max Mustermann',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ], $attrs));
    }

    private const UNAVAILABLE = 'Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.';

    public function test_published_link_page_is_visible(): void
    {
        $this->makePage(['visibility' => 'link']);

        $response = $this->get('/m/testslug');

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_published_public_page_is_visible(): void
    {
        $this->makePage(['visibility' => 'public']);

        $response = $this->get('/m/testslug');

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_private_page_shows_calm_unavailable_message(): void
    {
        $this->makePage(['visibility' => 'private']);

        $response = $this->get('/m/testslug');

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_unpublished_page_shows_calm_unavailable_message(): void
    {
        $this->makePage(['is_published' => false]);

        $response = $this->get('/m/testslug');

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_locked_page_shows_calm_unavailable_message(): void
    {
        $this->makePage(['is_locked' => true]);

        $response = $this->get('/m/testslug');

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
