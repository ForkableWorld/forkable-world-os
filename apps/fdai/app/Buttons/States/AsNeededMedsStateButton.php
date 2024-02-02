<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class AsNeededMedsStateButton extends IonicButton {
	public $accessibilityText = 'As Needed Meds';
	public $action = '/#/app/as-needed-meds';
	public $fontAwesome = 'fas fa-clinic-medical';
	public $icon = 'ion-star';
	public $id = 'as-needed-meds-state-button';
	public $image = 'https://static.quantimo.do/img/';
	public $ionIcon = 'ion-star';
	public $link = '/#/app/as-needed-meds';
	public $stateName = 'app.asNeededMeds';
	public $stateParams = [];
	public $text = 'As Needed Meds';
	public $title = 'As Needed Meds';
	public $tooltip = 'As Needed Meds';
	public $menus = [];
}
