<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
use App\Models\TrackingReminderNotification;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
class TrackingReminderNotificationEmail extends QMMailable {
	public $userName;
	private $trackingMessage;
	private $unique_args;
	/**
	 * @return Builder
	 */
	public static function usersQB(): Builder{
		$qb = parent::usersQB();
		$notifications = TrackingReminderNotification::whereNull(TrackingReminderNotification::FIELD_DELETED_AT)
			->where('notify_at', '<', Carbon::now())
			->where('notify_at', '>', Carbon::now()->subtract(CarbonInterval::days(1)))
			->groupBy('user_id')
			->get();
		$ids = $notifications->pluck('user_id')->toArray();
		$qb->getQuery()
		   ->whereIn(User::FIELD_ID, $ids)
			->where('send_reminder_notification_emails', 1);
//			->whereRaw('trn.notify_at < NOW()')
		return $qb;
	}
	/**
	 * Build the message.
	 * @return $this
	 */
	public function build(){
		$user = $this->getUser();
		$this->userName = $user->display_name;
		$this->trackingMessage = "Time to track!";
		$this->unique_args = ['user_id' => $user->getId()];
		$this->subject("How are you?");
		return $this->view('email.tracking-reminder-notifications-faces', $this->getParams());
	}
	/**
	 * @return array
	 */
	public function getParams(): array{
		$params = parent::getParams();
		$params['userName'] = $this->user->display_name;
		$params['unique_args'] = ['user_id' => $this->user->ID];
		return $params;
	}
}
