<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
    }

    public function test_admin_can_view_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}");

        $response->assertOk();
    }

    public function test_admin_can_access_edit_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($admin)->get("/admin/users/{$user->id}/edit");

        $response->assertOk();
    }

    public function test_normal_user_cannot_access_user_list(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertForbidden();
    }

    public function test_guest_cannot_access_user_list(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    // --- create user tests ---

    public function test_admin_can_access_create_user_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users/create');

        $response->assertOk();
    }

    public function test_admin_can_create_a_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name'     => 'Neue Person',
                'email'    => 'neu@example.com',
                'password' => 'secret123',
                'role'     => 'user',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'name'  => 'Neue Person',
            'email' => 'neu@example.com',
            'role'  => 'user',
        ]);
    }

    public function test_created_user_has_hashed_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'name'     => 'Hash Person',
                'email'    => 'hash@example.com',
                'password' => 'plaintext99',
                'role'     => 'user',
            ])
            ->call('create');

        $user = User::where('email', 'hash@example.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue(Hash::check('plaintext99', $user->password));
        $this->assertNotSame('plaintext99', $user->password);
    }

    public function test_normal_user_cannot_access_create_user_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users/create');

        $response->assertForbidden();
    }

    // --- table action tests ---

    public function test_user_table_does_not_show_view_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableActionDoesNotExist('view', record: $user);
    }

    public function test_user_table_shows_edit_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create(['role' => 'user']);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->assertTableActionExists('edit', record: $user);
    }

    public function test_list_header_shows_benutzer_erstellen_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertOk();
        $response->assertSee('Benutzer erstellen');
    }
}
