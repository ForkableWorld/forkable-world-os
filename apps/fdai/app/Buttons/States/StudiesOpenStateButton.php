<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class StudiesOpenStateButton extends IonicButton {
	public $accessibilityText = 'Open Studies';
	public $action = '/#/app/studies/open';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'studies-open-state-button';
	public $image = 'https://static.quantimo.do/img/science/png/books.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/studies/open';
	public $stateName = 'app.studiesOpen';
	public $stateParams = [];
	public $text = 'Open Studies';
	public $title = 'Open Studies';
	public $tooltip = 'Open Studies';
	public $menus = [];
}
