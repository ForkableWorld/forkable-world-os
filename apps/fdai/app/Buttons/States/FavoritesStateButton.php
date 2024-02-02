<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class FavoritesStateButton extends IonicButton {
	public $accessibilityText = 'Favorites';
	public $action = '/#/app/favorites';
	public $fontAwesome = 'far fa-star';
	public $icon = 'ion-star';
	public $id = 'favorites-state-button';
	public $image = 'https://static.quantimo.do/img/work-productivity/png/favorite.png';
	public $ionIcon = 'ion-star';
	public $link = '/#/app/favorites';
	public $stateName = 'app.favorites';
	public $stateParams = [];
	public $text = 'Favorites';
	public $title = 'Favorites';
	public $tooltip = 'Favorites';
	public $menus = [];
}
