<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
use App\UI\ImageUrls;
class ReminderAddStateButton extends VariableDependentStateButton {
	public $action = '/#/app/reminder-add/:variableName';
	public $fontAwesome = 'far fa-bell';
	public $icon = 'ion-android-notifications-none';
	public $id = 'reminder-add-state-button';
	public $image = ImageUrls::BASIC_FLAT_ICONS_BELL;
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/reminder-add/:variableName';
	public $stateName = 'app.reminderAdd';
	public $stateParams = [];
	public $text = 'Add Reminder';
	public $title = 'Add Reminder';
	public $menus = [];
}
