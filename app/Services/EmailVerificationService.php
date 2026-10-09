<?php

namespace App\Services;

use App\Models\User;
use Throwable;

class EmailVerificationService
{
    public function send(User $user): void
    {
        defer(function () use ($user): void {
            try {
                $user->sendEmailVerificationNotification();
            } catch (Throwable $exception) {
                report($exception);
            }
        });
    }
}
