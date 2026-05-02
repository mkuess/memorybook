<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageVisibilityTest extends TestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('visibilityProvider')]
    public function test_owner_can_update_visibility(string $visibility): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => $visibility]
        );

        $response->assertRedirect(route('memory-pages.edit', $page));
        $this->assertSame($visibility, $page->fresh()->visibility);
    }

    public static function visibilityProvider(): array
    {
        return [
            ['private'],
            ['link'],
            ['public'],
        ];
    }

    public function test_invalid_visibility_is_rejected(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => 'secret']
        );

        $response->assertSessionHasErrors('visibility');
        $this->assertSame('private', $page->fresh()->visibility);
    }

    public function test_non_owner_cannot_update_visibility(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->actingAs($other)->put(
            route('memory-pages.update-visibility', $page),
            ['visibility' => 'public']
        );

        $response->assertForbidden();
        $this->assertSame('private', $page->fresh()->visibility);
    }
}
