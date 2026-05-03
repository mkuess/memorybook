<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);

        return Order::create(array_merge([
            'user_id'             => $user->id,
            'memory_page_id'      => $page->id,
            'package'             => 'basic',
            'billing_name'        => 'Maria Muster',
            'billing_email'       => 'maria@example.com',
            'billing_address'     => 'Musterstraße 1',
            'billing_postal_code' => '1010',
            'billing_city'        => 'Wien',
            'billing_country'     => 'Österreich',
            'consent_confirmed_at' => now(),
        ], $overrides));
    }

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_orders_list(): void
    {
        $admin = $this->makeAdmin();
        $this->makeOrder();

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_orders_admin_resource(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/orders');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_orders_admin_resource(): void
    {
        $response = $this->get('/admin/orders');

        $response->assertRedirect();
    }

    public function test_admin_can_view_order_details(): void
    {
        $admin = $this->makeAdmin();
        $order = $this->makeOrder();

        $response = $this->actingAs($admin)->get('/admin/orders/' . $order->id);

        $response->assertOk();
    }

    public function test_admin_can_change_order_status(): void
    {
        $admin = $this->makeAdmin();
        $order = $this->makeOrder();

        $this->assertSame('requested', $order->status);

        $order->update(['status' => 'in_review']);

        $this->assertSame('in_review', $order->fresh()->status);
    }
}
