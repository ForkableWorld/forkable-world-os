<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Jobs;
use App\Repos\QMAPIRepo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tests\QMBaseTestCase;
class PHPUnitTestJob implements ShouldQueue {
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $sha;
	protected $testName;
	protected $testClass;
	/**
	 * Create a new job instance.
	 * @return void
	 */
	public function __construct(string $testClass, string $testName, string $sha){
		$this->testClass = $testClass;
		$this->testName = $testName;
		$this->sha = $sha;
	}
	/**
	 * Execute the job.
	 * @return void
	 */
	public function handle(){
		QMAPIRepo::checkoutCommit($this->sha);
		QMBaseTestCase::runTestOrClass($this->testClass, $this->testName);
	}
}
