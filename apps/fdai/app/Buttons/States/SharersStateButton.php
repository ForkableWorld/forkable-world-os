<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class SharersStateButton extends IonicButton {
	public $accessibilityText = 'Data Sharers';
	public $action = '/#/app/sharers';
	public $fontAwesome = 'fas fa-database';
	public $icon = 'ion-people';
	public $id = 'sharers-state-button';
	public $image = 'https://static.quantimo.do/img/Better-World-Through-Data-1200-630.png';
	public $ionIcon = 'ion-people';
	public $link = '/#/app/sharers';
	public $stateName = 'app.sharers';
	public $stateParams = [];
	public $text = 'Data Sharers';
	public $title = 'Data Sharers';
	public $tooltip = 'Data Sharers';
	public $menus = [];
}
