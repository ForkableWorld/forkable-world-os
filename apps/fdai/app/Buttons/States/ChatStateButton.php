<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class ChatStateButton extends IonicButton {
	public $accessibilityText = 'Talk to Dr. Modo';
	public $action = '/#/app/chat';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-chatbox';
	public $id = 'chat-state-button';
	public $image = 'https://static.quantimo.do/img/apps/energymodo/resources/android/custom/ic_stat_icon_bw.png';
	public $ionIcon = 'ion-chatbox';
	public $link = '/#/app/chat';
	public $stateName = 'app.chat';
	public $stateParams = [];
	public $text = 'Talk to Dr. Modo';
	public $title = 'Talk to Dr. Modo';
	public $tooltip = 'Talk to Dr. Modo';
	public $menus = [];
}
