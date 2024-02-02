<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\User;
use App\Models\WpPost;
use App\UI\ImageUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
class NewPost extends BaseNotification implements ShouldQueue
{
    use Queueable;
    protected $following;
    protected $post;
    /**
     * Create a new notification instance.
     * @param User $following
     * @param WpPost $post
     */
    public function __construct(User $following, WpPost $post){
        $this->following = $following;
        parent::__construct($post);
    }
    public function via($notifiable): array{
        return ['database', 'broadcast'];
    }
    public function toDatabase($notifiable): array{
        return [
            'following_id' => $this->following->ID,
            'following_name' => $this->following->display_name,
            'post_id' => $this->post->ID,
        ];
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage{
        return (new MailMessage)
                    ->subject($this->title)
                    ->line($this->body)
                    ->action('View Post', url('/'))
                    ->line('I love you!');
    }
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array{
        return [
            'id' => $this->id,
            'read_at' => null,
            'data' => $this->toDatabase($notifiable),
        ];
    }
    public function getTitleAttribute(): string{
        return $this->getSourceObject()->getTitleAttribute();
    }
    public function getBody(): string{
        return "Click to see the new post!";
    }
    public function getIcon(): string{
        return ImageUrls::ACTIVITIES_OPEN_BOOK;
    }
    public function getUrl(array $params = []): string{
        return $this->getSourceObject()->getUrl();
    }
}
