<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\EmotionsVariableCategory;
class EmotionsFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Emotion Tracking');
		$this->subtitle = "Turn data into happiness!";
		$this->tooltip = EmotionsVariableCategory::MORE_INFO;
		$this->image = EmotionsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
