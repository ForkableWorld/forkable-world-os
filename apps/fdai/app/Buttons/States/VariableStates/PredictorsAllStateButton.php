<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class PredictorsAllStateButton extends VariableDependentStateButton {
	public $action = '/#/app/predictors/:effectVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-all-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors/:effectVariableName';
	public $stateName = 'app.predictorsAll';
	public $stateParams = [];
	public $text = 'Top Predictors';
	public $title = 'Top Predictors';
	public $menus = [];
}
