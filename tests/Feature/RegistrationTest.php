<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Maria Musterfrau',
            'email'                 => 'maria@example.com',
            'password'              => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ], $overrides);
    }

    // --- page access ---

    public function test_guest_can_access_registration_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_registration_page_shows_konto_erstellen(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Konto erstellen');
    }

    public function test_registration_page_shows_german_field_labels(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Name');
        $response->assertSee('E-Mail-Adresse');
        $response->assertSee('Passwort bestätigen');
    }

    public function test_login_page_shows_konto_erstellen_link(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('Konto erstellen');
        $response->assertSee(route('register'), false);
    }

    // --- successful registration ---

    public function test_guest_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), $this->validData());

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
    }

    public function test_created_user_has_role_user(): void
    {
        $this->post(route('register'), $this->validData());

        $user = User::where('email', 'maria@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
    }

    public function test_created_user_password_is_hashed(): void
    {
        $this->post(route('register'), $this->validData());

        $user = User::where('email', 'maria@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotSame('SecurePass123!', $user->getAttributes()['password']);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('SecurePass123!', $user->getAttributes()['password']));
    }

    public function test_registration_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register'), $this->validData());

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_is_authenticated_after_registration(): void
    {
        $this->post(route('register'), $this->validData());

        $this->assertAuthenticated();
    }

    // --- security: role injection ---

    public function test_role_cannot_be_set_to_admin_through_registration(): void
    {
        $this->post(route('register'), array_merge($this->validData(), ['role' => 'admin']));

        $user = User::where('email', 'maria@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('user', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_registered_user_cannot_access_admin_panel(): void
    {
        $this->post(route('register'), $this->validData());

        $response = $this->get('/admin');

        $response->assertForbidden();
    }

    // --- validation ---

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('register'), $this->validData(['email' => 'taken@example.com']));

        $response->assertSessionHasErrors('email');
        $this->assertCount(1, User::where('email', 'taken@example.com')->get());
    }

    public function test_password_confirmation_is_required(): void
    {
        $response = $this->post(route('register'), $this->validData([
            'password_confirmation' => 'DifferentPass999!',
        ]));

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'maria@example.com']);
    }

    public function test_name_is_required(): void
    {
        $response = $this->post(route('register'), $this->validData(['name' => '']));

        $response->assertSessionHasErrors('name');
    }

    public function test_email_is_required(): void
    {
        $response = $this->post(route('register'), $this->validData(['email' => '']));

        $response->assertSessionHasErrors('email');
    }
}
