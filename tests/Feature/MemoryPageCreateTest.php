<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryPageCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_create_page(): void
    {
        $response = $this->get(route('memory-pages.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_logged_in_user_can_see_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('memory-pages.create'));

        $response->assertOk();
        $response->assertSee('Neue Erinnerungsseite anlegen');
    }

    public function test_logged_in_user_can_create_a_memory_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'Max Mustermann',
        ]);

        $this->assertDatabaseHas('memory_pages', ['person_name' => 'Max Mustermann']);

        $page = \App\Models\MemoryPage::where('person_name', 'Max Mustermann')->firstOrFail();
        $response->assertRedirect(route('memory-pages.edit', $page));
    }

    public function test_creating_a_memory_page_redirects_to_the_edit_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'Neue Person',
        ]);

        $page = \App\Models\MemoryPage::where('person_name', 'Neue Person')->firstOrFail();
        $response->assertRedirect(route('memory-pages.edit', $page));
    }

    public function test_qr_code_record_is_created_with_the_memory_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'QR Person',
        ]);

        $page = \App\Models\MemoryPage::where('person_name', 'QR Person')->firstOrFail();
        $this->assertNotNull($page->qrCode);
        $this->assertNotEmpty($page->qrCode->short_code);
    }

    public function test_created_page_belongs_to_the_logged_in_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'Max Mustermann',
        ]);

        $page = MemoryPage::where('person_name', 'Max Mustermann')->firstOrFail();

        $this->assertEquals($user->id, $page->user_id);
    }

    public function test_slug_is_generated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'Max Mustermann',
        ]);

        $page = MemoryPage::where('person_name', 'Max Mustermann')->firstOrFail();

        $this->assertNotNull($page->slug);
        $this->assertEquals(8, strlen($page->slug));
    }

    public function test_dashboard_lists_the_created_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('memory-pages.store'), [
            'person_name' => 'Max Mustermann',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Max Mustermann');
    }
}
