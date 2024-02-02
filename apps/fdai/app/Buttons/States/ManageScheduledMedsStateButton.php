<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class ManageScheduledMedsStateButton extends IonicButton {
	public $accessibilityText = 'Manage Scheduled Meds';
	public $action = '/#/app/manage-scheduled-meds';
	public $fontAwesome = 'fas fa-clinic-medical';
	public $icon = 'ion-android-notifications-none';
	public $id = 'manage-scheduled-meds-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/manage-scheduled-meds';
	public $stateName = 'app.manageScheduledMeds';
	public $stateParams = [];
	public $text = 'Manage Scheduled Meds';
	public $title = 'Manage Scheduled Meds';
	public $tooltip = 'Manage Scheduled Meds';
	public $menus = [];
}
