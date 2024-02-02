<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnusedParameterInspection */
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
class MailNotification extends Notification implements ShouldQueue {
	use Queueable;
	public function __construct(){
		//
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
		return (new MailMessage)->line('The introduction to the notification.')->action('Notification Action', url('/'))
		                        ->line('Thank you for using our application!');
	}
	/**
	 * Get the array representation of the notification.
	 * @param mixed $notifiable
	 * @return array
	 */
	public function toArray($notifiable): array{
		return [//
		];
	}
}
