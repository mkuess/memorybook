<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(?User $owner = null): MemoryPage
    {
        $owner ??= User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function storyPayload(array $overrides = []): array
    {
        return array_merge([
            'title'   => 'Eine schöne Erinnerung',
            'content' => 'Dies ist der Inhalt der Erinnerung.',
        ], $overrides);
    }

    public function test_owner_can_view_story_list(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.stories.index', $page));

        $response->assertOk();
    }

    public function test_non_owner_gets_403_on_story_list(): void
    {
        $page  = $this->makePage();
        $other = User::factory()->create();

        $response = $this->actingAs($other)->get(route('memory-pages.stories.index', $page));

        $response->assertForbidden();
    }

    public function test_owner_can_access_create_story_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)->get(route('memory-pages.stories.create', $page));

        $response->assertOk();
    }

    public function test_owner_can_create_story(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $response = $this->actingAs($owner)
            ->post(route('memory-pages.stories.store', $page), $this->storyPayload());

        $response->assertRedirect(route('memory-pages.stories.index', $page));
        $this->assertDatabaseHas('stories', ['title' => 'Eine schöne Erinnerung']);
    }

    public function test_created_story_belongs_to_memory_page(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $this->actingAs($owner)
            ->post(route('memory-pages.stories.store', $page), $this->storyPayload());

        $story = Story::first();

        $this->assertEquals($page->id, $story->memory_page_id);
    }

    public function test_created_story_belongs_to_user(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $this->actingAs($owner)
            ->post(route('memory-pages.stories.store', $page), $this->storyPayload());

        $story = Story::first();

        $this->assertEquals($owner->id, $story->user_id);
    }

    public function test_story_defaults_to_unpublished_if_not_checked(): void
    {
        $owner = User::factory()->create();
        $page  = $this->makePage($owner);

        $this->actingAs($owner)
            ->post(route('memory-pages.stories.store', $page), $this->storyPayload());

        $this->assertFalse(Story::first()->is_published);
    }
}
