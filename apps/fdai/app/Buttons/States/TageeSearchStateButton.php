<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\VariableDependentStateButton;
class TageeSearchStateButton extends VariableDependentStateButton {
	public $accessibilityText = 'Select Tagee';
	public $action = '/#/app/tagee-search';
	public $fontAwesome = 'fas fa-tag';
	public $icon = 'ion-search';
	public $id = 'tagee-search-state-button';
	public $image = 'https://static.quantimo.do/img/development/png/041-select-1.png';
	public $ionIcon = 'ion-search';
	public $link = '/#/app/tagee-search';
	public $stateName = 'app.tageeSearch';
	public $stateParams = [];
	public $text = 'Select Tagee';
	public $title = 'Select Tagee';
	public $tooltip = 'Select Tagee';
	public $menus = [];
}
