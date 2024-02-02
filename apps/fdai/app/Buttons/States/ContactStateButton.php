<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class ContactStateButton extends IonicButton {
	public $accessibilityText = 'Feedback';
	public $action = '/#/app/contact';
	public $fontAwesome = 'fab fa-facebook-messenger';
	public $icon = 'ion-android-chat';
	public $id = 'contact-state-button';
	public $image = 'https://static.quantimo.do/img/dialogue-assets/png/messenger.png';
	public $ionIcon = 'ion-android-chat';
	public $link = '/#/app/contact';
	public $stateName = 'app.contact';
	public $stateParams = [];
	public $text = 'Feedback';
	public $title = 'Feedback';
	public $tooltip = 'Feedback';
	public $menus = [];
}
