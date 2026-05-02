<?php

namespace Tests\Feature;

use App\Filament\Resources\ReportResource\Pages\EditReport;
use App\Models\MemoryPage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeReport(): Report
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);

        return Report::create([
            'memory_page_id' => $page->id,
            'reporter_email' => 'reporter@example.com',
            'reason'         => 'Unangemessene Inhalte',
            'description'    => 'Weitere Details zur Meldung.',
        ]);
    }

    public function test_admin_can_access_report_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_report_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/reports');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_report_list(): void
    {
        $response = $this->get('/admin/reports');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_report(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $report = $this->makeReport();

        $response = $this->actingAs($admin)->get("/admin/reports/{$report->id}");

        $response->assertOk();
    }

    public function test_admin_can_update_report_status(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $report = $this->makeReport();

        $this->assertEquals('open', $report->status);

        Livewire::actingAs($admin)
            ->test(EditReport::class, ['record' => $report->getRouteKey()])
            ->fillForm(['status' => 'resolved'])
            ->call('save');

        $this->assertEquals('resolved', $report->fresh()->status);
    }
}
