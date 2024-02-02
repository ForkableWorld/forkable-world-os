<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class TrackStateButton extends IonicButton {
	public $accessibilityText = 'Track Primary Outcome';
	public $action = '/#/app/track';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-compose';
	public $id = 'track-state-button';
	public $image = 'https://static.quantimo.do/img/business-strategy/png/target.png';
	public $ionIcon = 'ion-compose';
	public $link = '/#/app/track';
	public $stateName = 'app.track';
	public $stateParams = [];
	public $text = 'Track Primary Outcome';
	public $title = 'Track Primary Outcome';
	public $tooltip = 'Track Primary Outcome';
	public $menus = [];
}
