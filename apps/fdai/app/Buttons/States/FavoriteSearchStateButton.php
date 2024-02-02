<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class FavoriteSearchStateButton extends IonicButton {
	public $accessibilityText = 'Select a Variable';
	public $action = '/#/app/favorite-search';
	public $fontAwesome = 'far fa-list-alt';
	public $icon = 'ion-search';
	public $id = 'favorite-search-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/variable-list-screenshot-caption.png';
	public $ionIcon = 'ion-search';
	public $link = '/#/app/favorite-search';
	public $stateName = 'app.favoriteSearch';
	public $stateParams = [];
	public $text = 'Select a Variable';
	public $title = 'Select a Variable';
	public $tooltip = 'Select a Variable';
	public $menus = [];
}
