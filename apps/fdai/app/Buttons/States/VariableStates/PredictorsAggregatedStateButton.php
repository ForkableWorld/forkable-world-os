<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States\VariableStates;
use App\Buttons\VariableDependentStateButton;
class PredictorsAggregatedStateButton extends VariableDependentStateButton {
	public $action = '/#/app/predictors/aggregated/:effectVariableName';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-aggregated-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors/aggregated/:effectVariableName';
	public $stateName = 'app.predictorsAggregated';
	public $stateParams = [];
	public $text = 'Common Predictors';
	public $title = 'Common Predictors';
	public $menus = [];
}
