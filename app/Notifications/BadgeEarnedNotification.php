<?php

// BadgeEarnedNotification.php - Updated
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Badge;

class BadgeEarnedNotification extends Notification
{
    use Queueable;

    protected $badge;

    public function __construct(Badge $badge)
    {
        $this->badge = $badge;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'badge_earned',
            'message' => "Selamat! Kamu mendapatkan badge: \"{$this->badge->name}\".",
            'badge_id' => $this->badge->id,
            'badge_name' => $this->badge->name,
            'badge_description' => $this->badge->description,
            'action_url' => '/profile/' . auth()->id(), // Store relative URL
        ];
    }
}
