<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryEditTest extends TestCase
{
    use RefreshDatabase;

    private function makePageWithStory(?User $owner = null): array
    {
        $owner ??= User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);

        $story = $page->stories()->create([
            'user_id'      => $owner->id,
            'title'        => 'Originaltitel',
            'content'      => 'Originalinhalt',
            'is_published' => false,
        ]);

        return [$owner, $page, $story];
    }

    public function test_owner_can_access_edit_story_page(): void
    {
        [$owner, $page, $story] = $this->makePageWithStory();

        $response = $this->actingAs($owner)
            ->get(route('memory-pages.stories.edit', [$page, $story]));

        $response->assertOk();
        $response->assertSee('Originaltitel');
    }

    public function test_non_owner_gets_403_on_edit_story_page(): void
    {
        [, $page, $story] = $this->makePageWithStory();
        $other = User::factory()->create();

        $response = $this->actingAs($other)
            ->get(route('memory-pages.stories.edit', [$page, $story]));

        $response->assertForbidden();
    }

    public function test_owner_can_update_story(): void
    {
        [$owner, $page, $story] = $this->makePageWithStory();

        $response = $this->actingAs($owner)
            ->put(route('memory-pages.stories.update', [$page, $story]), [
                'title'        => 'Neuer Titel',
                'content'      => 'Neuer Inhalt',
                'is_published' => '1',
            ]);

        $response->assertRedirect(route('memory-pages.stories.index', $page));
        $this->assertEquals('Neuer Titel', $story->fresh()->title);
        $this->assertTrue($story->fresh()->is_published);
    }

    public function test_story_must_belong_to_the_memory_page(): void
    {
        [$owner, $page] = $this->makePageWithStory($owner = User::factory()->create());

        $otherPage = MemoryPage::create([
            'user_id'     => $owner->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Andere Person',
        ]);

        $foreignStory = $otherPage->stories()->create([
            'user_id' => $owner->id,
            'title'   => 'Fremd',
            'content' => 'Fremd',
        ]);

        $response = $this->actingAs($owner)
            ->get(route('memory-pages.stories.edit', [$page, $foreignStory]));

        $response->assertNotFound();
    }

    public function test_unchecked_is_published_sets_story_unpublished(): void
    {
        [$owner, $page, $story] = $this->makePageWithStory();

        $story->update(['is_published' => true]);

        $this->actingAs($owner)
            ->put(route('memory-pages.stories.update', [$page, $story]), [
                'title'   => $story->title,
                'content' => $story->content,
            ]);

        $this->assertFalse($story->fresh()->is_published);
    }

    public function test_story_list_links_to_edit_page(): void
    {
        [$owner, $page, $story] = $this->makePageWithStory();

        $response = $this->actingAs($owner)
            ->get(route('memory-pages.stories.index', $page));

        $response->assertOk();
        $response->assertSee(route('memory-pages.stories.edit', [$page, $story]));
    }
}
