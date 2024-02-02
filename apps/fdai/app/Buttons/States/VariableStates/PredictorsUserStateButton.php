<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class PredictorsUserStateButton extends VariableDependentStateButton {
	public $action = '/#/app/predictors/user/:effectVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-user-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors/user/:effectVariableName';
	public $stateName = 'app.predictorsUser';
	public $stateParams = [];
	public $text = 'Your Predictors';
	public $title = 'Your Predictors';
	public $menus = [];
}
