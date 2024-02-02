<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\Utils\IonicHelper;
class StudyStateButton extends IonicButton {
	public $accessibilityText = 'Study';
	public $action = '/#/app/study';
	public $fontAwesome = 'fas fa-book-medical';
	public $icon = 'ion-ios-book';
	public $id = 'study-state-button';
	public $image = 'https://static.quantimo.do/img/education/png/studying.png';
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/study';
	public $stateName = 'app.study';
	public $stateParams = [];
	public $text = 'Study';
	public $title = 'Study';
	public $tooltip = 'Study';
	public $menus = [];
	/**
	 * @param array $params
	 */
	public function __construct(array $params){
		$this->setParameters($params);
		parent::__construct();
	}
	/**
	 * @param array $params
	 * @param string|null $clientIdSubDomain
	 * @return string
	 */
	public static function getStudyUrl(array $params = [], string $clientIdSubDomain = null): string{
		return IonicHelper::getIonicAppUrl($clientIdSubDomain, 'study', $params);
	}
}
