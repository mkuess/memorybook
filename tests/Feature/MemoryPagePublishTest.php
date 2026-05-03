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
            'slug'        => 'abcd1234',
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function createPaidOrder(User $user, MemoryPage $page): Order
    {
        return Order::create([
            'user_id'                                => $user->id,
            'memory_page_id'                         => $page->id,
            'package'                                => 'basic',
            'status'                                 => 'paid',
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

    public function test_owner_can_publish(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $response->assertRedirect(route('memory-pages.edit', $page));

        $page->refresh();
        $this->assertTrue($page->is_published);
        $this->assertNotNull($page->published_at);
    }

    public function test_non_owner_cannot_publish(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->actingAs($other)->post(route('memory-pages.publish', $page));

        $response->assertForbidden();

        $page->refresh();
        $this->assertFalse($page->is_published);
    }

    public function test_owner_can_unpublish(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $page->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('memory-pages.unpublish', $page));

        $response->assertRedirect(route('memory-pages.edit', $page));

        $page->refresh();
        $this->assertFalse($page->is_published);
        $this->assertNull($page->published_at);
    }

    public function test_unpublishing_does_not_clear_consent_confirmed_at(): void
    {
        $user      = User::factory()->create();
        $page      = $this->createPageForUser($user);
        $consentAt = now()->subDay()->startOfSecond();
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

    public function test_publication_authorization_is_stored_on_order_not_on_publish(): void
    {
        $user  = User::factory()->create();
        $page  = $this->createPageForUser($user);
        $order = $this->createPaidOrder($user, $page);

        $this->assertNotNull($order->publication_authorization_confirmed_at);

        $this->actingAs($user)->post(route('memory-pages.publish', $page));

        $page->refresh();
        $this->assertTrue($page->is_published);
    }
}
