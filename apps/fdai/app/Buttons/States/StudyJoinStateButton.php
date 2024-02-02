<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\States;
use App\Buttons\IonicButton;
use App\Buttons\QMButton;
use App\Studies\QMStudy;
use App\Traits\HasCauseAndEffect;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class StudyJoinStateButton extends IonicButton {
	public $accessibilityText = 'Join Study';
	public $action = '/#/app/study-join';
	public $fontAwesome = FontAwesome::BOOK_MEDICAL_SOLID;
	public $icon = 'ion-ios-book';
	public $id = 'join-study-button';
	public $image = ImageUrls::EDUCATION_STUDYING;
	public $ionIcon = 'ion-ios-book';
	public $link = '/#/app/study-join';
	public $stateName = 'app.studyJoin';
	public $stateParams = [];
	public $text = "Join Study";
	public $title = "Join Study";
	public $classes = ['join-study-button'];
	public $tooltip = 'By joining this study, you will have the opportunity to gain insights into how the studied factor affects your health and accelerate scientific discovering by anonymously pooling your data.';
	public $menus = [];
	/**
	 * StudyJoinStateButton constructor.
	 * @param HasCauseAndEffect $hasCauseAndEffect
	 */
	public function __construct($hasCauseAndEffect = null){
		parent::__construct();
		if(!$hasCauseAndEffect){return;}
		$this->parameters = $hasCauseAndEffect->getJoinUrlParams();
		$this->setTooltip($hasCauseAndEffect->getStudyQuestion());
	}
	public function getRoundedHtml(string $backgroundColor = null, string $textColor = null): string{
		$html = parent::getRoundedHtml($backgroundColor, $textColor);
		$html = str_replace('join-this-study-button', $this->id, $html); // Legacy
		return $html;
	}
	/**
	 * @param HasCauseAndEffect|array $params
	 * @return string
	 */
	public static function url($params = []): string{
		$i = static::instance($params);
		return $i->getUrl();
	}
	/**
	 * @param HasCauseAndEffect|array $params
	 * @return self
	 */
	public static function instance($params = null): QMButton{
		if($params instanceof QMStudy){
			$i = new static($params);
		} else{
			$i = new static();
			$i->setParameters($params);
		}
		return $i;
	}
}
