<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_memory_pages(): void
    {
        $user = User::factory()->create();

        MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'xyz67890',
            'person_name' => 'Erika Musterfrau',
        ]);

        $this->assertCount(2, $user->memoryPages);
    }

    public function test_memory_page_belongs_to_user(): void
    {
        $user = User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $this->assertInstanceOf(User::class, $page->user);
        $this->assertEquals($user->id, $page->user->id);
    }

    public function test_default_visibility_is_private(): void
    {
        $user = User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $this->assertSame('private', $page->visibility);
    }

    public function test_default_is_published_is_false(): void
    {
        $user = User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $this->assertFalse($page->is_published);
    }

    public function test_default_is_locked_is_false(): void
    {
        $user = User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $this->assertFalse($page->is_locked);
    }

    public function test_slug_must_be_unique(): void
    {
        $user = User::factory()->create();

        MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $this->expectException(QueryException::class);

        MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Zweite Person',
        ]);
    }

    public function test_memory_page_supports_soft_deletes(): void
    {
        $user = User::factory()->create();

        $page = MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abc12345',
            'person_name' => 'Max Mustermann',
        ]);

        $page->delete();

        $this->assertSoftDeleted('memory_pages', ['id' => $page->id]);
        $this->assertNull(MemoryPage::find($page->id));
        $this->assertNotNull(MemoryPage::withTrashed()->find($page->id));
    }
}
