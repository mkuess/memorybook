<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMemoryPageOwnerPreviewTest extends TestCase
{
    use RefreshDatabase;

    private const UNAVAILABLE = 'Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.';
    private const OWNER_NOTICE = 'Vorschau: Diese Seite ist derzeit nicht öffentlich sichtbar.';
    private const ADMIN_NOTICE = 'Admin-Vorschau: Diese Seite ist öffentlich möglicherweise nicht sichtbar.';

    private function makeOwnerAndPage(array $pageAttrs = []): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create(array_merge([
            'user_id'      => $owner->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max Mustermann',
            'is_published' => false,
            'is_locked'    => false,
            'visibility'   => 'private',
        ], $pageAttrs));

        return [$owner, $page];
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    // --- owner preview ---

    public function test_owner_can_view_own_unpublished_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => false, 'visibility' => 'link']);
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_owner_can_view_own_private_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'private']);
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_owner_cannot_view_own_locked_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'link', 'is_locked' => true]);
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_owner_preview_shows_preview_notice(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => false, 'visibility' => 'private']);
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::OWNER_NOTICE);
    }

    public function test_owner_does_not_see_preview_notice_when_page_is_publicly_visible(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'link', 'is_locked' => false]);
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
        $response->assertDontSee(self::OWNER_NOTICE);
    }

    // --- admin preview ---

    public function test_admin_can_view_locked_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'link', 'is_locked' => true]);
        $admin    = $this->makeAdmin();
        $code     = $page->qrCode->short_code;

        $response = $this->actingAs($admin)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_admin_can_view_unpublished_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => false, 'visibility' => 'private']);
        $admin    = $this->makeAdmin();
        $code     = $page->qrCode->short_code;

        $response = $this->actingAs($admin)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }

    public function test_admin_preview_shows_admin_notice(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => false, 'is_locked' => true]);
        $admin    = $this->makeAdmin();
        $code     = $page->qrCode->short_code;

        $response = $this->actingAs($admin)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::ADMIN_NOTICE);
    }

    // --- guest / non-owner ---

    public function test_guest_cannot_view_unpublished_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => false, 'visibility' => 'link']);
        $code     = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_guest_cannot_view_private_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'private']);
        $code     = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_non_owner_cannot_view_private_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'private']);
        $other    = User::factory()->create(['role' => 'user']);
        $code     = $page->qrCode->short_code;

        $response = $this->actingAs($other)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    public function test_non_owner_cannot_view_unpublished_page(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => false, 'visibility' => 'link']);
        $other    = User::factory()->create(['role' => 'user']);
        $code     = $page->qrCode->short_code;

        $response = $this->actingAs($other)->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee(self::UNAVAILABLE);
        $response->assertDontSee('Max Mustermann');
    }

    // --- public route still works ---

    public function test_public_published_page_still_visible_to_guest(): void
    {
        [, $page] = $this->makeOwnerAndPage(['is_published' => true, 'visibility' => 'link', 'is_locked' => false]);
        $code     = $page->qrCode->short_code;

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Max Mustermann');
        $response->assertDontSee(self::UNAVAILABLE);
    }
}
