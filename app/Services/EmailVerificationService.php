<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\QueuedVerifyEmailNotification;

class EmailVerificationService
{
    public function send(User $user): void
    {
        $user->notify(new QueuedVerifyEmailNotification);
    }
}
