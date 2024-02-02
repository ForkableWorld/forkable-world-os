<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableCategoryStates;
use App\Buttons\VariableCategoryStateButton;
class VariableListCategoryStateButton extends VariableCategoryStateButton {
	public $action = '/#/app/variable-list-category/:variableCategoryName';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-android-notifications-none';
	public $id = 'variable-list-category-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/management.png';
	public $ionIcon = 'ion-android-notifications-none';
	public $link = '/#/app/variable-list-category/:variableCategoryName';
	public $stateName = 'app.variableListCategory';
	public $stateParams = [];
	public $text = 'Manage Variables';
	public $title = 'Manage Variables';
	public $menus = [];
	public $tooltip = "Change the settings for your tracking reminders (like notification frequency).";
}
