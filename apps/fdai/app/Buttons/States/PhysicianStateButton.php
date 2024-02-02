<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\Slim\Model\States\IonicState;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class PhysicianStateButton extends IonicButton {
	const PHYSICIAN_URL = "https://physician.quantimo.do";
	public $accessibilityText = 'Physician Dashboard';
	public $action = self::PHYSICIAN_URL;
	public $fontAwesome = FontAwesome::MEDKIT_SOLID;
	public $icon = 'ion-medkit';
	public $id = 'physician-state-button';
	public $image = ImageUrls::EMOTICON_SET_DOCTOR;
	public $ionIcon = 'ion-medkit';
	public $link = self::PHYSICIAN_URL;
	public $menus = [];
	public $screenshot = 'https://static.quantimo.do/img/screenshots/medication/physician-patient-list-screenshot.png';
	public $stateName = 'app.physician';
	public $stateParams = [];
	public $text = 'Physician Dashboard';
	public $title = 'Physician Dashboard';
	public $tooltip = "The physician dashboard allows physicians to view their patients data in addition to regularly emailed reports.";
	public function __construct(IonicState $state = null){
		parent::__construct($state);
		$this->link = self::PHYSICIAN_URL;
	}
}
