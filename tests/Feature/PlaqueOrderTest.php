<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\PlaqueOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaqueOrderTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(?User $user = null): MemoryPage
    {
        $user ??= User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function orderAttributes(MemoryPage $page, array $overrides = []): array
    {
        return array_merge([
            'memory_page_id'  => $page->id,
            'user_id'         => $page->user_id,
            'contact_name'    => 'Erika Mustermann',
            'contact_email'   => 'erika@example.com',
            'shipping_address' => 'Musterstraße 1, 12345 Musterstadt',
        ], $overrides);
    }

    public function test_memory_page_can_have_plaque_orders(): void
    {
        $page = $this->makeMemoryPage();

        PlaqueOrder::create($this->orderAttributes($page));
        PlaqueOrder::create($this->orderAttributes($page));

        $this->assertCount(2, $page->plaqueOrders);
    }

    public function test_user_can_have_plaque_orders(): void
    {
        $user  = User::factory()->create();
        $page1 = $this->makeMemoryPage($user);
        $page2 = $this->makeMemoryPage($user);

        PlaqueOrder::create($this->orderAttributes($page1));
        PlaqueOrder::create($this->orderAttributes($page2));

        $this->assertCount(2, $user->plaqueOrders);
    }

    public function test_plaque_order_belongs_to_memory_page(): void
    {
        $page  = $this->makeMemoryPage();
        $order = PlaqueOrder::create($this->orderAttributes($page));

        $this->assertInstanceOf(MemoryPage::class, $order->memoryPage);
        $this->assertEquals($page->id, $order->memoryPage->id);
    }

    public function test_plaque_order_belongs_to_user(): void
    {
        $page  = $this->makeMemoryPage();
        $order = PlaqueOrder::create($this->orderAttributes($page));

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($page->user_id, $order->user->id);
    }

    public function test_default_status_is_requested(): void
    {
        $page  = $this->makeMemoryPage();
        $order = PlaqueOrder::create($this->orderAttributes($page));

        $this->assertSame('requested', $order->status);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function test_status_can_store_all_intended_values(string $status): void
    {
        $page  = $this->makeMemoryPage();
        $order = PlaqueOrder::create($this->orderAttributes($page, ['status' => $status]));

        $this->assertSame($status, $order->status);
    }

    public static function statusProvider(): array
    {
        return [
            ['requested'],
            ['in_review'],
            ['in_production'],
            ['shipped'],
            ['done'],
            ['cancelled'],
        ];
    }
}
