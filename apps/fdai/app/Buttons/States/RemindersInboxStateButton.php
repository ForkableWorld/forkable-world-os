<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\QMColor;
class RemindersInboxStateButton extends IonicButton {
	public $accessibilityText = 'Reminder Inbox';
	public $action = '/#/app/reminders-inbox';
	public $fontAwesome = 'far fa-bell';
	public $icon = 'ion-android-inbox';
	public $id = 'reminders-inbox-state-button';
	public $image = ImageUrls::DIALOGUE_ASSETS_INBOX_3;
	public $ionIcon = IonIcon::ION_ICON_INBOX;
	public $link = '/#/app/reminders-inbox';
	public $stateName = 'app.remindersInbox';
	public $stateParams = [];
	public $text = 'Go to Inbox';
	public $title = 'Go to Inbox';
	public $tooltip = 'Go to Inbox';
	public $menus = [];
	public $color = QMColor::HEX_GOOGLE_GREEN;


}
