<?php
// FollowedUserPostedNotification.php - Updated
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\User;

// FollowedUserPostedNotification.php - Updated to use slug
class FollowedUserPostedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $poster;

    public function __construct(Post $post, User $poster)
    {
        $this->post = $post;
        $this->poster = $poster;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Someone you follow posted a new question.')
            ->action('View Post', route('posts.show', $this->post->slug))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $postType = $this->post->type === 'question' ? 'pertanyaan' : 'diskusi';

        return [
            'type' => 'followed_user_posted',
            'category' => 'content', // Content-related category
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_type' => $this->post->type,
            'poster_id' => $this->poster->id,
            'poster_name' => $this->poster->name,
            'message' => $this->poster->name . ' yang Anda ikuti, memposting ' . $postType . ' baru. Klik untuk melihat!',
            'action_url' => '/posts/' . $this->post->slug, // Use slug
            'created_at' => now(),
        ];
    }
}
