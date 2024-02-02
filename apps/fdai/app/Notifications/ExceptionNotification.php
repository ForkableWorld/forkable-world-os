<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;

use App\Logging\QMLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class ExceptionNotification extends Notification
{
    use Queueable;
	public QMLog $QMLog;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
	public function __construct(QMLog $logMessage){
		$this->QMLog = $logMessage;
	}

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable): array{
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable): MailMessage{
        return (new MailMessage)->line($this->QMLog->getMessage())
                                ->action('Download PHPUnit Test', $this->QMLog->getUrl())
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable): array{
        return [
            //
        ];
    }
}
