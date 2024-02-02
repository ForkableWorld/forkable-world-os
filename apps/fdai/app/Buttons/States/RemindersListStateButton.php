<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class RemindersListStateButton extends IonicButton {
	public $accessibilityText = 'Manage Reminders';
	public $action = '/#/app/reminders-list';
	public $fontAwesome = 'far fa-bell';
	public $icon = 'ion-android-notifications-none';
	public $id = 'reminders-list-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/reminders-list';
	public $stateName = 'app.remindersList';
	public $stateParams = [];
	public $text = 'Manage Reminders';
	public $title = 'Manage Reminders';
	public $tooltip = 'Manage Reminders';
	public $menus = [];
}
