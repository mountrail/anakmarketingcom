<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Post;
use App\Models\Answer;
use App\Models\User;

class PostAnsweredNotification extends Notification
{
    use Queueable;

    protected $post;
    protected $answer;
    protected $answerer;

    /**
     * Create a new notification instance.
     */
    public function __construct(Post $post, Answer $answer, User $answerer)
    {
        $this->post = $post;
        $this->answer = $answer;
        $this->answerer = $answerer;
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
            ->line('Someone answered your question.')
            ->action('View Answer', url('/posts/' . $this->post->id))
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
            'type' => 'post_answered',
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'answer_id' => $this->answer->id,
            'answerer_id' => $this->answerer->id,
            'answerer_name' => $this->answerer->name,
            'answerer_avatar' => $this->answerer->avatar,
            'message' => $this->answerer->name . ' menjawab pertanyaan Anda: "' . \Str::limit($this->post->title, 50) . '"',
            'action_url' => route('posts.show', $this->post->id),
            'created_at' => now(),
        ];
    }
}
