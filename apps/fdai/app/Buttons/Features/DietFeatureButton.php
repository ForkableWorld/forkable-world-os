<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\FoodsVariableCategory;
class DietFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Diet Tracking');
		$this->subtitle = "in just seconds a day";
		$this->tooltip = FoodsVariableCategory::MORE_INFO;
		$this->image = FoodsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
