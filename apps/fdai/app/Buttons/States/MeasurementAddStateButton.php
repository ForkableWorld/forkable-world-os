<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\Traits\ModelTraits\MeasurementTrait;
class MeasurementAddStateButton extends IonicButton {
	public $accessibilityText = 'Record a Measurement';
	public $action = '/#/app/measurement-add';
	public $fontAwesome = 'fas fa-record-vinyl';
	public $icon = 'ion-compose';
	public $id = 'measurement-add-state-button';
	public $image = 'https://static.quantimo.do/img/screenshots/wordpress/add-measurement-wordpress-screenshot.png';
	public $ionIcon = 'ion-compose';
	public $link = '/#/app/measurement-add';
	public $stateName = 'app.measurementAdd';
	public $stateParams = [];
	public $text = 'Record a Measurement';
	public $title = 'Record a Measurement';
	public $tooltip = 'Record a Measurement';
	public $menus = [];
	/**
	 * MeasurementAddStateButton constructor.
	 * @param MeasurementTrait|null $m
	 */
	public function __construct($m = null){
		parent::__construct();
		if($m){
			$this->setTextAndTitle($m->getValueUnitString());
			$this->setTooltip("Recorded " . $m->getStartSince());
			$this->setBackgroundColor($this->getColor());
			$this->setTextAndTitle($m->getValueUnitTime());
			$this->setParameters($m->getUrlParams());
			$this->setImage($m->getImage());
			$this->setFontAwesome($m->getFontAwesome());
		}
	}
	public function getUrl(array $params = []): string{
		$url = parent::getUrl($params);
		return $url;
	}
}
