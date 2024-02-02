<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableCategoryStates;
use App\Buttons\VariableCategoryStateButton;
class HistoryAllCategoryStateButton extends VariableCategoryStateButton {
	public $action = '/#/app/history-all-category/:variableCategoryName';
	public $fontAwesome = 'fas fa-history';
	public $icon = 'ion-ios-list-outline';
	public $id = 'history-all-category-state-button';
	public $image = 'https://static.quantimo.do/img/jetbrains/darcula/searchWithHistory.png';
	public $ionIcon = 'ion-ios-list-outline';
	public $link = '/#/app/history-all-category/:variableCategoryName';
	public $stateName = 'app.historyAllCategory';
	public $stateParams = [];
	public $text = 'History';
	public $title = 'History';
	public $menus = [];
	public $tooltip = "See past measurements";
}
