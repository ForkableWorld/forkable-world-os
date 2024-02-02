<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\Models\Study;
use App\UI\ImageUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
class StudyPublished extends BaseNotification
{
    use Queueable;
    /**
     * @param Study $study
     */
    public function __construct(Study $study){
        parent::__construct($study);
    }
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage{
        return (new MailMessage)
                    ->subject($this->getTitleAttribute())
                    ->line("Your study has been published!")
                    ->line($this->getBody())
                    ->action('Go to Study', $this->getUrl())
                    ->line('Thank you!');
    }
    public function getTitleAttribute(): string{
        return $this->getSourceObject()->getTitleAttribute()." Published!";
    }
    public function getBody(): string{
        return "Click to see the new study!";
    }
    public function getIcon(): string{
        return ImageUrls::STUDY;
    }
    public function getUrl(array $params = []): string{
        return $this->getSourceObject()->getUrl();
    }
}
