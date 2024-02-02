<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Charts\BarChartButton;
use App\Models\GlobalVariableRelationship;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Studies\QMStudy;
use App\Traits\HasCauseAndEffect;
use App\Types\QMArr;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class StudyButton extends BarChartButton {
	/**
	 * @param HasCauseAndEffect|QMStudy $m
	 */
	public function __construct($m = null){
		if(!$m){
			$this->setImage(Study::DEFAULT_IMAGE);
			$this->setTextAndTitle("Full Study with Charts");
			$this->setAdditionalInformationAndTooltip("Charts & Analysis");
			$this->setIonIcon(IonIcon::study);
			$this->setFontAwesome(FontAwesome::STUDY);
			return;
		}
		parent::__construct();
		$this->setTextAndTitle($m->getTitleAttribute());
		$this->setBackgroundColor($m->getColor());
		$this->link = $m->getUrl();
		$this->setImage($m->getAvatar());
		$this->fontAwesome = $m->getFontAwesome();
		$this->badgeText = $m->getChangeFromBaseline() ?? "?";
		$this->tooltip = $m->getTagLine();
	}
	/**
	 * @param HasCauseAndEffect[]|UserVariableRelationship[]|Study[]|GlobalVariableRelationship[] $models
	 * @return array
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection
	 */
	public static function toButtons($models): array{
		$buttons = [];
		foreach($models as $v){
			$b = new static($v);
			$buttons[$v->getNameAttribute()] = $b;
		}
		QMArr::sortDescending($buttons, 'badgeText');
		return $buttons;
	}
}
