<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnerAndPage(): array
    {
        $owner = User::factory()->create(['role' => 'user']);
        $page  = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);

        return [$owner, $page];
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'package'             => 'basic',
            'billing_name'        => 'Maria Muster',
            'billing_email'       => 'maria@example.com',
            'billing_address'     => 'Musterstraße 1',
            'billing_postal_code' => '1010',
            'billing_city'        => 'Wien',
            'billing_country'     => 'Österreich',
            'pub_auth'            => '1',
            'consent'             => '1',
        ], $overrides);
    }

    // --- access ---

    public function test_owner_can_access_checkout_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('memory-pages.checkout', $page));

        $response->assertOk();
        $response->assertSee('Paket auswählen');
    }

    public function test_non_owner_cannot_access_checkout_page(): void
    {
        [, $page] = $this->makeOwnerAndPage();
        $other    = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.checkout', $page));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_checkout_page(): void
    {
        [, $page] = $this->makeOwnerAndPage();

        $response = $this->get(route('memory-pages.checkout', $page));

        $response->assertRedirect(route('login'));
    }

    // --- validation ---

    public function test_checkout_requires_package(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['package' => '']));

        $response->assertSessionHasErrors('package');
    }

    public function test_checkout_rejects_invalid_package(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['package' => 'premium']));

        $response->assertSessionHasErrors('package');
    }

    public function test_checkout_requires_billing_name(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_name' => '']));

        $response->assertSessionHasErrors('billing_name');
    }

    public function test_checkout_requires_billing_email(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_email' => '']));

        $response->assertSessionHasErrors('billing_email');
    }

    public function test_checkout_requires_billing_address(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_address' => '']));

        $response->assertSessionHasErrors('billing_address');
    }

    public function test_checkout_requires_billing_postal_code(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_postal_code' => '']));

        $response->assertSessionHasErrors('billing_postal_code');
    }

    public function test_checkout_requires_billing_city(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_city' => '']));

        $response->assertSessionHasErrors('billing_city');
    }

    public function test_checkout_requires_billing_country(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['billing_country' => '']));

        $response->assertSessionHasErrors('billing_country');
    }

    public function test_checkout_requires_consent_checkbox(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['consent' => '0']));

        $response->assertSessionHasErrors('consent');
    }

    // --- successful submission ---

    public function test_owner_can_submit_checkout(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response->assertRedirect(route('memory-pages.checkout.confirmed', $page));
    }

    public function test_created_order_belongs_to_user(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame($owner->id, $order->user_id);
    }

    public function test_created_order_belongs_to_memory_page(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame($page->id, $order->memory_page_id);
    }

    public function test_created_order_has_status_paid(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame('paid', $order->status);
    }

    public function test_checkout_page_prefills_billing_name_from_user(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $owner->update(['name' => 'Prefill Name']);

        $response = $this->actingAs($owner)->get(route('memory-pages.checkout', $page));

        $response->assertOk();
        $response->assertSee('Prefill Name');
    }

    public function test_checkout_page_prefills_billing_email_from_user(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $owner->update(['email' => 'prefill@example.com']);

        $response = $this->actingAs($owner)->get(route('memory-pages.checkout', $page));

        $response->assertOk();
        $response->assertSee('prefill@example.com');
    }

    public function test_checkout_requires_publication_authorization_checkbox(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload(['pub_auth' => '0']));

        $response->assertSessionHasErrors('pub_auth');
    }

    public function test_order_stores_publication_authorization_timestamp(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $order = \App\Models\Order::first();
        $this->assertNotNull($order?->publication_authorization_confirmed_at);
    }

    public function test_created_order_stores_consent_timestamp(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $order = Order::first();
        $this->assertNotNull($order?->consent_confirmed_at);
    }

    public function test_order_submission_does_not_fail_if_email_notification_is_unavailable(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP error'));

        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response->assertRedirect(route('memory-pages.checkout.confirmed', $page));
        $this->assertDatabaseCount('orders', 1);
    }

    // --- confirmation page ---

    public function test_owner_can_view_confirmation_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)
            ->get(route('memory-pages.checkout.confirmed', $page));

        $response->assertOk();
        $response->assertSee('Bestellung eingegangen');
        $response->assertSee('Du kannst deine Profilseite jetzt aktivieren.');
    }

    public function test_after_checkout_edit_page_does_not_show_wird_geprüft(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertDontSee('wird geprüft');
    }

    public function test_after_checkout_memory_page_is_published(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $this->assertTrue($page->fresh()->is_published);
    }

    public function test_after_checkout_edit_page_shows_profilseite_verwalten(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Profilseite verwalten');
        $response->assertSee('Profilseite deaktivieren');
        $response->assertDontSee('Bestellung in Bearbeitung');
    }

    public function test_after_checkout_edit_page_shows_online(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Online');
    }

    public function test_customer_can_deactivate_profile_after_checkout(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $this->actingAs($owner)
            ->post(route('memory-pages.unpublish', $page))
            ->assertRedirect();

        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_customer_can_activate_profile_after_deactivation(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $this->actingAs($owner)->post(route('memory-pages.unpublish', $page));
        $this->actingAs($owner)->post(route('memory-pages.publish', $page));

        $this->assertTrue($page->fresh()->is_published);
    }

    public function test_public_page_visible_after_checkout_when_active(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['visibility' => 'public']);

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $code = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee($page->person_name);
    }

    public function test_public_page_unavailable_when_customer_deactivates(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['visibility' => 'public']);

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $this->actingAs($owner)->post(route('memory-pages.unpublish', $page));

        auth()->logout();

        $code = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertSee('nicht verfügbar');
        $response->assertDontSee($page->person_name);
    }

    public function test_admin_lock_overrides_customer_activation(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['visibility' => 'public']);

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $page->update(['is_locked' => true]);

        auth()->logout();

        $code = $page->qrCode->short_code;
        $response = $this->get("/m/{$code}");

        $response->assertSee('nicht verfügbar');
        $response->assertDontSee($page->person_name);
    }

    public function test_after_checkout_qr_code_link_is_visible(): void
    {
        Mail::fake();
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)
            ->post(route('memory-pages.checkout.store', $page), $this->validPayload());

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('QR-Code anzeigen');
    }

    public function test_before_checkout_qr_code_link_is_hidden(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertDontSee('QR-Code anzeigen');
    }

    // --- edit page link ---

    public function test_edit_page_shows_checkout_button(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Veröffentlichung bestellen');
        $response->assertSee(route('memory-pages.checkout', $page), false);
    }
}
