<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\SignInLinkNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SignInLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_sign_in_link_authenticates_and_verifies_user_once(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post('/login', ['email' => $user->email])
            ->assertRedirect(route('login'));

        $url = null;
        Notification::assertSentTo(
            $user,
            SignInLinkNotification::class,
            function (SignInLinkNotification $notification) use (&$url): bool {
                $url = $notification->url;

                return true;
            },
        );

        $this->get($url)->assertRedirect('/customer/home');

        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertDatabaseMissing('login_link_tokens', ['user_id' => $user->id]);

        $this->post('/logout')->assertRedirect('/');
        $this->get($url)
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_sign_in_link_expires_after_fifteen_minutes(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email]);

        $url = null;
        Notification::assertSentTo(
            $user,
            SignInLinkNotification::class,
            function (SignInLinkNotification $notification) use (&$url): bool {
                $url = $notification->url;

                return true;
            },
        );

        $this->travel(16)->minutes();

        $this->get($url)
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_requesting_a_new_link_invalidates_the_previous_link(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email]);
        $this->post('/login', ['email' => $user->email]);

        $urls = [];
        Notification::assertSentTo(
            $user,
            SignInLinkNotification::class,
            function (SignInLinkNotification $notification) use (&$urls): bool {
                $urls[] = $notification->url;

                return true;
            },
        );

        $this->assertCount(2, $urls);
        $this->get($urls[0])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->get($urls[1])->assertRedirect('/customer/home');

        $this->assertAuthenticatedAs($user);
    }
}
