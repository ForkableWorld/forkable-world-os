<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class TodayMedScheduleStateButton extends IonicButton {
	public $accessibilityText = 'Today\'s Med Schedule';
	public $action = '/#/app/today-med-schedule';
	public $fontAwesome = 'fas fa-clinic-medical';
	public $icon = 'ion-android-notifications-none';
	public $id = 'today-med-schedule-state-button';
	public $image = 'https://static.quantimo.do/img/activities/png/medal.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/today-med-schedule';
	public $stateName = 'app.todayMedSchedule';
	public $stateParams = [];
	public $text = 'Today\'s Med Schedule';
	public $title = 'Today\'s Med Schedule';
	public $tooltip = 'Today\'s Med Schedule';
	public $menus = [];
}
