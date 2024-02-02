<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Utils\IonicHelper;
use App\Variables\QMVariable;
class MeasurementAddVariableStateButton extends VariableDependentStateButton {
	public $fontAwesome = 'fas fa-record-vinyl';
	public $icon = 'ion-compose';
	public $id = 'measurement-add-variable-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/wordpress/add-measurement-wordpress-screenshot.png';
	public $ionIcon = 'ion-compose';
	public $stateName = 'app.measurementAddVariable';
	public $stateParams = [];
	public $text = 'Record a Measurement';
	public $title = 'Record a Measurement';
	public $shortTitle = '?';
	public $menus = [];
	/**
	 * @param QMTrackingReminderNotification|QMVariable $variableReminderOrNotification
	 * @param array $params
	 * @param string|null $title
	 */
	public function __construct($variableReminderOrNotification, array $params = [], string $title = null){
		if($variableReminderOrNotification instanceof QMTrackingReminderNotification){
			$n = $variableReminderOrNotification;
			$params['startAt'] = $n->getAt();
			$params['trackingReminderNotificationId'] = $n->getId();
		}
		$params['variableCategoryName'] = $variableReminderOrNotification->getVariableCategoryName();
		$params['unitAbbreviatedName'] = $variableReminderOrNotification->getUserUnit()->getAbbreviatedName();
		$params['variableName'] = $variableReminderOrNotification->getVariableName();
		parent::__construct($variableReminderOrNotification, $params);
		if($title){
			$this->setTextAndTitle($title);
		}
		$url = IonicHelper::getIonicAppUrl(null, "measurement-add-variable-name/" . $this->getUrlEncodedVariableName(),
			$params);
		$this->setUrl($url);
		$this->setAction($url);
	}
}
