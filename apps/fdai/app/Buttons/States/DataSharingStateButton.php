<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class DataSharingStateButton extends IonicButton {
	public $accessibilityText = 'Manage Data Sharing';
	public $action = '/#/app/data-sharing';
	public $fontAwesome = 'fas fa-clinic-medical';
	public $icon = 'ion-locked';
	public $id = 'data-sharing-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-locked';
	public $link = '/#/app/data-sharing';
	public $stateName = 'app.dataSharing';
	public $stateParams = [];
	public $text = 'Manage Data Sharing';
	public $title = 'Manage Data Sharing';
	public $tooltip = 'Manage Data Sharing';
	public $menus = [];
}
