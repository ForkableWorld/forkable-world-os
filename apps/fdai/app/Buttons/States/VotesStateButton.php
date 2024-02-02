<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class VotesStateButton extends IonicButton {
	public $accessibilityText = 'Your Votes';
	public $action = '/#/app/votes';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-star';
	public $id = 'votes-state-button';
	public $image = 'https://static.quantimo.do/img/essential-collection/png/star.png';
	public $ionIcon = 'ion-star';
	public $link = '/#/app/votes';
	public $stateName = 'app.votes';
	public $stateParams = [];
	public $text = 'Your Votes';
	public $title = 'Your Votes';
	public $tooltip = 'Your Votes';
	public $menus = [];
}
