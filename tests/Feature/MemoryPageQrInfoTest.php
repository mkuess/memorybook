<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemoryPageQrInfoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

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
        $response->assertSee(strtoupper($code));
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

    public function test_page_shows_memorybook_com_label(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('memorybook.com');
    }

    public function test_page_does_not_show_memorybook_at(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertDontSee('memorybook.at');
    }

    public function test_page_renders_qr_code_image_tag(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('<img', false);
        $page->qrCode->refresh();
        $this->assertNotNull($page->qrCode->png_path);
    }

    public function test_short_code_displayed_uppercase(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $code  = $page->qrCode->short_code;

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee(strtoupper($code));
    }

    public function test_page_shows_png_download_button(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('PNG herunterladen');
        $response->assertSee(route('memory-pages.qr-code.download-png', $page), false);
    }

    public function test_page_shows_pdf_download_button(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('PDF herunterladen');
        $response->assertSee(route('memory-pages.qr-code.download-pdf', $page), false);
    }

    public function test_png_download_returns_image(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code.download-png', $page));

        $response->assertOk();
        $this->assertStringContainsString('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.png', $response->headers->get('Content-Disposition'));
    }

    public function test_pdf_download_returns_pdf(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code.download-pdf', $page));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_non_owner_cannot_download_png(): void
    {
        $page  = $this->makePage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.qr-code.download-png', $page));

        $response->assertForbidden();
    }

    public function test_non_owner_cannot_download_pdf(): void
    {
        $page  = $this->makePage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.qr-code.download-pdf', $page));

        $response->assertForbidden();
    }

    // --- visibility privacy warning ---

    public function test_qr_code_page_shows_private_warning_when_visibility_is_private(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $page->update(['visibility' => 'private']);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('Profilseite ist privat');
    }

    public function test_qr_code_page_shows_explanatory_warning_text_when_private(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $page->update(['visibility' => 'private']);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('Diese Profilseite ist derzeit nicht öffentlich aufrufbar, weil die Sichtbarkeit auf Privat gestellt ist.');
        $response->assertSee('damit der QR-Code für Besucher funktioniert.');
    }

    public function test_qr_code_page_does_not_show_private_warning_when_visibility_is_link(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $page->update(['visibility' => 'link']);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertDontSee('Profilseite ist privat');
    }

    public function test_qr_code_page_does_not_show_private_warning_when_visibility_is_public(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $page->update(['visibility' => 'public']);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertDontSee('Profilseite ist privat');
    }

    public function test_qr_code_still_renders_for_owner_when_visibility_is_private(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);
        $page->update(['visibility' => 'private']);

        $response = $this->actingAs($owner)->get(route('memory-pages.qr-code', $page));

        $response->assertOk();
        $response->assertSee('QR-Code');
        $response->assertSee($page->person_name);
    }

    public function test_dashboard_links_to_qr_code_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        Order::create([
            'user_id'         => $owner->id,
            'memory_page_id'  => $page->id,
            'status'          => 'paid',
            'package'         => 'basic',
            'billing_name'    => 'Test User',
            'billing_email'   => $owner->email,
            'billing_address' => 'Teststr. 1',
            'billing_city'    => 'Berlin',
            'billing_postal_code' => '10115',
            'billing_country' => 'DE',
            'amount_cents'    => 4900,
            'currency'        => 'EUR',
        ]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.qr-code', $page));
    }
}
