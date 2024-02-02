<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class PredictorsNegativeStateButton extends IonicButton {
	public $accessibilityText = 'Negative Predictors';
	public $action = '/#/app/predictors-negative';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-negative-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors-negative';
	public $stateName = 'app.predictorsNegative';
	public $stateParams = [];
	public $text = 'Negative Predictors';
	public $title = 'Negative Predictors';
	public $tooltip = 'Negative Predictors';
	public $menus = [];
}
