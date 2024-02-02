<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class HelpStateButton extends IonicButton {
	public $accessibilityText = 'Help';
	public $action = '/#/app/help';
	public $fontAwesome = 'fas fa-hands-helping';
	public $icon = 'ion-help';
	public $id = 'help-state-button';
	public $image = 'https://static.quantimo.do/img/basic-flat-icons/png/help.png';
	public $ionIcon = 'ion-help';
	public $link = '/#/app/help';
	public $stateName = 'app.help';
	public $stateParams = [];
	public $text = 'Help';
	public $title = 'Help';
	public $tooltip = 'Help';
	public $menus = [];
}
