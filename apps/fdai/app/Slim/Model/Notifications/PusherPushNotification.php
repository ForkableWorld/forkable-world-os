<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\Cards\UserRelatedQMCard;
use App\Logging\QMLog;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Traits\HasClassName;
use App\Traits\LoggerTrait;
use Exception;
use Pusher\Pusher;
use Pusher\PusherException;
class PusherPushNotification {
	use LoggerTrait, HasClassName;
	private $card;
	/**
	 * @param UserRelatedQMCard $card
	 * @throws PusherException
	 */
	public function __construct($card){
		$this->card = $card;
		$pusher = self::getPusher();
		$channel = self::getChannel($card->getUserId());
		$notificationId = $channel . '_' . $card->getId();
		/** @var PushNotificationData $previous */
		$previous = Memory::get($notificationId, Memory::SENT_PUSH_NOTIFICATIONS);
		if($previous){
			$this->logInfo("Already sent " . $previous->title . " to $channel");
			return;
		}
		Memory::set($notificationId, Memory::SENT_PUSH_NOTIFICATIONS);
		$this->logInfo("Sending pusher push to $channel: " . json_encode($card));
		$pusher->trigger($channel, 'my-event', $card);
	}
	/**
	 * @param $userId
	 * @return string
	 */
	public static function getChannel($userId): string{
		return 'user-' . $userId;
	}
	/**
	 * @return Pusher
	 * @throws PusherException
	 */
	public static function getPusher(): Pusher{
		$options = [
			'cluster' => 'us2',
			'encrypted' => true,
		];
		$pusher = new Pusher('4e7cd12d82bff45e4976', '30300e14b300cb0fb3cf', '584259', $options);
		return $pusher;
	}
	/**
	 * @param QMUser|null $user
	 * @param string|null $channel
	 * @return bool
	 * @throws PusherException
	 */
	public static function sendUserViaPushForIFrameAuth(QMUser $user = null, string $channel = null): bool{
		if(!$user){
			$user = QMAuth::getQMUser();
		}
		if(!$channel || !is_string($channel)){
			QMLog::info("Could not getGoogleAnalyticsCookie channel for pusher to send user back to app within iFrame! " .
				" If you need todo this, add Google Analytics to " . QMRequest::getReferrer());
			return false;
		}
		$pusher = self::getPusher();
		if(!$user){
			QMLog::error("No user to sendUserViaPushForIFrameAuth");
			return false;
		}
		$uniquePushId = $channel . '_user_' . $user->id;
		/** @var PushNotificationData $previous */
		$previous = Memory::get($uniquePushId, Memory::SENT_PUSH_NOTIFICATIONS);
		if($previous){
			QMLog::error("Already sent $user to $uniquePushId to $channel");
		}
		Memory::set($uniquePushId, $user, Memory::SENT_PUSH_NOTIFICATIONS);
		QMLog::info("Sending user pusher push to $channel for $user");
		try {
			$pusher->trigger($channel, 'user', $user);
			return true;
		} catch (Exception $e) {
			QMLog::info(__METHOD__.": ".$e->getMessage());
			return false;
		}
	}
	public function __toString(){
		return $this->card->getTitleAttribute() . " " . (new \ReflectionClass(static::class))->getShortName();
	}
}
