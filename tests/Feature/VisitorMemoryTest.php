<?php

namespace Tests\Feature;

use App\Mail\VisitorMemoryConfirmationMail;
use App\Models\MemoryPage;
use App\Models\Order;
use App\Models\Story;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitorMemoryTest extends TestCase
{
    use RefreshDatabase;

    private function makeEligiblePage(): array
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max Mustermann',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);
        Order::create([
            'user_id'                                => $user->id,
            'memory_page_id'                         => $page->id,
            'package'                                => 'basic',
            'status'                                 => 'paid',
            'billing_name'                           => 'Test',
            'billing_email'                          => 'test@example.com',
            'billing_address'                        => 'Str. 1',
            'billing_postal_code'                    => '1010',
            'billing_city'                           => 'Wien',
            'billing_country'                        => 'Österreich',
            'consent_confirmed_at'                   => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);

        return [$user, $page, $page->qrCode->short_code];
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'content' => 'Ich erinnere mich noch gut an diesen Tag.',
            'email'   => 'besucher@example.com',
            'consent' => '1',
        ], $overrides);
    }

    // --- access ---

    public function test_visitor_can_access_form_for_eligible_page(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->get(route('visitor-memory.create', $code));

        $response->assertOk();
        $response->assertSee('Erinnerung hinterlassen');
    }

    public function test_visitor_cannot_access_form_when_page_is_private(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'private',
        ]);
        Order::create([
            'user_id' => $user->id, 'memory_page_id' => $page->id, 'package' => 'basic',
            'status' => 'paid', 'billing_name' => 'T', 'billing_email' => 't@t.com',
            'billing_address' => 'S', 'billing_postal_code' => '1', 'billing_city' => 'W',
            'billing_country' => 'A', 'consent_confirmed_at' => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);

        $response = $this->get(route('visitor-memory.create', $page->qrCode->short_code));

        $response->assertForbidden();
    }

    public function test_visitor_cannot_access_form_when_page_is_unpublished(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max',
            'is_published' => false,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);
        Order::create([
            'user_id' => $user->id, 'memory_page_id' => $page->id, 'package' => 'basic',
            'status' => 'paid', 'billing_name' => 'T', 'billing_email' => 't@t.com',
            'billing_address' => 'S', 'billing_postal_code' => '1', 'billing_city' => 'W',
            'billing_country' => 'A', 'consent_confirmed_at' => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);

        $response = $this->get(route('visitor-memory.create', $page->qrCode->short_code));

        $response->assertForbidden();
    }

    public function test_visitor_cannot_access_form_when_page_is_locked(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max',
            'is_published' => true,
            'is_locked'    => true,
            'visibility'   => 'link',
        ]);
        Order::create([
            'user_id' => $user->id, 'memory_page_id' => $page->id, 'package' => 'basic',
            'status' => 'paid', 'billing_name' => 'T', 'billing_email' => 't@t.com',
            'billing_address' => 'S', 'billing_postal_code' => '1', 'billing_city' => 'W',
            'billing_country' => 'A', 'consent_confirmed_at' => now(),
            'publication_authorization_confirmed_at' => now(),
        ]);

        $response = $this->get(route('visitor-memory.create', $page->qrCode->short_code));

        $response->assertForbidden();
    }

    public function test_visitor_cannot_access_form_without_paid_order(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id'      => $user->id,
            'slug'         => substr(md5(uniqid()), 0, 8),
            'person_name'  => 'Max',
            'is_published' => true,
            'is_locked'    => false,
            'visibility'   => 'link',
        ]);

        $response = $this->get(route('visitor-memory.create', $page->qrCode->short_code));

        $response->assertForbidden();
    }

    // --- validation ---

    public function test_visitor_form_requires_content(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload(['content' => '']));

        $response->assertSessionHasErrors('content');
    }

    public function test_visitor_form_requires_email(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload(['email' => '']));

        $response->assertSessionHasErrors('email');
    }

    public function test_visitor_form_requires_valid_email(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload(['email' => 'not-an-email']));

        $response->assertSessionHasErrors('email');
    }

    public function test_visitor_form_requires_consent_checkbox(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload(['consent' => '0']));

        $response->assertSessionHasErrors('consent');
    }

    // --- submission behavior ---

    public function test_visitor_submission_is_not_published_immediately(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();

        $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $story = Story::where('is_visitor_submission', true)->first();
        $this->assertNotNull($story);
        $this->assertFalse($story->is_published);
    }

    public function test_visitor_submission_stores_token(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();

        $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $story = Story::where('is_visitor_submission', true)->first();
        $this->assertNotNull($story->visitor_token);
        $this->assertNotNull($story->visitor_token_expires_at);
    }

    public function test_visitor_submission_stores_email(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();

        $this->post(route('visitor-memory.store', $code), $this->validPayload(['email' => 'visitor@test.com']));

        $story = Story::where('is_visitor_submission', true)->first();
        $this->assertSame('visitor@test.com', $story->visitor_email);
    }

    public function test_visitor_submission_sends_confirmation_email(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();

        $this->post(route('visitor-memory.store', $code), $this->validPayload(['email' => 'visitor@test.com']));

        Mail::assertSent(VisitorMemoryConfirmationMail::class, function ($mail) {
            return $mail->hasTo('visitor@test.com');
        });
    }

    public function test_visitor_submission_redirects_to_thankyou(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $response->assertRedirect(route('visitor-memory.thankyou', $code));
    }

    public function test_visitor_submission_does_not_fail_when_email_unavailable(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP error'));
        [,, $code] = $this->makeEligiblePage();

        $response = $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $response->assertRedirect(route('visitor-memory.thankyou', $code));
        $this->assertDatabaseCount('stories', 1);
    }

    public function test_visitor_can_upload_image_with_submission(): void
    {
        Mail::fake();
        Storage::fake('public');
        [,, $code] = $this->makeEligiblePage();

        $file = UploadedFile::fake()->image('memory.jpg', 100, 100);
        $this->post(route('visitor-memory.store', $code), $this->validPayload(['image' => $file]));

        $story = Story::where('is_visitor_submission', true)->first();
        $this->assertNotNull($story->image_path);
        Storage::disk('public')->assertExists($story->image_path);
    }

    // --- confirmation link ---

    public function test_confirmation_link_publishes_story(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();
        $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $story = Story::where('is_visitor_submission', true)->first();
        $token = $story->visitor_token;

        $response = $this->get(route('visitor-memory.confirm', ['code' => $code, 'token' => $token]));

        $response->assertOk();
        $response->assertSee('Danke, deine Erinnerung wurde veröffentlicht.');
        $this->assertTrue($story->fresh()->is_published);
    }

    public function test_confirmation_invalidates_token_after_use(): void
    {
        Mail::fake();
        [,, $code] = $this->makeEligiblePage();
        $this->post(route('visitor-memory.store', $code), $this->validPayload());

        $story = Story::where('is_visitor_submission', true)->first();
        $token = $story->visitor_token;

        $this->get(route('visitor-memory.confirm', ['code' => $code, 'token' => $token]));

        $this->assertNull($story->fresh()->visitor_token);
    }

    public function test_expired_token_shows_expired_view(): void
    {
        $user = User::factory()->create();
        $page = MemoryPage::create([
            'user_id' => $user->id, 'slug' => substr(md5(uniqid()), 0, 8), 'person_name' => 'Max',
        ]);
        $story = $page->stories()->create([
            'user_id'                  => $user->id,
            'title'                    => 'Test',
            'content'                  => 'Inhalt',
            'is_visitor_submission'    => true,
            'is_published'             => false,
            'visitor_token'            => 'expiredtoken123',
            'visitor_token_expires_at' => now()->subDay(),
        ]);

        $response = $this->get(route('visitor-memory.confirm', [
            'code'  => $page->qrCode->short_code,
            'token' => 'expiredtoken123',
        ]));

        $response->assertOk();
        $response->assertSee('Link ungültig oder abgelaufen');
        $this->assertFalse($story->fresh()->is_published);
    }

    public function test_invalid_token_shows_expired_view(): void
    {
        [,, $code] = $this->makeEligiblePage();

        $response = $this->get(route('visitor-memory.confirm', [
            'code'  => $code,
            'token' => 'nonexistenttoken',
        ]));

        $response->assertOk();
        $response->assertSee('Link ungültig oder abgelaufen');
    }

    // --- visitor email not exposed publicly ---

    public function test_visitor_email_is_not_rendered_on_public_page(): void
    {
        [,, $code] = $this->makeEligiblePage();
        $page       = \App\Models\QrCode::where('short_code', $code)->first()->memoryPage;

        $page->stories()->create([
            'user_id'               => $page->user_id,
            'title'                 => 'Erinnerung',
            'content'               => 'Schöner Moment',
            'is_visitor_submission' => true,
            'is_published'          => true,
            'visitor_email'         => 'private@example.com',
            'visitor_token'         => null,
        ]);

        $response = $this->get("/m/{$code}");

        $response->assertOk();
        $response->assertDontSee('private@example.com');
    }
}
