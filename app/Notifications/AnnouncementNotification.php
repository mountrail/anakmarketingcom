<?php

// AnnouncementNotification.php - Updated with category system
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
        // Store relative URL instead of absolute URL
        $this->actionUrl = $actionUrl ? $this->makeRelativeUrl($actionUrl) : null;
        $this->isPinned = $isPinned;
        $this->post = $post;
    }

    /**
     * Convert absolute URL to relative URL
     */
    private function makeRelativeUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // If it's already a relative URL, return as is
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0) {
            return $url;
        }

        // Parse URL and return path + query + fragment
        $parsed = parse_url($url);
        $relative = $parsed['path'] ?? '/';

        if (!empty($parsed['query'])) {
            $relative .= '?' . $parsed['query'];
        }

        if (!empty($parsed['fragment'])) {
            $relative .= '#' . $parsed['fragment'];
        }

        return $relative;
    }

    /**
     * Get the notification's delivery channels.
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
            ->action('View', $this->actionUrl ? url($this->actionUrl) : url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement',
            'category' => 'system', // System category
            'title' => $this->title,
            'message' => $this->message,
            'action_url' => $this->actionUrl, // Now stores relative URL
            'is_pinned' => $this->isPinned,
            'post_id' => $this->post ? $this->post->id : null,
            'post_title' => $this->post ? $this->post->title : null,
            'created_at' => now(),
        ];
    }
}
