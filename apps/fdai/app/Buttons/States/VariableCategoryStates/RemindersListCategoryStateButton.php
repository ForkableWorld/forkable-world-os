<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableCategoryStates;
use App\Buttons\VariableCategoryStateButton;
class RemindersListCategoryStateButton extends VariableCategoryStateButton {
	public $action = '/#/app/reminders-list-category/:variableCategoryName';
	public $fontAwesome = 'far fa-bell';
	public $icon = 'ion-android-notifications-none';
	public $id = 'reminders-list-category-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/reminders-list-category/:variableCategoryName';
	public $stateName = 'app.remindersListCategory';
	public $stateParams = [];
	public $text = 'Manage Reminders';
	public $title = 'Manage Reminders';
	public $menus = [];
	public $tooltip = "Change the settings for your tracking reminders (like notification frequency).";
}
