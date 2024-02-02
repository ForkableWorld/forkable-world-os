<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\IonIcon;
class MeasurementAddSearchStateButton extends IonicButton {
	public $accessibilityText = 'Select a Variable';
	public $action = '/#/app/measurement-add-search';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-search';
	public $id = 'measurement-add-search-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/variable-list-screenshot-caption.png';
	public $ionIcon = IonIcon::ion_icon_recordMeasurement;
	public $link = '/#/app/measurement-add-search';
	public $stateName = 'app.measurementAddSearch';
	public $stateParams = [];
	public $text = 'Record a Measurement';
	public $title = 'Record a Measurement';
	public $tooltip = 'Record a Measurement';
	public $menus = [];
}
