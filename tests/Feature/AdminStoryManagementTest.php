<?php

namespace Tests\Feature;

use App\Filament\Resources\MemoryPageResource\Pages\ManageMemoryPageStories;
use App\Models\MemoryPage;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminStoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makePage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create(['role' => 'user']);

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Test Person',
        ]);
    }

    private function makeStory(MemoryPage $page, array $attrs = []): Story
    {
        return Story::create(array_merge([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Eine Geschichte',
            'content'        => 'Inhalt der Geschichte.',
            'sort_order'     => 0,
            'is_published'   => false,
        ], $attrs));
    }

    // --- detail page shows button ---

    public function test_memory_page_detail_page_shows_stories_verwalten_button(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makePage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee('Stories verwalten');
    }

    public function test_stories_verwalten_button_links_to_correct_url(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makePage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}");

        $response->assertOk();
        $response->assertSee("/admin/memory-pages/{$page->id}/stories", false);
    }

    // --- access tests ---

    public function test_admin_can_access_story_management_page(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makePage();

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}/stories");

        $response->assertOk();
        $response->assertSee('Stories verwalten');
        $response->assertSee($page->person_name);
    }

    public function test_normal_user_cannot_access_story_management_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = $this->makePage();

        $response = $this->actingAs($user)->get("/admin/memory-pages/{$page->id}/stories");

        $response->assertForbidden();
    }

    // --- story listing tests ---

    public function test_only_stories_for_that_memory_page_are_listed(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makePage();
        $story = $this->makeStory($page, ['title' => 'Richtige Geschichte']);

        $response = $this->actingAs($admin)->get("/admin/memory-pages/{$page->id}/stories");

        $response->assertOk();
        $response->assertSee('Richtige Geschichte');
    }

    public function test_stories_from_other_memory_pages_are_not_listed(): void
    {
        $admin      = $this->makeAdmin();
        $page       = $this->makePage();
        $otherPage  = $this->makePage();

        $this->makeStory($page, ['title' => 'Eigene Geschichte']);
        $this->makeStory($otherPage, ['title' => 'Fremde Geschichte']);

        Livewire::actingAs($admin)
            ->test(ManageMemoryPageStories::class, ['record' => $page->id])
            ->assertSee('Eigene Geschichte')
            ->assertDontSee('Fremde Geschichte');
    }

    // --- edit test ---

    public function test_admin_can_edit_a_story_from_management_page(): void
    {
        $admin = $this->makeAdmin();
        $page  = $this->makePage();
        $story = $this->makeStory($page, ['title' => 'Ursprünglicher Titel', 'is_published' => false]);

        Livewire::actingAs($admin)
            ->test(ManageMemoryPageStories::class, ['record' => $page->id])
            ->callTableAction('edit', $story, data: [
                'title'        => 'Geänderter Titel',
                'content'      => 'Neuer Inhalt',
                'is_published' => true,
                'sort_order'   => 2,
            ])
            ->assertHasNoTableActionErrors();

        $updated = $story->fresh();
        $this->assertSame('Geänderter Titel', $updated->title);
        $this->assertSame('Neuer Inhalt', $updated->content);
        $this->assertTrue($updated->is_published);
        $this->assertSame(2, $updated->sort_order);
    }
}
