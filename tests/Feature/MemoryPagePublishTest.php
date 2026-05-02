<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPagePublishTest extends TestCase
{
    use RefreshDatabase;

    private function createPageForUser(User $user): MemoryPage
    {
        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => 'abcd1234',
            'person_name' => 'Max Mustermann',
        ]);
    }

    public function test_owner_can_publish_with_consent(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(
            route('memory-pages.publish', $page),
            ['consent' => '1']
        );

        $response->assertRedirect(route('memory-pages.edit', $page));

        $page->refresh();
        $this->assertTrue($page->is_published);
        $this->assertNotNull($page->consent_confirmed_at);
        $this->assertNotNull($page->published_at);
    }

    public function test_owner_cannot_publish_without_consent(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->post(
            route('memory-pages.publish', $page),
            []
        );

        $response->assertSessionHasErrors('consent');

        $page->refresh();
        $this->assertFalse($page->is_published);
    }

    public function test_non_owner_cannot_publish(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->actingAs($other)->post(
            route('memory-pages.publish', $page),
            ['consent' => '1']
        );

        $response->assertForbidden();

        $page->refresh();
        $this->assertFalse($page->is_published);
    }

    public function test_owner_can_unpublish(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);
        $page->update([
            'is_published'         => true,
            'published_at'         => now(),
            'consent_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(
            route('memory-pages.unpublish', $page)
        );

        $response->assertRedirect(route('memory-pages.edit', $page));

        $page->refresh();
        $this->assertFalse($page->is_published);
        $this->assertNull($page->published_at);
    }

    public function test_unpublishing_does_not_clear_consent_confirmed_at(): void
    {
        $user      = User::factory()->create();
        $page      = $this->createPageForUser($user);
        $consentAt = now()->subDay()->startOfSecond();
        $page->update([
            'is_published'         => true,
            'published_at'         => now(),
            'consent_confirmed_at' => $consentAt,
        ]);

        $this->actingAs($user)->post(route('memory-pages.unpublish', $page));

        $page->refresh();
        $this->assertNotNull($page->consent_confirmed_at);
        $this->assertTrue($page->consent_confirmed_at->equalTo($consentAt));
    }
}
