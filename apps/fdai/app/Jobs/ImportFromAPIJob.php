<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\DataSources\Connectors\Exceptions\ConnectorDisabledException;
use App\Exceptions\NoGeoDataException;
use App\Models\Connection;
use App\PhpUnitJobs\Import\ConnectionsJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class ImportFromAPIJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	/** @var Connection */
	private $Connection;
	/**
	 * Create a new job instance.
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection){
		ConnectionsJob::slack(__METHOD__);
		$this->connection = $connection;
	}
	/**
	 * Execute the job.
	 * @return void
	 * @throws ConnectorDisabledException
	 * @throws NoGeoDataException
	 */
	public function handle(){
		ConnectionsJob::slack(__METHOD__);
		$this->connection->import(__METHOD__);
	}
}
