<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\Notifier;
use Illuminate\Notifications\Messages\MailMessage;

class NotificationService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function toUser(User $user, MailMessage $mailMessage, array $data = [])
    {
        $data = [
            'via' => ['mail', "database"],
            'data' => [
                'subject' => $mailMessage->subject,
                'greeting' => $mailMessage->greeting,
                'outroLines' => $mailMessage->outroLines,
                'introLines' => $mailMessage->introLines,
                'actionText' => $mailMessage->actionText,
                'actionUrl' => $mailMessage->actionUrl,
            ] + $data,
        ];

        $user->notify(new Notifier($mailMessage, $data));
    }

    public function toAdmin(User $user) {}

    public function toEmail(string $email, MailMessage $mailMessage, array $data = [])
    {
        $data = [
            'via' => ['mail'],
            'data' => [
                'subject' => $mailMessage->subject,
                'greeting' => $mailMessage->greeting,
                'outroLines' => $mailMessage->outroLines,
                'introLines' => $mailMessage->introLines,
                'actionText' => $mailMessage->actionText,
                'actionUrl' => $mailMessage->actionUrl,
            ] + $data,
        ];

        // Directly send the email without attaching it to a user
        \Illuminate\Support\Facades\Notification::route('mail', $email)->notify(new Notifier($mailMessage, $data));
    }
}