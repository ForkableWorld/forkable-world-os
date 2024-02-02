<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class UsersStateButton extends IonicButton {
	public $accessibilityText = 'Users';
	public $action = '/#/app/users';
	public $fontAwesome = 'fas fa-users';
	public $icon = 'ion-android-people';
	public $id = 'users-state-button';
	public $image = 'https://static.quantimo.do/img/essential-collection/png/users-1.png';
	public $ionIcon = 'ion-android-people';
	public $link = '/#/app/users';
	public $stateName = 'app.users';
	public $stateParams = [];
	public $text = 'Users';
	public $title = 'Users';
	public $tooltip = 'Users';
	public $menus = [];
}
