<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class LoginStateButton extends IonicButton {
	public $accessibilityText = 'Login';
	public $action = '/#/app/login';
	public $fontAwesome = 'fas fa-sign-in-alt';
	public $icon = 'ion-log-in';
	public $id = 'login-state-button';
	public $image = 'https://static.quantimo.do/img/essential-collection/png/login.png';
	public $ionIcon = 'ion-log-in';
	public $link = '/#/app/login';
	public $stateName = 'app.login';
	public $stateParams = [];
	public $text = 'Login';
	public $title = 'Login';
	public $tooltip = 'Login';
	public $menus = [];
}
