<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Notifications;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
/** @noinspection PhpUnusedParameterInspection */
class ServerDownNotification extends Notification {
	public function __construct(){
		//
	}
	/**
	 * Get the notification's delivery channels.
	 * @param mixed $notifiable
	 * @return array
	 */
	public function via($notifiable): array{
		return ['nexmo'];
	}
	/**
	 * Get the Nexmo / SMS representation of the notification.
	 * @param mixed $notifiable
	 * @return NexmoMessage
	 */
	public function toNexmo($notifiable): NexmoMessage{
		return (new NexmoMessage)->content('SMS notifications work only with laravel/nexmo-notification-channel package');
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
