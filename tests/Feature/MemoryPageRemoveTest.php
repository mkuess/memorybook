<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageRemoveTest extends TestCase
{
    use RefreshDatabase;

    private function makeOwnerAndPage(array $attrs = []): array
    {
        $owner = User::factory()->create();
        $page  = MemoryPage::create(array_merge([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ], $attrs));

        return [$owner, $page];
    }

    // --- edit page shows the remove button ---

    public function test_customer_edit_page_shows_profilseite_entfernen(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Profilseite löschen');
        $response->assertSee(route('memory-pages.remove.confirm', $page), false);
    }

    // --- confirmation page ---

    public function test_owner_can_access_confirmation_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('memory-pages.remove.confirm', $page));

        $response->assertOk();
        $response->assertSee('Profilseite wirklich entfernen?');
        $response->assertSee('Ja, Profilseite löschen');
        $response->assertSee('Abbrechen');
    }

    public function test_non_owner_cannot_access_confirmation_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.remove.confirm', $page));

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_confirmation_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->get(route('memory-pages.remove.confirm', $page));

        $response->assertRedirect(route('login'));
    }

    public function test_already_removed_page_confirmation_returns_404(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('memory-pages.remove.confirm', $page));

        $response->assertNotFound();
    }

    // --- removal action ---

    public function test_owner_can_remove_memory_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $response->assertRedirect(route('dashboard'));
    }

    public function test_non_owner_cannot_remove_memory_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->post(route('memory-pages.remove', $page));

        $response->assertForbidden();
    }

    public function test_guest_cannot_remove_memory_page(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->post(route('memory-pages.remove', $page));

        $response->assertRedirect(route('login'));
    }

    public function test_removing_sets_is_published_false(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['is_published' => true]);

        $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $this->assertFalse($page->fresh()->is_published);
    }

    public function test_removing_sets_visibility_private(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage(['visibility' => 'public']);

        $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $this->assertSame('private', $page->fresh()->visibility);
    }

    public function test_removing_sets_customer_removed_at(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $this->assertNotNull($page->fresh()->customer_removed_at);
    }

    public function test_removal_redirects_with_success_message(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('removed_success', 'Profilseite wurde entfernt.');
    }

    // --- dashboard hides removed pages ---

    public function test_removed_memory_page_disappears_from_dashboard(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee($page->person_name);
    }

    public function test_non_removed_memory_page_still_appears_on_dashboard(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($page->person_name);
    }

    // --- edit page blocked for removed pages ---

    public function test_removed_memory_page_edit_page_returns_404(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertNotFound();
    }

    // --- public profile unavailable ---

    public function test_removed_memory_page_is_not_publicly_visible(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage([
            'is_published' => true,
            'visibility'   => 'public',
        ]);

        $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $code = $page->qrCode->short_code;

        auth()->logout();
        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertSee('Diese Erinnerungsseite ist derzeit nicht öffentlich verfügbar.');
        $response->assertDontSee($page->person_name);
    }

    // --- admin still sees removed pages via Filament model query ---

    public function test_admin_can_still_query_removed_memory_page_via_model(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $found = MemoryPage::find($page->id);

        $this->assertNotNull($found);
        $this->assertNotNull($found->customer_removed_at);
    }

    public function test_removed_page_is_not_hard_deleted(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->actingAs($owner)->post(route('memory-pages.remove', $page));

        $this->assertDatabaseHas('memory_pages', ['id' => $page->id]);
        $this->assertNull(MemoryPage::find($page->id)->deleted_at);
    }

    // --- admin restore ---

    public function test_admin_can_restore_customer_removed_at_to_null(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $page->update(['customer_removed_at' => null]);

        $this->assertNull($page->fresh()->customer_removed_at);
    }

    public function test_restored_page_reappears_on_dashboard(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $page->update(['customer_removed_at' => null]);

        $response = $this->actingAs($owner)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee($page->person_name);
    }

    public function test_restored_page_edit_is_accessible_again(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $page->update(['customer_removed_at' => null]);

        $response = $this->actingAs($owner)->get(route('memory-pages.edit', $page));

        $response->assertOk();
    }

    // --- is_customer_removed helper ---

    public function test_is_customer_removed_returns_true_when_set(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();
        $page->update(['customer_removed_at' => now()]);

        $this->assertTrue($page->fresh()->isCustomerRemoved());
    }

    public function test_is_customer_removed_returns_false_when_not_set(): void
    {
        [$owner, $page] = $this->makeOwnerAndPage();

        $this->assertFalse($page->isCustomerRemoved());
    }
}
