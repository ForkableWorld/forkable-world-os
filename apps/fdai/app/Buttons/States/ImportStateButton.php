<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\Slim\Model\States\IonicState;
class ImportStateButton extends IonicButton {
	public $accessibilityText = 'Import Data';
	public $action = '/#/app/import';
	public $fontAwesome = 'fas fa-file-import';
	public $icon = 'ion-ios-cloud-download-outline';
	public $id = 'import-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/import-data-website-screenshot-caption.png';
	public $ionIcon = 'ion-ios-cloud-download-outline';
	public $link = '/#/app/import';
	public $stateName = 'app.import';
	public $stateParams = [];
	public $text = 'Import Data';
	public $title = 'Import Data';
	public $tooltip = 'Import Data';
	public $menus = [];
	public function __construct(IonicState $state = null){
		parent::__construct($state);
		if($this->link === '/#/app/import'){
			parent::__construct($state);
			le("wrong link");
		}
	}
}
