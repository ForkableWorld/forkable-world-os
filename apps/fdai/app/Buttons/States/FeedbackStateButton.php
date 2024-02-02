<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class FeedbackStateButton extends IonicButton {
	public $accessibilityText = 'Feedback';
	public $action = '/#/app/feedback';
	public $fontAwesome = 'fab fa-facebook-messenger';
	public $icon = 'ion-speakerphone';
	public $id = 'feedback-state-button';
	public $image = 'https://static.quantimo.do/img/dialogue-assets/png/messenger.png';
	public $ionIcon = 'ion-speakerphone';
	public $link = '/#/app/feedback';
	public $stateName = 'app.feedback';
	public $stateParams = [];
	public $text = 'Feedback';
	public $title = 'Feedback';
	public $tooltip = 'Feedback';
	public $menus = [];
}
