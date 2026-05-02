<?php

namespace Tests\Feature;

use App\Filament\Resources\PlaqueOrderResource\Pages\EditPlaqueOrder;
use App\Models\MemoryPage;
use App\Models\PlaqueOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlaqueOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makePlaqueOrder(): PlaqueOrder
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);

        return PlaqueOrder::create([
            'memory_page_id'  => $page->id,
            'user_id'         => $owner->id,
            'contact_name'    => 'Erika Mustermann',
            'contact_email'   => 'erika@example.com',
            'shipping_address' => 'Musterstraße 1, 12345 Musterstadt',
        ]);
    }

    public function test_admin_can_access_plaque_order_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/plaque-orders');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_plaque_order_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/plaque-orders');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_plaque_order_list(): void
    {
        $response = $this->get('/admin/plaque-orders');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_plaque_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makePlaqueOrder();

        $response = $this->actingAs($admin)->get("/admin/plaque-orders/{$order->id}");

        $response->assertOk();
    }

    public function test_admin_can_update_plaque_order_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makePlaqueOrder();

        $this->assertEquals('requested', $order->status);

        Livewire::actingAs($admin)
            ->test(EditPlaqueOrder::class, ['record' => $order->getRouteKey()])
            ->fillForm(['status' => 'in_production'])
            ->call('save');

        $this->assertEquals('in_production', $order->fresh()->status);
    }
}
