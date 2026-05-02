<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageQrInfoTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    public function test_owner_can_view_qr_code_info_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
    }

    public function test_non_owner_gets_403(): void
    {
        $page  = $this->makePage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.qr-code', $page));

        $response->assertForbidden();
    }

    public function test_page_shows_short_code(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $code  = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee($code);
    }

    public function test_page_shows_public_url(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $code  = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee("/m/{$code}");
    }

    public function test_dashboard_links_to_qr_code_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.qr-code', $page));
    }
}
