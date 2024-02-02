<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use App\UI\ImageUrls;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use App\Reports\AnalyticalReport;
class DailyGradeReport extends BaseNotification {
    use Queueable;
    /** @var AnalyticalReport */
    protected $sourceObject;
    /**
     * Create a new notification instance.
     * @param AnalyticalReport $r
     */
    public function __construct(AnalyticalReport $r){
        parent::__construct($r);
    }
    /**
     * Get the notification's delivery channels.
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return ['mail'];
    }
    /**
     * Get the mail representation of the notification.
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable): MailMessage{
        return (new MailMessage)
            ->subject($this->getTitleAttribute())
            ->markdown('mail.daily-grade-report', $this->toArray($notifiable));
    }
    public function getTitleAttribute(): string{
        return $this->getSourceObject()->getTitleAttribute()." Ready!";
    }
    public function getBody(): string{
        return "Click to see results!";
    }
    public function getIcon(): string{
        return ImageUrls::ACTIVITIES_OPEN_BOOK;
    }
    public function getUrl(array $params = []): string{
        return $this->getSourceObject()->getUrl();
    }
}
