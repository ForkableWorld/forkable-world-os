<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
class ConnectorConnectedResponse extends ConnectorResponse {
	/**
	 * ConnectorNonOauthConnectResponse constructor.
	 * @param QMConnector $connector
	 * @param string|null $methodName
	 */
	public function __construct(QMConnector $connector, string $methodName = null){
		if(!$methodName){
			$methodName = $connector->name . '/connect';
		}
		//$connector->requestImport();
		$this->summary = $this->description = "Connected $connector->displayName";
		parent::__construct($connector, $methodName);
	}
}
