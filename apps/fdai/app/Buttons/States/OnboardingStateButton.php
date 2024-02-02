<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class OnboardingStateButton extends IonicButton {
	public $accessibilityText = 'Getting Started';
	public $action = '/#/app/onboarding';
	public $icon = 'ion-android-notifications-none';
	public $id = 'onboarding-state-button';
	public $link = '/#/app/onboarding';
	public $stateName = 'app.onboarding';
	public $stateParams = [];
	public $text = 'Getting Started';
	public $menus = [];
	public $ionIcon = IonIcon::power;
	public $fontAwesome = FontAwesome::EDIT;
	public $title = "Start Tracking";
	public $image = ImageUrls::BASIC_FLAT_ICONS_HELP;
	public $tooltip = "Going through the onboarding process makes it easy to tracking and discover hidden factors affecting your well-being!";
	public static function url($params = []): string{
		return parent::url($params); 
	}
}
