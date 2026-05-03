<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\QrCode;
use App\Models\User;
use App\Services\QrCodeImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemoryPageQrCodeTest extends TestCase
{
    use RefreshDatabase;

    private function createPage(array $attrs = []): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create(array_merge([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ], $attrs));
    }

    public function test_creating_a_memory_page_also_creates_one_qr_code_record(): void
    {
        $page = $this->createPage();

        $this->assertNotNull($page->qrCode);
        $this->assertInstanceOf(QrCode::class, $page->qrCode);
        $this->assertDatabaseHas('qr_codes', ['memory_page_id' => $page->id]);
    }

    public function test_qr_code_short_code_is_8_characters(): void
    {
        $page = $this->createPage();

        $this->assertEquals(8, strlen($page->qrCode->short_code));
    }

    public function test_qr_code_short_code_is_unique(): void
    {
        $page1 = $this->createPage();
        $page2 = $this->createPage();

        $this->assertNotEquals($page1->qrCode->short_code, $page2->qrCode->short_code);
    }

    public function test_newly_generated_short_codes_are_uppercase_letters_only(): void
    {
        $page = $this->createPage();
        $code = $page->qrCode->short_code;

        $this->assertMatchesRegularExpression('/^[A-Z]{8}$/', $code,
            "Short code '{$code}' should contain only uppercase A-Z letters.");
    }

    public function test_editing_a_memory_page_does_not_change_the_qr_code_short_code(): void
    {
        $user = User::factory()->create();
        $page = $this->createPage(['user_id' => $user->id]);

        $originalShortCode = $page->qrCode->short_code;

        $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
        ]);

        $page->refresh();
        $this->assertEquals($originalShortCode, $page->qrCode->short_code);
    }

    public function test_qr_code_image_service_generates_and_stores_png(): void
    {
        Storage::fake('public');

        $page    = $this->createPage();
        $qr      = $page->qrCode;
        $url     = route('memory-pages.public', $qr->short_code);
        $service = app(QrCodeImageService::class);

        $path = $service->generateAndStore($qr, $url);

        Storage::disk('public')->assertExists($path);
    }

    public function test_qr_code_record_stores_png_path_after_generation(): void
    {
        Storage::fake('public');

        $page    = $this->createPage();
        $qr      = $page->qrCode;
        $url     = route('memory-pages.public', $qr->short_code);
        $service = app(QrCodeImageService::class);

        $service->generateAndStore($qr, $url);
        $qr->refresh();

        $this->assertNotNull($qr->png_path);
        $this->assertStringStartsWith('qrcodes/', $qr->png_path);
        $this->assertStringEndsWith('.png', $qr->png_path);
    }

    public function test_ensure_image_exists_does_not_regenerate_if_file_already_present(): void
    {
        Storage::fake('public');

        $page    = $this->createPage();
        $qr      = $page->qrCode;
        $url     = route('memory-pages.public', $qr->short_code);
        $service = app(QrCodeImageService::class);

        $service->generateAndStore($qr, $url);
        $qr->refresh();
        $firstPath = $qr->png_path;

        $service->ensureImageExists($qr, $url);
        $qr->refresh();

        $this->assertEquals($firstPath, $qr->png_path);
    }
}
