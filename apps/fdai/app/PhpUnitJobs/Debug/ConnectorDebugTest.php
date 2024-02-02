<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Debug;
use OAuth\Common\Exception\Exception;
use OAuth\Common\Token\Exception\ExpiredTokenException;
use App\Exceptions\CredentialsNotFoundException;
use App\Exceptions\InvalidTimestampException;
use App\Exceptions\InvalidVariableValueException;
use App\Exceptions\NoResponseException;
use App\DataSources\Connectors\FitbitConnector;
use App\DataSources\Connectors\TigerViewConnector;
use App\Models\Connection;
use App\PhpUnitJobs\JobTestCase;
class ConnectorDebugTest extends JobTestCase {
	/**
	 * @group Production
	 */
	public function testConnectorImportDebug(): void{
		$c = TigerViewConnector::instance();
		$c->setFromDate(0);
		$c->importData();
		Connection::getConnectionById(230, 88)->import(__METHOD__);
	}
	/**
	 * @throws \App\DataSources\TooEarlyException
	 * @throws \App\Exceptions\InvalidVariableValueAttributeException
	 * @throws \App\Exceptions\TooSlowException
	 * @throws \App\Slim\Controller\Connector\ConnectorException
	 */
	public function testFitbitImport(){
		$c = FitbitConnector::getByUserId(82534);
		$c->setFromDate(0);
		$c->importData();
	}
}
