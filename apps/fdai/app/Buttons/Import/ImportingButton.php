<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Import;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\UI\QMColor;
class ImportingButton extends QMButton {
	public function __construct(QMConnector $connector){
		parent::__construct("Updating", null, QMColor::HEX_GOOGLE_BLUE, "ion-ios-cloud-download");
		$this->id = 'updating-' . $connector->name . '-button';
	}
}
