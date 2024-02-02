<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class IntroStateButton extends IonicButton {
	public $accessibilityText = 'Intro';
	public $action = '/#/app/intro';
	public $fontAwesome = 'fas fa-hands-helping';
	public $icon = 'ion-log-in';
	public $id = 'intro-state-button';
	public $image = 'https://static.quantimo.do/img/essential-collection/png/help.png';
	public $ionIcon = 'ion-log-in';
	public $link = '/#/app/intro';
	public $stateName = 'app.intro';
	public $stateParams = [];
	public $text = 'Intro';
	public $title = 'Intro';
	public $tooltip = 'Intro';
	public $menus = [];
}
