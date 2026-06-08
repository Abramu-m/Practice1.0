<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewUserRegistered extends Notification
{
    use Queueable;

    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('New User Registration - Verification Required')
                    ->greeting('Hello Admin!')
                    ->line('A new user has registered and is waiting for verification.')
                    ->line('User Details:')
                    ->line('Name: ' . $this->user->first_name . ' ' . $this->user->last_name)
                    ->line('Email: ' . $this->user->email)
                    ->line('Role: ' . ucfirst($this->user->role))
                    ->line('Registration Date: ' . $this->user->created_at->format('d/m/Y H:i'))
                    ->action('Verify User', url('/users/pending-verification'))
                    ->line('Please review and verify the user account.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->first_name . ' ' . $this->user->last_name,
            'user_email' => $this->user->email,
            'user_role' => $this->user->role,
            'message' => 'New user registration requires verification',
            'action_url' => url('/users/pending-verification')
        ];
    }
}
