<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class StudiesCreatedStateButton extends IonicButton {
	public $accessibilityText = 'Your Studies';
	public $action = '/#/app/studies/created';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'studies-created-state-button';
	public $image = 'https://static.quantimo.do/img/science/png/books.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/studies/created';
	public $stateName = 'app.studiesCreated';
	public $stateParams = [];
	public $text = 'Your Studies';
	public $title = 'Your Studies';
	public $tooltip = 'Your Studies';
	public $menus = [];
}
