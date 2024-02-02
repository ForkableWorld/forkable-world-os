<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class RemindersInboxTodayStateButton extends IonicButton {
	public $accessibilityText = 'Inbox';
	public $action = '/#/app/reminders-inbox-today';
	public $fontAwesome = 'fas fa-inbox';
	public $icon = 'ion-android-inbox';
	public $id = 'reminders-inbox-today-state-button';
	public $image = 'https://static.quantimo.do/img/dialogue-assets/png/inbox-1.png';
	public $ionIcon = 'ion-android-inbox';
	public $link = '/#/app/reminders-inbox-today';
	public $stateName = 'app.remindersInboxToday';
	public $stateParams = [];
	public $text = 'Inbox';
	public $title = 'Inbox';
	public $tooltip = 'Inbox';
	public $menus = [];
}
