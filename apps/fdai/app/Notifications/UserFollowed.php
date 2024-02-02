<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\User;
use Illuminate\Bus\Queueable;
class UserFollowed extends BaseNotification
{
    use Queueable;
    /**
     * @var User
     */
    protected $sourceObject;
    /**
     * Create a new notification instance.
     * @param User $follower
     */
    public function __construct(User $follower){
        $this->title = "New Follower";
        $this->body =  $follower->display_name." followed you!";
        $this->url =  $follower->getUrl();
        $this->icon =  $follower->getImage();
        parent::__construct($follower);
    }
    public function toDatabase($notifiable): array{
        return [
            'follower_id' => $this->sourceObject->ID,
            'follower_name' => $this->sourceObject->display_name,
        ];
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
            'data' => [
                'follower_id' => $this->sourceObject->ID,
                'follower_name' => $this->sourceObject->display_name,
            ],
        ];
    }
    public function getTitleAttribute(): string{
        return $this->title;
    }
    public function getBody(): string{
        return $this->body;
    }
    public function getIcon(): string{
        return $this->icon;
    }
    public function getUrl(array $params = []): string{
        return $this->url;
    }
}
