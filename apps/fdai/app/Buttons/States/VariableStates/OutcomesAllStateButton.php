<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class OutcomesAllStateButton extends VariableDependentStateButton {
	public $action = '/#/app/outcomes/:causeVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'outcomes-all-state-button';
	public $image = 'https://static.quantimo.do/img/business-strategy/png/target.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/outcomes/:causeVariableName';
	public $stateName = 'app.outcomesAll';
	public $stateParams = [];
	public $text = 'Top Outcomes';
	public $title = 'Top Outcomes';
	public $menus = [];
}
