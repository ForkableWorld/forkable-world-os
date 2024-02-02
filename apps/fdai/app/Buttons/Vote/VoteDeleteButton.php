<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Vote;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class VoteDeleteButton extends QMButton {
	public $fontAwesome = FontAwesome::DELETE;
	/**
	 * @param string $url
	 * @param array $params
	 * @param string $causeVariableName
	 * @param string $effectVariableName
	 * @param string $text
	 */
	public function __construct(string $url, array $params, string $causeVariableName, string $effectVariableName,
		string $text){
		$params['vote'] = "none";
		parent::__construct($text, null, null, IonIcon::androidDelete);
		$this->setWebhookUrl($url);
		$this->setFunctionName("vote");
		$this->setParameters($params);
		$message = $causeVariableName . " could influence " . $effectVariableName;
		$this->setConfirmationText("Are you unsure if $message?");
		$this->setSuccessToastText("Thank you for teaching me!");
		$this->setAdditionalInformationAndTooltip("Delete Vote");
	}
}
