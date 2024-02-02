<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\SymptomsVariableCategory;
class SymptomsFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Track Symptoms');
		$this->subtitle = "in just seconds a day";
		$this->tooltip = SymptomsVariableCategory::MORE_INFO;
		$this->image = SymptomsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
