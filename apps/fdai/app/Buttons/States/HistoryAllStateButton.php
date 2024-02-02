<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\UI\IonIcon;
class HistoryAllStateButton extends IonicButton {
	public $accessibilityText = 'History';
	public $action = '/#/app/history-all';
	public $fontAwesome = 'fas fa-history';
	public $icon = 'ion-ios-list-outline';
	public $id = 'history-all-state-button';
	public $image = 'https://static.quantimo.do/img/jetbrains/darcula/searchWithHistory.png';
	public $ionIcon = IonIcon::history;
	public $link = '/#/app/history-all';
	public $stateName = 'app.historyAll';
	public $stateParams = [];
	public $text = "Measurement History";
	public $title = "Measurement History";
	public $tooltip = "Measurement History";
	public $menus = [];
	public function __construct(array $params = []){
		$this->setParameters($params);
		parent::__construct();
	}
}
