<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class NotificationPreferencesStateButton extends IonicButton {
	public $accessibilityText = 'Notification Settings';
	public $action = '/#/app/notificationPreferences';
	public $fontAwesome = 'fas fa-cog';
	public $icon = 'ion-android-notifications';
	public $id = 'notification-preferences-state-button';
	public $image = 'https://static.quantimo.do/img/essential-collection/png/notification.png';
	public $ionIcon = 'ion-android-notifications';
	public $link = '/#/app/notificationPreferences';
	public $stateName = 'app.notificationPreferences';
	public $stateParams = [];
	public $text = 'Notification Settings';
	public $title = 'Notification Settings';
	public $tooltip = 'Notification Settings';
	public $menus = [];
}
