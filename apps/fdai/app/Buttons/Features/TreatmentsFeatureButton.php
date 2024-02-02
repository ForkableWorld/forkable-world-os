<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\TreatmentsVariableCategory;
class TreatmentsFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Treatment Tracking');
		$this->subtitle = 'with reminders';
		$this->tooltip = TreatmentsVariableCategory::MORE_INFO;
		$this->image = TreatmentsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
