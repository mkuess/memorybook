<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function reportAttributes(MemoryPage $page, array $overrides = []): array
    {
        return array_merge([
            'memory_page_id' => $page->id,
            'reporter_email' => 'reporter@example.com',
            'reason'         => 'inappropriate_content',
            'description'    => 'This page contains inappropriate content.',
        ], $overrides);
    }

    public function test_memory_page_can_have_reports(): void
    {
        $page = $this->makeMemoryPage();

        Report::create($this->reportAttributes($page));
        Report::create($this->reportAttributes($page, ['reporter_email' => 'other@example.com']));

        $this->assertCount(2, $page->reports);
    }

    public function test_report_belongs_to_memory_page(): void
    {
        $page   = $this->makeMemoryPage();
        $report = Report::create($this->reportAttributes($page));

        $this->assertInstanceOf(MemoryPage::class, $report->memoryPage);
        $this->assertEquals($page->id, $report->memoryPage->id);
    }

    public function test_default_status_is_open(): void
    {
        $page   = $this->makeMemoryPage();
        $report = Report::create($this->reportAttributes($page));

        $this->assertSame('open', $report->status);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function test_status_can_store_all_intended_values(string $status): void
    {
        $page   = $this->makeMemoryPage();
        $report = Report::create($this->reportAttributes($page, ['status' => $status]));

        $this->assertSame($status, $report->status);
    }

    public static function statusProvider(): array
    {
        return [
            ['open'],
            ['in_review'],
            ['resolved'],
            ['dismissed'],
        ];
    }
}
