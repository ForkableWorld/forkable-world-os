<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class MapStateButton extends IonicButton {
	public $accessibilityText = 'Map';
	public $action = '/#/app/map';
	public $fontAwesome = 'fab fa-canadian-maple-leaf';
	public $icon = 'ion-map';
	public $id = 'map-state-button';
	public $image = 'https://static.quantimo.do/img/basic-flat-icons/png/map.png';
	public $ionIcon = 'ion-map';
	public $link = '/#/app/map';
	public $stateName = 'app.map';
	public $stateParams = [];
	public $text = 'Map';
	public $title = 'Map';
	public $tooltip = 'Map';
	public $menus = [];
}
