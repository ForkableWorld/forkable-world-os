<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class ChartsStateButton extends VariableDependentStateButton {
	public $action = '/#/app/charts/:variableName';
	public $fontAwesome = 'fas fa-chart-line';
	public $icon = 'ion-arrow-graph-up-right';
	public $id = 'charts-state-button';
	public $image = 'https://static.quantimo.do/img/features/graph-1.png';
	public $ionIcon = 'ion-arrow-graph-up-right';
	public $link = '/#/app/charts/:variableName';
	public $stateName = 'app.charts';
	public $stateParams = [];
	public $text = 'Charts';
	public $title = 'Charts';
	public $menus = [];
}
