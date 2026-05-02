<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeMemoryPage(): MemoryPage
    {
        $user = User::factory()->create();

        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);
    }

    public function test_memory_page_can_have_stories(): void
    {
        $page = $this->makeMemoryPage();

        Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Erste Erinnerung',
            'content'        => 'Ein schöner Moment.',
        ]);

        Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Zweite Erinnerung',
            'content'        => 'Noch ein Moment.',
        ]);

        $this->assertCount(2, $page->stories);
    }

    public function test_story_belongs_to_memory_page(): void
    {
        $page = $this->makeMemoryPage();

        $story = Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Erinnerung',
            'content'        => 'Inhalt.',
        ]);

        $this->assertInstanceOf(MemoryPage::class, $story->memoryPage);
        $this->assertEquals($page->id, $story->memoryPage->id);
    }

    public function test_story_belongs_to_user(): void
    {
        $page = $this->makeMemoryPage();

        $story = Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Erinnerung',
            'content'        => 'Inhalt.',
        ]);

        $this->assertInstanceOf(User::class, $story->user);
        $this->assertEquals($page->user_id, $story->user->id);
    }

    public function test_default_sort_order_is_0(): void
    {
        $page = $this->makeMemoryPage();

        $story = Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Erinnerung',
            'content'        => 'Inhalt.',
        ]);

        $this->assertSame(0, $story->sort_order);
    }

    public function test_default_is_published_is_false(): void
    {
        $page = $this->makeMemoryPage();

        $story = Story::create([
            'memory_page_id' => $page->id,
            'user_id'        => $page->user_id,
            'title'          => 'Erinnerung',
            'content'        => 'Inhalt.',
        ]);

        $this->assertFalse($story->is_published);
    }
}
