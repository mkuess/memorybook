<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPagePublishTest extends TestCase
{
    use RefreshDatabase;

    private function createPageForUser(User $user): MemoryPage
    {
        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function createOrderWithStatus(User $user, MemoryPage $page, string $status): Order
    {
        return Order::create([
            'user_id'                                => $user->id,
            'memory_page_id'                         => $page->id,
            'package'                                => 'basic',
            'status'                                 => $status,
            'billing_name'                           => 'Maria Muster',
            'billing_email'                          => 'maria@example.com',
            'billing_address'                        => 'Musterstraße 1',
            'billing_postal_code'                    => '1010',
            'billing_city'                           => 'Wien',
            'billing_country'                        => 'Österreich',
            'consent_confirmed_at'                   => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);
    }

    // --- publish ---

    public function test_owner_can_publish_when_paid_order_exists(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $this->createOrderWithStatus($user, $page, 'paid');

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertRedirect(route('memory-pages.edit', $page));
        $this->assertTrue($page->fresh()->is_published);
        $this->assertNotNull($page->fresh()->published_at);
    }

    public function test_customer_cannot_publish_when_no_order_exists(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();
        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_customer_cannot_publish_when_order_is_requested(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $this->createOrderWithStatus($user, $page, 'requested');

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();
        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_customer_cannot_publish_when_order_is_in_review(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $this->createOrderWithStatus($user, $page, 'in_review');

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();
        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_customer_cannot_publish_when_order_is_cancelled(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $this->createOrderWithStatus($user, $page, 'cancelled');

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();
        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_non_owner_cannot_publish(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);
        $this->createOrderWithStatus($owner, $page, 'paid');

        $response = $this->actingAs($other)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();
        $this->assertFalse($page->fresh()->is_published);
    }

    // --- unpublish ---

    public function test_owner_can_unpublish_when_paid_order_exists(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $this->createOrderWithStatus($user, $page, 'paid');
        $page->update(['is_published' => true, 'published_at' => now()]);

        $response = $this->actingAs($user)->post(route('memory-pages.unpublish', $page));

        $response->assertRedirect(route('memory-pages.edit', $page));
        $this->assertFalse($page->fresh()->is_published);
        $this->assertNull($page->fresh()->published_at);
    }

    public function test_customer_cannot_unpublish_when_no_paid_order(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $page->update(['is_published' => true, 'published_at' => now()]);

        $response = $this->actingAs($user)->post(route('memory-pages.unpublish', $page));

        $response->assertForbidden();
        $this->assertTrue($page->fresh()->is_published);
    }

    public function test_non_owner_cannot_unpublish(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);
        $this->createOrderWithStatus($owner, $page, 'paid');
        $page->update(['is_published' => true, 'published_at' => now()]);

        $response = $this->actingAs($other)->post(route('memory-pages.unpublish', $page));

        $response->assertForbidden();
        $this->assertTrue($page->fresh()->is_published);
    }

    // --- consent_confirmed_at preserved ---

    public function test_unpublishing_does_not_clear_consent_confirmed_at(): void
    {
        $user      = User::factory()->create();
        $page      = $this->createPageForUser($user);
        $consentAt = now()->subDay()->startOfSecond();
        $this->createOrderWithStatus($user, $page, 'paid');
        $page->update([
            'is_published'         => true,
            'published_at'         => now(),
            'consent_confirmed_at' => $consentAt,
        ]);

        $this->actingAs($user)->post(route('memory-pages.unpublish', $page));

        $page->refresh();
        $this->assertNotNull($page->consent_confirmed_at);
        $this->assertTrue($page->consent_confirmed_at->equalTo($consentAt));
    }

    // --- publication authorization lives on order ---

    public function test_publication_authorization_is_stored_on_order(): void
    {
        $user  = User::factory()->create();
        $page  = $this->createPageForUser($user);
        $order = $this->createOrderWithStatus($user, $page, 'paid');

        $this->assertNotNull($order->publication_authorization_confirmed_at);
    }
}
