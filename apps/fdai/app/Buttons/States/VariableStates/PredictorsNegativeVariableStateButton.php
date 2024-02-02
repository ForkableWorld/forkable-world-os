<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class PredictorsNegativeVariableStateButton extends VariableDependentStateButton {
	public $action = '/#/app/predictors-negative-variable/:effectVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-negative-variable-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors-negative-variable/:effectVariableName';
	public $stateName = 'app.predictorsNegativeVariable';
	public $stateParams = [];
	public $text = 'Negative Predictors';
	public $title = 'Negative Predictors';
	public $menus = [];
}
