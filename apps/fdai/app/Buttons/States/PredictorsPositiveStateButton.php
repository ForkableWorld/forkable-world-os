<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class PredictorsPositiveStateButton extends IonicButton {
	public $accessibilityText = 'Positive Predictors';
	public $action = '/#/app/predictors-positive';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'predictors-positive-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/predictors-positive';
	public $stateName = 'app.predictorsPositive';
	public $stateParams = [];
	public $text = 'Positive Predictors';
	public $title = 'Positive Predictors';
	public $tooltip = 'Positive Predictors';
	public $menus = [];
}
