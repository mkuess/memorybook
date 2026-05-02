<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_editing_a_memory_page_does_not_change_the_qr_code_short_code(): void
    {
        $user = User::factory()->create();
        $page = $this->createPage(['user_id' => $user->id]);

        $originalShortCode = $page->qrCode->short_code;

        $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
            'visibility'  => 'private',
        ]);

        $page->refresh();
        $this->assertEquals($originalShortCode, $page->qrCode->short_code);
    }
}
