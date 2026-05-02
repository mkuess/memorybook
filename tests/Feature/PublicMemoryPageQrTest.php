<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemoryPageQrTest extends TestCase
{
    use RefreshDatabase;

    private const UNAVAILABLE = 'Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.';

    private function makePublishedPage(array $overrides = []): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create(array_merge([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max Mustermann',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ], $overrides));
    }

    public function test_published_page_is_visible_through_qr_code_short_code(): void
    {
        $page = $this->makePublishedPage();
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_scan_count_increments_for_known_qr_code(): void
    {
        $page = $this->makePublishedPage();
        $qr   = $page->qrCode;

        $this->assertEquals(0, $qr->scan_count);

        $this->get("/m/{$qr->short_code}");
        $this->get("/m/{$qr->short_code}");

        $this->assertEquals(2, $qr->fresh()->scan_count);
    }

    public function test_unknown_code_does_not_error_and_shows_unavailable_message(): void
    {
        $response = $this->get('/m/xxxxxxxx');

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
    }

    public function test_private_page_through_known_qr_code_shows_unavailable_message(): void
    {
        $page = $this->makePublishedPage(['visibility' => 'private']);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_unpublished_page_through_known_qr_code_shows_unavailable_message(): void
    {
        $page = $this->makePublishedPage(['is_published' => false]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_locked_page_through_known_qr_code_shows_unavailable_message(): void
    {
        $page = $this->makePublishedPage(['is_locked' => true]);
        $code = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }
}
