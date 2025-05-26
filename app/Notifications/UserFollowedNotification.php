<?php

// UserFollowedNotification.php - Updated
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class UserFollowedNotification extends Notification
{
    use Queueable;

    protected $follower;

    public function __construct(User $follower)
    {
        $this->follower = $follower;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Someone started following you.')
            ->action('View Profile', route('profile.show', $this->follower->id))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_followed',
            'follower_id' => $this->follower->id,
            'follower_name' => $this->follower->name,
            'follower_avatar' => $this->follower->getProfileImageUrl(),
            'message' => $this->follower->name . ' mulai mengikuti Anda. Klik untuk melihat profilnya!',
            'action_url' => '/profile/' . $this->follower->id, // Store relative URL
            'created_at' => now(),
        ];
    }
}
