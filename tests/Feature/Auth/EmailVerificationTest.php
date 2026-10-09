<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\QueuedVerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
    }

    public function test_verification_email_is_sent_through_brevo_api(): void
    {
        config([
            'mail.default' => 'brevo',
            'mail.from.address' => 'sender@example.com',
            'mail.from.name' => 'Food Vending System',
            'services.brevo.key' => 'test-api-key',
        ]);

        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response(['messageId' => '<test-id>'], 201),
        ]);

        $user = User::factory()->unverified()->create();

        Notification::sendNow($user, new QueuedVerifyEmailNotification);

        Http::assertSent(function (Request $request) use ($user): bool {
            return $request->url() === 'https://api.brevo.com/v3/smtp/email'
                && $request['to'][0]['email'] === $user->email
                && $request['subject'] !== ''
                && str_contains($request['htmlContent'], "/email/verify/{$user->id}/");
        });
    }

    public function test_registration_sends_a_verification_email(): void
    {
        Queue::fake();

        $this->followingRedirects()
            ->post('/register/customer', [
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertOk()
            ->assertSee('Your account was registered successfully.');

        $user = User::where('email', 'customer@example.com')->firstOrFail();

        Queue::assertPushed(SendQueuedNotifications::class);
    }

    public function test_registration_response_is_shown_when_email_delivery_fails(): void
    {
        Queue::fake();

        config([
            'mail.default' => 'brevo',
            'services.brevo.key' => null,
        ]);

        $this->followingRedirects()
            ->post('/register/customer', [
                'name' => 'Test Customer',
                'email' => 'customer@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertOk()
            ->assertSee('Your account was registered successfully.');

        $this->assertAuthenticated();
        Queue::assertPushed(SendQueuedNotifications::class);
    }

    public function test_unverified_user_can_resend_their_verification_email(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->from('/email/verify')
            ->post('/email/verification-notification')
            ->assertRedirect('/email/verify')
            ->assertSessionHas('status', 'verification-link-sending');

        Notification::assertSentTo($user, QueuedVerifyEmailNotification::class);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/customer/home');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
