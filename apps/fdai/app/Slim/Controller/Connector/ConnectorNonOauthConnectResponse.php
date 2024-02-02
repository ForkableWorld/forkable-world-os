<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
class ConnectorNonOauthConnectResponse extends ConnectorConnectedResponse {
	/**
	 * ConnectorNonOauthConnectResponse constructor.
	 * @param QMConnector $connector
	 */
	public function __construct(QMConnector $connector){
		parent::__construct($connector, $connector->name . '/connect/non-oauth');
	}
}
