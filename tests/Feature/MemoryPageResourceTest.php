<?php

namespace Tests\Feature;

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
}
