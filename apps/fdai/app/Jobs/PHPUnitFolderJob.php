<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\Logging\ConsoleLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;
use RuntimeException;
use Tests\QMBaseTestCase;
class PHPUnitFolderJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	use IsMonitored;
	public $folder;
	public $sha;
	/**
	 * Create a new job instance.
	 * @param string $folder
	 * @param string $sha
	 */
	public function __construct(string $folder, string $sha){
		$this->folder = $folder;
		$this->sha = $sha;
	}
	public function handle(){
		//$this->fail(new RuntimeException('Failed tests: '.implode(', ', array_keys([]))));
		//return;
		//QMAPIRepo::checkoutCommit($this->sha);
		try {
			$result = QMBaseTestCase::runTestsInFolder($this->folder);
		} catch (\Throwable $e) {
			$this->fail($e);
		}
		$failed = QMBaseTestCase::getFailedTests();
		if($failed){
			$this->fail(new RuntimeException('Failed tests: '.implode(', ', array_keys($failed))));
		} else {
			ConsoleLog::info("Tests passed!");
		}
	}
}
