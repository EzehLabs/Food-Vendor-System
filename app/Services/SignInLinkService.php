<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SignInLinkNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SignInLinkService
{
    private const EXPIRATION_MINUTES = 15;

    public function sendTo(string $email): void
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            return;
        }

        $token = Str::random(64);
        $expiresAt = now()->addMinutes(self::EXPIRATION_MINUTES);

        DB::table('login_link_tokens')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $url = route('login.link.consume', [
            'user' => $user->id,
            'token' => $token,
        ]);

        defer(function () use ($user, $url): void {
            try {
                $user->notify(new SignInLinkNotification($url));
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }

    public function consume(User $user, string $token): bool
    {
        return DB::transaction(function () use ($user, $token): bool {
            $loginLink = DB::table('login_link_tokens')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (
                ! $loginLink
                || ! hash_equals($loginLink->token_hash, hash('sha256', $token))
                || now()->greaterThanOrEqualTo($loginLink->expires_at)
            ) {
                return false;
            }

            DB::table('login_link_tokens')
                ->where('user_id', $user->id)
                ->delete();

            return true;
        });
    }
}
