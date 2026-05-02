<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(): MemoryPage
    {
        $owner = User::factory()->create(['role' => 'user']);

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    public function test_admin_can_access_qr_code_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/qr-codes');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_qr_code_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/qr-codes');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_qr_code_list(): void
    {
        $response = $this->get('/admin/qr-codes');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_qr_code_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();
        $qr    = $page->qrCode;

        $response = $this->actingAs($admin)->get("/admin/qr-codes/{$qr->id}");

        $response->assertOk();
    }

    public function test_view_page_shows_short_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();
        $qr    = $page->qrCode;

        $response = $this->actingAs($admin)->get("/admin/qr-codes/{$qr->id}");

        $response->assertOk();
        $response->assertSee($qr->short_code);
    }

    public function test_view_page_shows_public_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();
        $qr    = $page->qrCode;

        $response = $this->actingAs($admin)->get("/admin/qr-codes/{$qr->id}");

        $response->assertOk();
        $response->assertSee("/m/{$qr->short_code}");
    }
}
