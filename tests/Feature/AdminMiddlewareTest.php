<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-admin-only', fn () => response('ok', 200))
            ->middleware(['auth', 'admin']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/test-admin-only');

        $response->assertRedirect(route('login'));
    }

    public function test_normal_user_gets_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/test-admin-only');

        $response->assertForbidden();
    }

    public function test_admin_user_gets_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/test-admin-only');

        $response->assertOk();
        $response->assertSee('ok');
    }
}
