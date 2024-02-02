<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\EnvironmentVariableCategory;
class WeatherFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Weather Tracking');
		$this->subtitle = 'Automatically log weather';
		$this->tooltip = EnvironmentVariableCategory::MORE_INFO;
		$this->image = EnvironmentVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
