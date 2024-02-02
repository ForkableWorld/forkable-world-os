<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\VariableCategories\PaymentsVariableCategory;
class PurchasesFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Purchase Tracking');
		$this->subtitle = 'Automatically log purchases';
		$this->tooltip = PaymentsVariableCategory::MORE_INFO;
		$this->image = PaymentsVariableCategory::IMAGE_URL;
		$this->premium = false;
	}
}
