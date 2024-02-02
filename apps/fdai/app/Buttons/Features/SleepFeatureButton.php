<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\SleepVariableCategory;
class SleepFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Sleep Quality');
		$this->subtitle = 'Create a Sleep Quality reminder to record your sleep quality every day';
		$this->tooltip = SleepVariableCategory::MORE_INFO;
		$this->image = SleepVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
