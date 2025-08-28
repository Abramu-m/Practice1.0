<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminTriggeredPasswordReset extends Notification
{
    use Queueable;

    protected $admin;
    protected $userEmail;

    public function __construct($admin, $userEmail)
    {
        $this->admin = $admin;
        $this->userEmail = $userEmail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Admin triggered password reset')
                    ->line("Administrator {$this->admin->first_name} {$this->admin->last_name} ({$this->admin->email}) requested a password reset for: {$this->userEmail}")
                    ->line('If this was not expected, please review the user account immediately.');
    }
}
