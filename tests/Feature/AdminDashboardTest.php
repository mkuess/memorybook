<?php

namespace Tests\Feature;

use App\Filament\Widgets\PendingWorkOverview;
use App\Models\MemoryPage;
use App\Models\PlaqueOrder;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeMemoryPage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create(['role' => 'user']);

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Test Person',
        ]);
    }

    public function test_admin_can_view_admin_dashboard(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    private function makeReport(MemoryPage $page, string $status = 'open'): Report
    {
        return Report::create([
            'memory_page_id' => $page->id,
            'reporter_email' => 'reporter@example.com',
            'reason'         => 'inappropriate_content',
            'description'    => 'Test report.',
            'status'         => $status,
        ]);
    }

    private function makePlaqueOrder(MemoryPage $page, User $owner, string $status = 'requested'): PlaqueOrder
    {
        return PlaqueOrder::create([
            'memory_page_id'   => $page->id,
            'user_id'          => $owner->id,
            'contact_name'     => 'Erika Mustermann',
            'contact_email'    => 'erika@example.com',
            'shipping_address' => 'Musterstraße 1, 12345 Musterstadt',
            'status'           => $status,
        ]);
    }

    public function test_dashboard_shows_open_report_count(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makeMemoryPage();

        $this->makeReport($page, 'open');
        $this->makeReport($page, 'open');
        $this->makeReport($page, 'resolved');

        Livewire::actingAs($admin)
            ->test(PendingWorkOverview::class)
            ->assertSee('Offene Meldungen')
            ->assertSee('2');
    }

    public function test_dashboard_shows_open_plaque_order_count(): void
    {
        $admin = $this->makeAdmin();
        $owner = User::factory()->create(['role' => 'user']);
        $page  = $this->makeMemoryPage($owner);

        $this->makePlaqueOrder($page, $owner, 'requested');
        $this->makePlaqueOrder($page, $owner, 'in_review');
        $this->makePlaqueOrder($page, $owner, 'shipped');

        Livewire::actingAs($admin)
            ->test(PendingWorkOverview::class)
            ->assertSee('Plaketten offen')
            ->assertSee('2');
    }

    public function test_dashboard_shows_recent_memory_page_count(): void
    {
        $admin = $this->makeAdmin();
        $owner = User::factory()->create(['role' => 'user']);

        // 3 pages created now (within last 7 days)
        $this->makeMemoryPage($owner);
        $this->makeMemoryPage($owner);
        $this->makeMemoryPage($owner);

        // 1 old page (outside window) — update timestamp after creation
        $old = $this->makeMemoryPage($owner);
        $old->update(['created_at' => now()->subDays(10)]);

        Livewire::actingAs($admin)
            ->test(PendingWorkOverview::class)
            ->assertSee('Neue Erinnerungsseiten')
            ->assertSee('3');
    }

    public function test_dashboard_shows_locked_memory_page_count(): void
    {
        $admin = $this->makeAdmin();
        $owner = User::factory()->create(['role' => 'user']);

        $page1 = $this->makeMemoryPage($owner);
        $page2 = $this->makeMemoryPage($owner);
        $page3 = $this->makeMemoryPage($owner);

        $page1->update(['is_locked' => true]);
        $page2->update(['is_locked' => true]);

        Livewire::actingAs($admin)
            ->test(PendingWorkOverview::class)
            ->assertSee('Gesperrte Seiten')
            ->assertSee('2');
    }
}
