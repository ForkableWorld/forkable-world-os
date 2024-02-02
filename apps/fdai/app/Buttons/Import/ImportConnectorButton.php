<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Import;
use App\Buttons\QMButton;
use App\DataSources\QMConnector;
use App\Properties\Connection\ConnectionConnectStatusProperty;
use App\UI\ImageUrls;
use App\UI\QMColor;
class ImportConnectorButton extends QMButton {
	public function __construct(QMConnector $connector){
		parent::__construct("Import Data", null, QMColor::HEX_GOOGLE_GREEN, "ion-link");
		$this->setUrl($connector->getConnectUrlWithParams());
		$this->setFunctionName(QMConnector::ACTION_CONNECT);
		$this->setId('import-data-' . $connector->name . '-button');
		if($connector->connectStatus === ConnectionConnectStatusProperty::CONNECT_STATUS_ERROR ||
			$connector->connectStatus === ConnectionConnectStatusProperty::CONNECT_STATUS_EXPIRED){
			$this->setTextAndTitle("Reconnect");
		}
		$this->setImage(ImageUrls::DEVELOPMENT_001_CLOUD_COMPUTING_2);
	}
}
