<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\PhpUnitJobs\Debug;
use App\Jobs\ImportFromAPIJob;
use App\DataSources\Connectors\WeatherConnector;
use App\Models\Connection;
use App\PhpUnitJobs\JobTestCase;
class QueueDebug extends JobTestCase {
	public function testQueueDebug(){
		ImportFromAPIJob::dispatch(Connection::getConnectionById(230, WeatherConnector::ID));
	}
}
