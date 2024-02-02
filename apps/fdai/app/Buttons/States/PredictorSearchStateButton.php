<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class PredictorSearchStateButton extends IonicButton {
	public $accessibilityText = 'Outcomes';
	public $action = '/#/app/predictor-search';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-search';
	public $id = 'predictor-search-state-button';
	public $image = 'https://static.quantimo.do/img/business-strategy/png/target.png';
	public $ionIcon = 'ion-search';
	public $link = '/#/app/predictor-search';
	public $stateName = 'app.predictorSearch';
	public $stateParams = [];
	public $text = 'Outcomes';
	public $title = 'Outcomes';
	public $tooltip = 'Outcomes';
	public $menus = [];
	/**
	 * @return string
	 */
	public static function getMoreDiscoveriesButtonHtml(): string{
		$b = new PredictorSearchStateButton();
		$b->setTextAndTitle("More Discoveries");
		return $b->getRectangleWPButton();
	}
}
