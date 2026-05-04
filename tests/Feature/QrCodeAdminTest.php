<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\QrCode;
use App\Models\User;
use App\Services\QrCodeImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QrCodeAdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeUserWithPage(): array
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Test Person',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);

        return [$user, $page, $page->qrCode];
    }

    // --- short code generation ---

    public function test_generated_short_codes_are_uppercase(): void
    {
        [, , $qr] = $this->makeUserWithPage();

        $this->assertMatchesRegularExpression('/^[A-Z]{8}$/', $qr->short_code);
    }

    // --- case-insensitive public URL resolution ---

    public function test_public_page_resolves_with_uppercase_short_code(): void
    {
        [, $page, $qr] = $this->makeUserWithPage();

        $response = $this->get('/m/' . strtoupper($qr->short_code));

        $response->assertOk();
        $response->assertSee($page->person_name);
    }

    public function test_public_page_resolves_with_lowercase_short_code(): void
    {
        [, $page, $qr] = $this->makeUserWithPage();

        $response = $this->get('/m/' . strtolower($qr->short_code));

        $response->assertOk();
        $response->assertSee($page->person_name);
    }

    // --- admin view page ---

    public function test_admin_can_access_qr_code_view_page(): void
    {
        $admin = $this->makeAdmin();
        [, , $qr] = $this->makeUserWithPage();

        $response = $this->actingAs($admin)->get("/admin/qr-codes/{$qr->id}");

        $response->assertOk();
    }

    public function test_admin_qr_view_shows_regenerate_button(): void
    {
        $admin = $this->makeAdmin();
        [, , $qr] = $this->makeUserWithPage();

        $response = $this->actingAs($admin)->get("/admin/qr-codes/{$qr->id}");

        $response->assertOk();
        $response->assertSee('QR-Code neu generieren');
    }

    public function test_regular_user_cannot_access_admin_qr_view(): void
    {
        [$user, , $qr] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get("/admin/qr-codes/{$qr->id}");

        $response->assertForbidden();
    }

    // --- regenerate action ---

    public function test_regenerate_stores_png_file(): void
    {
        Storage::fake('public');

        [, $page, $qr] = $this->makeUserWithPage();
        $url            = route('memory-pages.public', $qr->short_code);

        app(QrCodeImageService::class)->generateAndStore($qr, $url);

        $qr->refresh();
        $this->assertNotNull($qr->png_path);
        Storage::disk('public')->assertExists($qr->png_path);
    }

    public function test_regenerate_overwrites_existing_file(): void
    {
        Storage::fake('public');

        [, $page, $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        $service = app(QrCodeImageService::class);
        $service->generateAndStore($qr, $url);
        $first = Storage::disk('public')->get($qr->fresh()->png_path);

        // second call should overwrite cleanly
        $service->generateAndStore($qr, $url);
        $second = Storage::disk('public')->get($qr->fresh()->png_path);

        $this->assertNotNull($second);
        $this->assertGreaterThan(1000, strlen($second));
    }

    public function test_generated_png_is_valid_png(): void
    {
        Storage::fake('public');

        [, $page, $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        app(QrCodeImageService::class)->generateAndStore($qr, $url);

        $qr->refresh();
        $bytes = Storage::disk('public')->get($qr->png_path);

        // PNG magic bytes: \x89PNG
        $this->assertStringStartsWith("\x89PNG", $bytes);
    }

    public function test_generated_png_filename_uses_uppercase_code(): void
    {
        Storage::fake('public');

        [, , $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        app(QrCodeImageService::class)->generateAndStore($qr, $url);

        $qr->refresh();
        $this->assertSame('qrcodes/' . strtoupper($qr->short_code) . '.png', $qr->png_path);
    }

    // --- domain text in PNG ---

    public function test_generate_labeled_png_returns_valid_png(): void
    {
        Storage::fake('public');

        [, , $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        $png = app(QrCodeImageService::class)->generateLabeledPng($qr, $url);

        $this->assertStringStartsWith("\x89PNG", $png);
        $this->assertGreaterThan(1000, strlen($png));
    }

    // --- buildRawQrPng ---

    public function test_build_raw_qr_png_returns_valid_png(): void
    {
        [, , $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        $png = app(QrCodeImageService::class)->buildRawQrPng($url);

        $this->assertStringStartsWith("\x89PNG", $png);
        $this->assertGreaterThan(100, strlen($png));
    }

    // --- PDF download (two-card layout) ---

    public function test_pdf_download_returns_pdf_content_type(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(
            route('memory-pages.qr-code.download-pdf', $page)
        );

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }

    public function test_pdf_download_contains_uppercase_code_in_filename(): void
    {
        [$user, $page, $qr] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(
            route('memory-pages.qr-code.download-pdf', $page)
        );

        $response->assertOk();
        $this->assertStringContainsString(
            strtoupper($qr->short_code),
            $response->headers->get('Content-Disposition')
        );
    }

    public function test_pdf_template_contains_both_card_sections(): void
    {
        [, , $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        $rawQrB64 = base64_encode(app(QrCodeImageService::class)->buildRawQrPng($url));
        $logoPath = public_path('images/memorybook-logo.png');
        $logoB64  = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : '';
        $code     = strtoupper($qr->short_code);

        $html = view('memory-pages.qr-download-pdf', compact('rawQrB64', 'logoB64', 'code'))->render();

        $this->assertStringContainsString('ls-wrap', $html);
        $this->assertStringContainsString('pt-wrap', $html);
        $this->assertStringContainsString('memorybook.at', $html);
        $this->assertStringContainsString(strtoupper($qr->short_code), $html);
    }

    public function test_pdf_template_does_not_contain_standalone_extra_text_block(): void
    {
        [, , $qr] = $this->makeUserWithPage();
        $url = route('memory-pages.public', $qr->short_code);

        $rawQrB64 = base64_encode(app(QrCodeImageService::class)->buildRawQrPng($url));
        $logoB64  = '';
        $code     = strtoupper($qr->short_code);

        $html = view('memory-pages.qr-download-pdf', compact('rawQrB64', 'logoB64', 'code'))->render();

        // No standalone .domain / .code paragraph outside the card divs
        $this->assertStringNotContainsString('memorybook.com', $html);
    }
}
