<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Import;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\UI\QMColor;
class DisconnectButton extends QMButton {
	public function __construct(QMConnector $connector){
		parent::__construct("Disconnect", null, QMColor::HEX_GOOGLE_RED, "ion-close-circled");
		$this->setUrl($connector->getDisconnectUrl());
		$this->setFunctionName(QMConnector::ACTION_DISCONNECT);
		$this->id = 'disconnect-' . $connector->name . '-button';
	}
}
