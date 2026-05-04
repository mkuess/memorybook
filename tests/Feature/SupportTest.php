<?php

namespace Tests\Feature;

use App\Models\MemoryPage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(): User
    {
        return User::factory()->create(['role' => 'user']);
    }

    private function makePageFor(User $user): MemoryPage
    {
        return MemoryPage::create([
            'user_id'     => $user->id,
            'slug'        => substr(md5(uniqid()), 0, 8),
            'person_name' => 'Max Mustermann',
        ]);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'category'    => 'Problem',
            'subject'     => 'Test-Betreff',
            'description' => 'Eine Testnachricht.',
        ], $overrides);
    }

    // --- access control ---

    public function test_authenticated_customer_can_access_support_page(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get(route('support.create'))->assertOk();
    }

    public function test_guest_cannot_access_support_page(): void
    {
        $this->get(route('support.create'))->assertRedirect(route('login'));
    }

    // --- validation ---

    public function test_support_form_requires_category(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('support.store'), $this->validPayload(['category' => '']))
            ->assertSessionHasErrors('category');
    }

    public function test_support_form_rejects_invalid_category(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('support.store'), $this->validPayload(['category' => 'Unbekannt']))
            ->assertSessionHasErrors('category');
    }

    public function test_support_form_requires_subject(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('support.store'), $this->validPayload(['subject' => '']))
            ->assertSessionHasErrors('subject');
    }

    public function test_support_form_requires_message(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('support.store'), $this->validPayload(['description' => '']))
            ->assertSessionHasErrors('description');
    }

    // --- storage ---

    public function test_submitting_support_message_stores_user_id(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('support.store'), $this->validPayload());

        $this->assertDatabaseHas('reports', [
            'user_id'  => $user->id,
            'category' => 'Problem',
        ]);
    }

    public function test_submitting_support_message_stores_reporter_name_and_email(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('support.store'), $this->validPayload());

        $this->assertDatabaseHas('reports', [
            'reporter_name'  => $user->name,
            'reporter_email' => $user->email,
        ]);
    }

    public function test_submitting_support_message_stores_subject_and_description(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('support.store'), $this->validPayload([
            'subject'     => 'Mein Betreff',
            'description' => 'Meine Nachricht',
        ]));

        $this->assertDatabaseHas('reports', [
            'subject'     => 'Mein Betreff',
            'description' => 'Meine Nachricht',
            'status'      => 'open',
        ]);
    }

    public function test_customer_can_link_own_memory_page(): void
    {
        $user = $this->makeUser();
        $page = $this->makePageFor($user);

        $this->actingAs($user)->post(route('support.store'), $this->validPayload([
            'memory_page_id' => $page->id,
        ]));

        $this->assertDatabaseHas('reports', [
            'user_id'        => $user->id,
            'memory_page_id' => $page->id,
        ]);
    }

    public function test_customer_cannot_link_another_users_memory_page(): void
    {
        $user  = $this->makeUser();
        $other = $this->makeUser();
        $page  = $this->makePageFor($other);

        $this->actingAs($user)->post(route('support.store'), $this->validPayload([
            'memory_page_id' => $page->id,
        ]));

        $this->assertDatabaseHas('reports', [
            'user_id'        => $user->id,
            'memory_page_id' => null,
        ]);
    }

    public function test_submitting_redirects_with_success_message(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)
            ->post(route('support.store'), $this->validPayload())
            ->assertRedirect(route('support.create'))
            ->assertSessionHas('success');
    }

    // --- admin visibility ---

    public function test_admin_can_see_support_message_in_admin_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = $this->makeUser();

        $this->actingAs($user)->post(route('support.store'), $this->validPayload([
            'subject' => 'Adminsichtbar',
        ]));

        $this->actingAs($admin)->get('/admin/reports')->assertOk();

        $this->assertDatabaseHas('reports', [
            'subject'  => 'Adminsichtbar',
            'category' => 'Problem',
        ]);
    }

    public function test_normal_user_cannot_access_admin_reports(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/admin/reports')->assertForbidden();
    }

    public function test_all_valid_categories_are_accepted(): void
    {
        $user = $this->makeUser();

        foreach (['Problem', 'Frage', 'Verbesserungsvorschlag', 'Sonstiges'] as $cat) {
            $this->actingAs($user)
                ->post(route('support.store'), $this->validPayload(['category' => $cat]))
                ->assertRedirect(route('support.create'));
        }

        $this->assertSame(4, Report::where('user_id', $user->id)->count());
    }
}
