<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class StudiesStateButton extends IonicButton {
	public $accessibilityText = 'Studies';
	public $action = '/#/app/studies';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'studies-state-button';
	public $image = 'https://static.quantimo.do/img/science/png/books.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/studies';
	public $stateName = 'app.studies';
	public $stateParams = [];
	public $text = 'Studies';
	public $title = 'Studies';
	public $tooltip = 'Studies';
	public $menus = [];
}
