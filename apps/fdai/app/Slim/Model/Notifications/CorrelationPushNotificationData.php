<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Model\Notifications;
use App\VariableRelationships\QMUserVariableRelationship;
/** Class CorrelationNotificationData
 * @package App\Slim\Model\Notifications
 */
class CorrelationPushNotificationData extends PushNotificationData {
	/**
	 * @param QMUserVariableRelationship $c
	 */
	public function __construct(QMUserVariableRelationship $c){
		parent::__construct();
		$title = $c->getPredictorExplanationTitle(false);
		if(empty($title)){
			le("Could not send CorrelationNotification because could not get last cause sentence");
		}
		$this->setTitle($title);
		$message = $c->changeFromBaselineSentence(true);
		if(stripos($message, "above average.") !== false){
			le("above average.");
		}
		$this->setMessage($message);
		// https://github.com/phonegap/phonegap-plugin-push/commit/2660b51da66e791ff342d027ea6afa4313281e28
		$this->setNotId($c->causeVariableId . $c->effectVariableId); // notId required to wake up app
		$links = $c->getStudyLinks();
		$url = $links->getStudyLinkStatic();
		$this->setUrl($url);
		$this->setImage($c->getEffectVariableImage());
		$this->setForceStart(0);
	}
}
