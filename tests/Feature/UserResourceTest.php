<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
