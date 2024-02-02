<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\DataSources\QMConnector;
use App\Slim\Model\QMResponseBody;
class ConnectorRedirectResponse extends ConnectorResponse {
	/**
	 * @var string
	 */
	public $location;
	public $success = true;
	public $status = "OK";
	public $code = QMResponseBody::CODE_TEMPORARY_REDIRECT;
	/**
	 * @param QMConnector $connector
	 * @param string $methodName
	 * @param string $location
	 */
	public function __construct(QMConnector $connector, string $methodName, string $location){
		parent::__construct($connector, $methodName);
		$this->location = $location;
	}
}
