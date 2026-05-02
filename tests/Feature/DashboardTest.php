<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithPage(array $pageAttrs = []): array
    {
        $user = User::factory()->create();
        $page = MemoryPage::create(array_merge([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ], $pageAttrs));

        return [$user, $page];
    }

    // --- access ---

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_logged_in_user_can_see_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Meine Erinnerungsseiten');
    }

    public function test_empty_state_is_visible_when_user_has_no_memory_pages(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Sie haben noch keine Erinnerungsseite angelegt.');
    }

    // --- story count column ---

    public function test_dashboard_shows_story_count_for_each_memory_page(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $page->stories()->create([
            'user_id'      => $user->id,
            'title'        => 'Geschichte 1',
            'content'      => 'Inhalt.',
            'is_published' => true,
        ]);
        $page->stories()->create([
            'user_id'      => $user->id,
            'title'        => 'Geschichte 2',
            'content'      => 'Inhalt.',
            'is_published' => false,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('2');
    }

    public function test_dashboard_shows_zero_story_count_when_no_stories(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('0');
    }

    public function test_story_count_links_to_the_story_overview_page(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.stories.index', $page), false);
    }

    // --- published column ---

    public function test_dashboard_shows_ja_for_published_pages(): void
    {
        [$user] = $this->makeUserWithPage(['is_published' => true]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Ja');
    }

    public function test_dashboard_shows_nein_for_unpublished_pages(): void
    {
        [$user] = $this->makeUserWithPage(['is_published' => false]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Nein');
    }

    public function test_dashboard_shows_freigegeben_column_header(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Freigegeben');
    }

    public function test_dashboard_shows_storys_column_header(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Storys');
    }

    // --- no admin fields ---

    public function test_dashboard_does_not_show_admin_lock_status(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('is_locked');
        $response->assertDontSee('Gesperrt');
    }

    // --- existing links still present ---

    public function test_dashboard_still_shows_edit_link(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.edit', $page), false);
    }

    public function test_dashboard_still_shows_qr_code_link(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee(route('memory-pages.qr-code', $page), false);
    }
}
