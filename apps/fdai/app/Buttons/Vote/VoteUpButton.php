<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Vote;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class VoteUpButton extends QMButton {
	public $fontAwesome = FontAwesome::THUMBS_UP;
	/**
	 * YesNotificationButton constructor.
	 * @param string $url
	 * @param array $params
	 * @param string $causeVariableName
	 * @param string $effectVariableName
	 * @param string $text
	 */
	public function __construct(string $url, array $params, string $causeVariableName, string $effectVariableName,
		string $text){
		$params['vote'] = "up";
		parent::__construct($text, null, null, IonIcon::ion_icon_thumbs_up);
		$this->setWebhookUrl($url);
		$this->setFunctionName("vote");
		$this->setParameters($params);
		$message = "seems plausible that " . $causeVariableName . " could influence " . $effectVariableName;
		$this->setConfirmationText("Do you agree that it $message?");
		$this->setSuccessToastText("Thank you for teaching me!");
		$this->setAdditionalInformationAndTooltip("Up Vote");
	}
}
