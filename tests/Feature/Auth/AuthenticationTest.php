<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_login_requests_a_sign_in_link_by_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post('/login', [
            'email' => $user->email,
        ]);

        $this->assertGuest();
        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'login-link-sent');
    }

    public function test_unknown_email_gets_the_same_login_response_without_sending_mail(): void
    {
        Notification::fake();
        $response = $this->post('/login', ['email' => 'unknown@example.com']);

        $this->assertGuest();
        $response->assertRedirect(route('login'))
            ->assertSessionHas('status', 'login-link-sent');
        Notification::assertNothingSent();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $sessionId = $this->app['session']->getId();
        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
        $this->assertNotSame($sessionId, $this->app['session']->getId());
    }
}
