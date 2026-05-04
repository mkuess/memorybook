<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLastLoginTest extends TestCase
{
    use RefreshDatabase;

    // --- login sets last_login_at ---

    public function test_successful_login_sets_last_login_at(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->assertNull($user->last_login_at);

        $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_failed_login_does_not_set_last_login_at(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_last_login_at_is_updated_on_each_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->update(['last_login_at' => now()->subDays(10)]);
        $before = $user->last_login_at;

        $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        $this->assertTrue($user->fresh()->last_login_at->isAfter($before));
    }

    // --- Filament Benutzer list ---

    public function test_admin_can_access_benutzer_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_benutzer_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_benutzer_list_shows_zuletzt_eingeloggt_column(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Zuletzt eingeloggt');
    }

    public function test_benutzer_list_does_not_show_erstellt_column(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $html = $response->getContent();
        // The table header "Erstellt" should no longer appear as a standalone column label.
        // "Erstellt am" in the infolist is fine; we only check the table header.
        $this->assertStringNotContainsString('>Erstellt<', $html);
    }

    public function test_user_with_null_last_login_shows_noch_nie(): void
    {
        $admin   = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'role'          => 'user',
            'last_login_at' => null,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Noch nie');
    }

    public function test_user_with_last_login_shows_formatted_date(): void
    {
        $admin    = User::factory()->create(['role' => 'admin']);
        $loginAt  = now()->setDate(2026, 5, 4)->setTime(10, 35, 0);
        User::factory()->create([
            'role'          => 'user',
            'last_login_at' => $loginAt,
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('04.05.2026 10:35');
    }
}
