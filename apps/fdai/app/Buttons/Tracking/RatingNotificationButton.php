<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Tracking;
use App\Logging\QMLog;
use App\Slim\Controller\TrackingReminder\PostTrackingReminderNotificationsController;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\UI\ImageHelper;
use App\Variables\QMVariable;
class RatingNotificationButton extends NotificationButton {
	/**
	 * @param int $modifiedValue
	 * @param QMVariable $n
	 */
	public function __construct(int $modifiedValue, QMVariable $n = null){
		$callbacks[1] = 'trackOneRatingAction';
		$callbacks[2] = 'trackTwoRatingAction';
		$callbacks[3] = 'trackThreeRatingAction';
		$callbacks[4] = 'trackFourRatingAction';
		$callbacks[5] = 'trackFiveRatingAction';
		if(!isset($callbacks[$modifiedValue])){
			QMLog::exceptionIfNotProduction("$modifiedValue is not a valid rating value", [
				'debug url' => $n->getUrl(),
				'notification' => $n,
			]);
		} else{
			$this->setRatingNotificationButtonImage($modifiedValue, $n);
			parent::__construct(QMUnit::getUnitByAbbreviatedName("/5"), $callbacks[$modifiedValue], $modifiedValue,
				QMTrackingReminderNotification::TRACK, $n);
			$this->setImageHtml($this->image);
		}
		$this->webhookUrl = PostTrackingReminderNotificationsController::getUrl();
	}
	/**
	 * @param int $modifiedValue
	 * @param QMVariable $n
	 */
	public function setRatingNotificationButtonImage(int $modifiedValue, QMVariable $n){
		$valence = $n->getValence();
		$this->image = ImageHelper::getRatingImageUrl($valence, $modifiedValue);
	}
	public function getUrl(array $params = []): string{
		return $this->webhookUrl;
	}
}
