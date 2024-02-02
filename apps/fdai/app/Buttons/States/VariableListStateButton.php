<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class VariableListStateButton extends IonicButton {
	public $accessibilityText = 'Manage Variables';
	public $action = '/#/app/variable-list';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-android-notifications-none';
	public $id = 'variable-list-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/variable-list';
	public $stateName = 'app.variableList';
	public $stateParams = [];
	public $text = 'Manage Variables';
	public $title = 'Manage Variables';
	public $tooltip = 'Manage Variables';
	public $menus = [];
}
