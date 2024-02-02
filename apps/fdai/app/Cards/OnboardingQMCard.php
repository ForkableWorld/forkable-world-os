<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\AppSettings\AppSettings;
use App\Exceptions\ClientNotFoundException;
use App\Models\Application;
class OnboardingQMCard extends QMCard {
	public $type = QMCard::TYPE_onboarding;
	public function __construct(){
		parent::__construct();
	}
	/**
	 * @return QMCard[]
	 */
	public static function getOnboardingCards(){
		try {
			$appSettings = Application::getByClientId();
		} catch (ClientNotFoundException $e) {
			le($e);
		}
		$cards = $appSettings->getAppDesign()->getOnboarding()->getActiveAsCards();
		return $cards;
	}
}
