<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class OutcomeSearchStateButton extends IonicButton {
	public $accessibilityText = 'Predictors';
	public $action = '/#/app/outcome-search';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-search';
	public $id = 'outcome-search-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/predictors-website-screenshot-caption.png';
	public $ionIcon = 'ion-search';
	public $link = '/#/app/outcome-search';
	public $stateName = 'app.outcomeSearch';
	public $stateParams = [];
	public $text = 'Predictors';
	public $title = 'Predictors';
	public $tooltip = 'Predictors';
	public $menus = [];
}
