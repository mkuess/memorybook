<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageEditTest extends TestCase
{
    use RefreshDatabase;

    private function createPageForUser(User $user, array $attrs = []): MemoryPage
    {
        return MemoryPage::create(array_merge([
            'user_id'     => $user->id,
            'slug'        => 'abcd1234',
            'person_name' => 'Max Mustermann',
        ], $attrs));
    }

    public function test_owner_can_access_edit_page(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('memory-pages.edit', $page));

        $response->assertOk();
        $response->assertSee('Erinnerungsseite bearbeiten');
        $response->assertSee('Max Mustermann');
    }

    public function test_non_owner_gets_403(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $page  = $this->createPageForUser($owner);

        $response = $this->actingAs($other)->get(route('memory-pages.edit', $page));

        $response->assertForbidden();
    }

    public function test_owner_can_update_basic_data(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
            'birth_date'  => '1950-03-15',
            'short_bio'   => 'Eine kurze Biografie.',
        ]);

        $response->assertRedirect(route('dashboard'));

        $page->refresh();
        $this->assertSame('Erika Musterfrau', $page->person_name);
        $this->assertSame('1950-03-15', $page->birth_date->toDateString());
        $this->assertSame('Eine kurze Biografie.', $page->short_bio);
    }

    public function test_slug_cannot_be_changed_through_request_data(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $this->actingAs($user)->put(route('memory-pages.update', $page), [
            'person_name' => 'Erika Musterfrau',
            'slug'        => 'hackedsl',
        ]);

        $page->refresh();
        $this->assertSame('abcd1234', $page->slug);
    }

    public function test_dashboard_links_to_edit_page(): void
    {
        $user = User::factory()->create();
        $page = $this->createPageForUser($user);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.edit', $page));
    }
}
