<?php

namespace Tests\Feature;

use App\Filament\Resources\MemoryPageResource\Pages\CreateMemoryPage;
use App\Filament\Resources\MemoryPageResource\Pages\EditMemoryPage;
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
}
