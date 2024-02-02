<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\Controller\Connector;
use App\Models\Connection;
use App\Exceptions\TooSlowException;
class ConnectorUpdateResponse extends ConnectorResponse {
	/**
	 * @param Connection $c
	 * @throws TooSlowException
	 */
	public function __construct(Connection $c){
		$c->incrementalImport("Requested by user");
		parent::__construct($c->getQMConnector(), 'doInfo');
	}
}
