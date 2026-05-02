<?php

namespace Tests\Feature;

use App\Filament\Resources\MemoryPageResource\Pages\CreateMemoryPage;
use App\Filament\Resources\MemoryPageResource\Pages\EditMemoryPage;
use App\Filament\Resources\MemoryPageResource\Pages\ViewMemoryPage;
use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MemoryPageResourceTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create(['role' => 'user']);

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    // --- list / access tests ---

    public function test_admin_can_access_memory_page_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/memory-pages');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_memory_page_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/memory-pages');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_memory_page_list(): void
    {
        $response = $this->get('/admin/memory-pages');

        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_memory_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
    }

    public function test_admin_can_access_edit_memory_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}/edit");

        $response->assertOk();
    }

    public function test_admin_can_edit_is_locked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $this->assertFalse($page->is_locked);

        Livewire::actingAs($admin)
            ->test(EditMemoryPage::class, ['record' => $page->getRouteKey()])
            ->fillForm(['is_locked' => true])
            ->call('save');

        $this->assertTrue($page->fresh()->is_locked);
    }

    // --- create tests ---

    public function test_admin_can_access_create_memory_page_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/memory-pages/create');

        $response->assertOk();
    }

    public function test_admin_can_create_a_memory_page_for_selected_user(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        Livewire::actingAs($admin)
            ->test(CreateMemoryPage::class)
            ->fillForm([
                'user_id'      => $targetUser->id,
                'person_name'  => 'Erika Musterfrau',
                'visibility'   => 'private',
                'is_published' => false,
                'is_locked'    => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('memory_pages', [
            'user_id'     => $targetUser->id,
            'person_name' => 'Erika Musterfrau',
        ]);
    }

    public function test_created_page_has_generated_slug(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        Livewire::actingAs($admin)
            ->test(CreateMemoryPage::class)
            ->fillForm([
                'user_id'     => $targetUser->id,
                'person_name' => 'Erika Musterfrau',
                'visibility'  => 'private',
            ])
            ->call('create');

        $page = MemoryPage::where('person_name', 'Erika Musterfrau')->first();

        $this->assertNotNull($page);
        $this->assertNotEmpty($page->slug);
    }

    public function test_created_page_has_qr_code_record(): void
    {
        $admin      = User::factory()->create(['role' => 'admin']);
        $targetUser = User::factory()->create(['role' => 'user']);

        Livewire::actingAs($admin)
            ->test(CreateMemoryPage::class)
            ->fillForm([
                'user_id'     => $targetUser->id,
                'person_name' => 'Erika Musterfrau',
                'visibility'  => 'private',
            ])
            ->call('create');

        $page = MemoryPage::where('person_name', 'Erika Musterfrau')->first();

        $this->assertNotNull($page);
        $this->assertNotNull($page->qrCode);
        $this->assertNotEmpty($page->qrCode->short_code);
    }

    public function test_normal_user_cannot_create_memory_page_through_filament(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/memory-pages/create');

        $response->assertForbidden();
    }

    // --- detail view tests ---

    public function test_detail_view_shows_public_url_when_qr_code_exists(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('/m/' . $page->qrCode->short_code);
    }

    public function test_detail_view_shows_fallback_text_when_qr_code_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $page->qrCode()->delete();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Noch kein QR-Code vorhanden');
    }

    public function test_detail_view_renders_public_url_as_link(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee(
            'href="' . url('/m/' . $page->qrCode->short_code) . '"',
            false
        );
    }

    public function test_detail_view_public_url_opens_in_new_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
    }

    // --- locked status display tests ---

    public function test_detail_view_shows_nicht_gesperrt_for_unlocked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $this->assertFalse($page->is_locked);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Nicht gesperrt');
    }

    public function test_detail_view_shows_gesperrt_for_locked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $page->update(['is_locked' => true]);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Gesperrt');
    }

    public function test_detail_view_shows_jetzt_blockieren_for_unlocked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $this->assertFalse($page->is_locked);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Jetzt blockieren');
    }

    public function test_detail_view_shows_jetzt_freigeben_for_locked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $page->update(['is_locked' => true]);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Jetzt freigeben');
    }

    // --- lock button positioning tests ---

    public function test_lock_button_appears_after_gesperrt_not_under_freigegeben_for_unlocked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $this->assertFalse($page->is_locked);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        // "Jetzt blockieren" must come after both "Freigegeben" and "Gesperrt"
        // proving it is in the Gesperrt section, not between Freigegeben and Gesperrt
        $response->assertSeeInOrder(['Freigegeben', 'Gesperrt', 'Jetzt blockieren']);
    }

    public function test_lock_button_appears_after_gesperrt_not_under_freigegeben_for_locked_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $page->update(['is_locked' => true]);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        // "Jetzt freigeben" must come after both "Freigegeben" and "Gesperrt"
        $response->assertSeeInOrder(['Freigegeben', 'Gesperrt', 'Jetzt freigeben']);
    }

    public function test_lock_button_is_not_rendered_between_freigegeben_and_gesperrt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();

        $html      = $response->getContent();
        $posFreig  = strpos($html, 'Freigegeben');
        $posGesper = strpos($html, 'Gesperrt');
        $posButton = strpos($html, 'Jetzt blockieren');

        // The button must appear after "Gesperrt", not between "Freigegeben" and "Gesperrt"
        $this->assertGreaterThan($posFreig, $posGesper, '"Gesperrt" should appear after "Freigegeben"');
        $this->assertGreaterThan($posGesper, $posButton, '"Jetzt blockieren" should appear after "Gesperrt"');
    }

    // --- header edit action tests ---

    public function test_detail_view_header_shows_beitrag_bearbeiten(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Beitrag bearbeiten');
    }

    // --- table action: Profil ansehen ---

    public function test_table_action_profil_ansehen_links_to_public_url(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get('/admin/memory-pages');

        $response->assertOk();
        $response->assertSee('/m/' . $page->qrCode->short_code, false);
    }

    public function test_table_action_profil_ansehen_opens_in_new_tab(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get('/admin/memory-pages');

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
    }

    public function test_table_action_hidden_when_no_qr_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();
        $code  = $page->qrCode->short_code;

        $page->qrCode()->delete();

        $response = $this->actingAs($admin)->get('/admin/memory-pages');

        $response->assertOk();
        $response->assertDontSee('/m/' . $code, false);
    }

    public function test_internal_view_route_still_accessible(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
    }

    // --- toggle lock action tests ---

    public function test_admin_can_block_memory_page_from_detail_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $this->assertFalse($page->is_locked);

        Livewire::actingAs($admin)
            ->test(ViewMemoryPage::class, ['record' => $page->getRouteKey()])
            ->callInfolistAction('.toggle_lockAction', 'toggle_lock');

        $this->assertTrue($page->fresh()->is_locked);
    }

    public function test_admin_can_unblock_memory_page_from_detail_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page  = $this->makeMemoryPage();

        $page->update(['is_locked' => true]);
        $this->assertTrue($page->fresh()->is_locked);

        Livewire::actingAs($admin)
            ->test(ViewMemoryPage::class, ['record' => $page->getRouteKey()])
            ->callInfolistAction('.toggle_lockAction', 'toggle_lock');

        $this->assertFalse($page->fresh()->is_locked);
    }

    public function test_normal_user_cannot_access_detail_view_actions(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = $this->makeMemoryPage();

        $response = $this->actingAs($user)->get("/admin/memory-pages/{$page->id}");

        $response->assertForbidden();
    }
}
