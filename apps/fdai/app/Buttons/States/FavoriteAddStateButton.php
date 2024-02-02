<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\VariableDependentStateButton;
class FavoriteAddStateButton extends VariableDependentStateButton {
	public $accessibilityText = 'Add Favorite';
	public $action = '/#/app/favorite-add';
	public $fontAwesome = 'far fa-star';
	public $icon = 'ion-star';
	public $id = 'favorite-add-state-button';
	public $image = 'https://static.quantimo.do/img/work-productivity/png/favorite.png';
	public $ionIcon = 'ion-star';
	public $link = '/#/app/favorite-add';
	public $stateName = 'app.favoriteAdd';
	public $stateParams = [];
	public $text = 'Add Favorite';
	public $title = 'Add Favorite';
	public $tooltip = 'Add Favorite';
	public $menus = [];
}
