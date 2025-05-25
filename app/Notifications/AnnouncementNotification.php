<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;

class AnnouncementNotification extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $actionUrl;
    protected $isPinned;
    protected $post;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title, string $message, string $actionUrl = null, bool $isPinned = false, Post $post = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->actionUrl = $actionUrl;
        $this->isPinned = $isPinned;
        $this->post = $post;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line($this->message)
            ->action('View', $this->actionUrl ?: url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement',
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl,
            'is_pinned' => $this->isPinned,
            'post_id' => $this->post ? $this->post->id : null,
            'post_title' => $this->post ? $this->post->title : null,
            'created_at' => now(),
        ];
    }
}
