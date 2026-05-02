<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\QrCode;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrCodeTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(string $slug = 'abc12345'): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => $slug,
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function qrAttributes(MemoryPage $page, array $overrides = []): array
    {
        return array_merge([
            'memory_page_id' => $page->id,
            'short_code'     => 'AAAA1111',
        ], $overrides);
    }

    public function test_memory_page_can_have_one_qr_code(): void
    {
        $page = $this->makeMemoryPage();

        QrCode::create($this->qrAttributes($page));

        $this->assertInstanceOf(QrCode::class, $page->qrCode);
        $this->assertEquals($page->id, $page->qrCode->memory_page_id);
    }

    public function test_qr_code_belongs_to_memory_page(): void
    {
        $page = $this->makeMemoryPage();

        $qr = QrCode::create($this->qrAttributes($page));

        $this->assertInstanceOf(MemoryPage::class, $qr->memoryPage);
        $this->assertEquals($page->id, $qr->memoryPage->id);
    }

    public function test_default_scan_count_is_0(): void
    {
        $page = $this->makeMemoryPage();

        $qr = QrCode::create($this->qrAttributes($page));

        $this->assertSame(0, $qr->scan_count);
    }

    public function test_short_code_must_be_unique(): void
    {
        $this->expectException(QueryException::class);

        $page1 = $this->makeMemoryPage('page0001');
        $page2 = $this->makeMemoryPage('page0002');

        QrCode::create($this->qrAttributes($page1, ['short_code' => 'SAME1234']));
        QrCode::create($this->qrAttributes($page2, ['short_code' => 'SAME1234']));
    }

    public function test_memory_page_id_must_be_unique(): void
    {
        $this->expectException(QueryException::class);

        $page = $this->makeMemoryPage();

        QrCode::create($this->qrAttributes($page, ['short_code' => 'AAAA1111']));
        QrCode::create($this->qrAttributes($page, ['short_code' => 'BBBB2222']));
    }
}
