<?php

// PostAnsweredNotification.php - Updated to use slug
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

    public function __construct(Post $post, Answer $answer, User $answerer)
    {
        $this->post = $post;
        $this->answer = $answer;
        $this->answerer = $answerer;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('Someone answered your question.')
            ->action('View Answer', route('posts.show', $this->post->slug))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $postType = $this->post->type === 'question' ? 'pertanyaan' : 'diskusi';

        return [
            'type' => 'post_answered',
            'category' => 'content', // Content-related category
            'post_id' => $this->post->id,
            'post_title' => $this->post->title,
            'post_type' => $this->post->type,
            'answer_id' => $this->answer->id,
            'answerer_id' => $this->answerer->id,
            'answerer_name' => $this->answerer->name,
            'message' => $this->answerer->name . ' menjawab ' . $postType . ' Anda. Klik untuk melihat!',
            'action_url' => '/' . $this->post->slug, // Use slug
            'created_at' => now(),
        ];
    }
}
