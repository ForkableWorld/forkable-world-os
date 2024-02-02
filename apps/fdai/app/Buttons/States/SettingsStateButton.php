<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class SettingsStateButton extends IonicButton {
	public $accessibilityText = 'Settings';
	public $action = '/#/app/settings';
	public $fontAwesome = 'fas fa-cog';
	public $icon = 'ion-settings';
	public $id = 'settings-state-button';
	public $image = 'https://static.quantimo.do/img/audio-and-video-controls/png/settings.png';
	public $ionIcon = 'ion-settings';
	public $link = '/#/app/settings';
	public $stateName = 'app.settings';
	public $stateParams = [];
	public $text = 'Settings';
	public $title = 'Settings';
	public $tooltip = 'Settings';
	public $menus = [];
}
