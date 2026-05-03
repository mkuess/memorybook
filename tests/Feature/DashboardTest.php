<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\QrCode;
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

    private function createPaidOrder(User $user, MemoryPage $page): Order
    {
        return Order::create([
            'user_id'         => $user->id,
            'memory_page_id'  => $page->id,
            'status'          => 'paid',
            'package'         => 'basic',
            'billing_name'    => 'Test User',
            'billing_email'   => $user->email,
            'billing_address' => 'Musterstraße 1',
            'billing_postal_code' => '12345',
            'billing_city'    => 'Musterstadt',
            'billing_country' => 'DE',
            'consent_given_at' => now(),
        ]);
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

    // --- status column ---

    public function test_dashboard_shows_status_column_header(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Status');
    }

    public function test_dashboard_shows_erinnerungen_column_header(): void
    {
        [$user] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Erinnerungen');
    }

    public function test_dashboard_shows_online_when_all_conditions_met(): void
    {
        [$user, $page] = $this->makeUserWithPage([
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'public',
        ]);
        $this->createPaidOrder($user, $page);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Online');
        $response->assertDontSee('Offline');
    }

    public function test_dashboard_shows_online_when_visibility_is_link(): void
    {
        [$user, $page] = $this->makeUserWithPage([
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);
        $this->createPaidOrder($user, $page);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Online');
    }

    public function test_dashboard_shows_offline_when_no_paid_order(): void
    {
        [$user] = $this->makeUserWithPage([
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Offline');
        $response->assertDontSee('Online');
    }

    public function test_dashboard_shows_offline_when_not_published(): void
    {
        [$user, $page] = $this->makeUserWithPage([
            'is_published' => false,
            'is_locked'    => false,
            'visibility'   => 'public',
        ]);
        $this->createPaidOrder($user, $page);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Offline');
        $response->assertDontSee('Online');
    }

    public function test_dashboard_shows_offline_when_locked(): void
    {
        [$user, $page] = $this->makeUserWithPage([
            'is_published' => true,
            'is_locked'    => true,
            'visibility'   => 'public',
        ]);
        $this->createPaidOrder($user, $page);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Offline');
        $response->assertDontSee('Online');
    }

    public function test_dashboard_shows_offline_when_visibility_is_private(): void
    {
        [$user, $page] = $this->makeUserWithPage([
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'private',
        ]);
        $this->createPaidOrder($user, $page);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Offline');
        $response->assertDontSee('Online');
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

    // --- new actions ---

    public function test_dashboard_shows_qr_code_action_with_svg_icon(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('QR-Code');
        $response->assertSee('<svg', false);
    }

    public function test_dashboard_shows_profil_aufrufen_action(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Profil aufrufen');
    }

    public function test_profil_aufrufen_links_to_public_profile_url(): void
    {
        [$user, $page] = $this->makeUserWithPage();
        $code = $page->qrCode->short_code;

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee("/m/{$code}", false);
    }

    public function test_profil_aufrufen_opens_in_new_tab(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('target="_blank"', false);
        $response->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_no_broken_profile_link_when_qr_code_is_missing(): void
    {
        [$user, $page] = $this->makeUserWithPage();

        $page->qrCode()->delete();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertDontSee('/m/', false);
        $response->assertSee('Kein QR-Code');
        $response->assertDontSee('Profil aufrufen');
    }
}
