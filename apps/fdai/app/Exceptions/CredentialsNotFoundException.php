<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Exceptions;
use App\DataSources\QMConnector;
class CredentialsNotFoundException extends BadRequestException {
	/**
	 * CredentialsNotFoundException constructor.
	 * @param QMConnector $c
	 * @param string|null $userMessage
	 */
	public function __construct(QMConnector $c, string $userMessage = null){
		$connection = $c->getOrCreateConnection();
		//$connection->disconnect();   This is handled by $connection->setErrorMessage
		$internal = "Credentials not found for $c Connection created " . $connection->getTimeSinceCreatedAt();
		if(!$userMessage){
			$userMessage =
				"I couldn't find your $c->displayName credentials!  Please reconnect or create a ticket at https://help.quantimo.do";
		}
		$userMessage .= "\nMore details at " . $connection->getDataLabShowUrl();
		$connection->setConnectErrorMessage($userMessage, $internal);
		parent::__construct($userMessage);
	}
}
