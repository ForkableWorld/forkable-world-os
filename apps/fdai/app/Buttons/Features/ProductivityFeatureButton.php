<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Features;
use App\Buttons\FeatureButton;
use App\DataSources\Connectors\RescueTimeConnector;
class ProductivityFeatureButton extends FeatureButton {
	public function __construct(){
		parent::__construct();
		$this->setTextAndTitle('Productivity Tracking');
		$this->subtitle = 'Passively track app usage';
		$this->tooltip =
			"You can do this by installing and connecting Rescuetime on the Import Data page.  Rescuetime is a program" .
			" that runs on your computer & passively tracks of productivity and app usage.";
		$this->image = RescueTimeConnector::IMAGE;
		$this->premium = false;
	}
}
