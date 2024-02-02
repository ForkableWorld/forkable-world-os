<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class HistoryAllVariableStateButton extends VariableDependentStateButton {
	public $action = '/#/app/history-all-variable/:variableName';
	public $fontAwesome = 'fas fa-history';
	public $icon = 'ion-ios-list-outline';
	public $id = 'history-all-variable-state-button';
	public $image = 'https://static.quantimo.do/img/jetbrains/darcula/searchWithHistory.png';
	public $ionIcon = 'ion-ios-list-outline';
	public $link = '/#/app/history-all-variable/:variableName';
	public $stateName = 'app.historyAllVariable';
	public $stateParams = [];
	public $text = 'History';
	public $title = 'History';
	public $menus = [];
}
