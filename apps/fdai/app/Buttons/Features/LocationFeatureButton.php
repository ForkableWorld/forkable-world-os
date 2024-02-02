<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\LocationsVariableCategory;
class LocationFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Location Tracking');
		$this->subtitle = 'Automatically log places';
		$this->tooltip = LocationsVariableCategory::MORE_INFO;
		$this->image = LocationsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
