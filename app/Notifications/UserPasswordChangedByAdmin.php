<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserPasswordChangedByAdmin extends Notification
{
    use Queueable;

    protected $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $adminName = $this->admin ? ($this->admin->first_name . ' ' . $this->admin->last_name . ' (' . $this->admin->email . ')') : 'an administrator';

        return (new MailMessage)
                    ->subject('Your password was changed by an administrator')
                    ->line("{$adminName} has set a new password for your account.")
                    ->line('If you did not expect this change, please contact support immediately and consider changing your password again.')
                    ->line('For security reasons, the new password is not included in this email.');
    }
}
