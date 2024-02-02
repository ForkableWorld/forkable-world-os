<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class RemindersInboxCompactStateButton extends IonicButton {
	public $accessibilityText = 'Reminder Inbox';
	public $action = '/#/app/reminders-inbox-compact';
	public $fontAwesome = 'far fa-bell';
	public $icon = 'ion-android-inbox';
	public $id = 'reminders-inbox-compact-state-button';
	public $image = 'https://static.quantimo.do/img/dialogue-assets/png/inbox-1.png';
	public $ionIcon = 'ion-android-inbox';
	public $link = '/#/app/reminders-inbox-compact';
	public $stateName = 'app.remindersInboxCompact';
	public $stateParams = [];
	public $text = 'Reminder Inbox';
	public $title = 'Reminder Inbox';
	public $tooltip = 'Reminder Inbox';
	public $menus = [];
}
