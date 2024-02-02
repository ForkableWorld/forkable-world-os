<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
class HistoryStateButton extends IonicButton {
	public $accessibilityText = 'History';
	public $action = '/#/app/history';
	public $fontAwesome = 'fas fa-history';
	public $icon = 'ion-ios-list-outline';
	public $id = 'history-state-button';
	public $image = 'https://static.quantimo.do/img/jetbrains/darcula/searchWithHistory.png';
	public $ionIcon = 'ion-ios-list-outline';
	public $link = '/#/app/history';
	public $stateName = 'app.history';
	public $stateParams = [];
	public $text = 'History';
	public $title = 'History';
	public $tooltip = 'History';
	public $menus = [];
}
