<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\User;

class FollowedUserPostedNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $poster;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post, User $poster)
    {
        $this->post = $post;
        $this->poster = $poster;
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
            ->line('Someone you follow posted a new question.')
            ->action('View Post', url('/posts/' . $this->post->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $postType = $this->post->type === 'question' ? 'pertanyaan' : 'diskusi';

        return [
            'type' => 'followed_user_posted',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_type' => $this->post->type,
            'poster_id' => $this->poster->id,
            'poster_name' => $this->poster->name,
            'poster_avatar' => $this->poster->getProfileImageUrl(),
            'message' => $this->poster->name . ' yang Anda ikuti, memposting ' . $postType . ' baru. Klik untuk melihat!',
            'action_url' => route('posts.show', $this->post->id),
            'created_at' => now(),
        ];
    }
}
