<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class TagSearchStateButton extends IonicButton {
	public $accessibilityText = 'Tags';
	public $action = '/#/app/tag-search';
	public $fontAwesome = 'fas fa-tags';
	public $icon = 'ion-search';
	public $id = 'tag-search-state-button';
	public $image = 'https://static.quantimo.do/img/shopping/png/price-tag.png';
	public $ionIcon = 'ion-search';
	public $link = '/#/app/tag-search';
	public $stateName = 'app.tagSearch';
	public $stateParams = [];
	public $text = 'Tags';
	public $title = 'Tags';
	public $tooltip = 'Tags';
	public $menus = [];
}
