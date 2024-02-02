<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Import;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\UI\QMColor;
class ConnectorUpdateButton extends QMButton {
	public function __construct(QMConnector $connector){
		parent::__construct("Update", null, QMColor::HEX_GOOGLE_GREEN, "ion-refresh");
		$this->setAction(QMConnector::ACTION_UPDATE);
		$this->setFunctionName(QMConnector::ACTION_UPDATE);
		$this->setUrl($connector->getUpdateUrl());
		$this->id = 'update-' . $connector->name . '-button';
	}
}
