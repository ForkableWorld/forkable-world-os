<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class PredictorsPositiveVariableStateButton extends VariableDependentStateButton {
	public $action = '/#/app/predictors-positive-variable/:effectVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-positive-variable-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors-positive-variable/:effectVariableName';
	public $stateName = 'app.predictorsPositiveVariable';
	public $stateParams = [];
	public $text = 'Positive Predictors';
	public $title = 'Positive Predictors';
	public $menus = [];
}
