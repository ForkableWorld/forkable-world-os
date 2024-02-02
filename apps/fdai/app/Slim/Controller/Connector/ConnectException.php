<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
class ConnectException extends ConnectorException {
	/**
	 * ConnectorConnectionErrorResponse constructor.
	 * @param QMConnector $connector
	 * @param string $internalMessage
	 * @param int $code
	 * @param string|null $userMessage
	 */
	public function __construct(QMConnector $connector, string $internalMessage, int $code = 400,
		string $userMessage = null){
		$internalMessage .= "\nGot $code response when trying to connect";
		if(!$userMessage){
			$userMessage = "We couldn't contact $connector->displayName, please try again later at;\n"
				.$connector->getConnectUrlWithParams();
		}
		$this->userMessage = $userMessage;
		$connector->getOrCreateConnection()->setConnectErrorMessage($userMessage, $internalMessage);
		parent::__construct($connector, $connector->name . '/connect', $code,
			"Couldn't connect $connector->displayName", $internalMessage);
	}
}
