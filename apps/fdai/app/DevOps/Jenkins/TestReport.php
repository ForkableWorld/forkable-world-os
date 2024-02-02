<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use stdClass;
class TestReport {
	public $suites;
	public $skipCount;
	public $passCount;
	public $failCount;
	public $duration;
	/**
	 * @var JenkinsAPI
	 */
	protected $jenkins;
	/**
	 * @var string
	 */
	protected $jobName;
	/**
	 * @var int
	 */
	protected $buildNumber;
	/**
	 * __construct
	 * @param JenkinsAPI $jenkins
	 * @param stdClass $response
	 * @param string $jobName
	 * @param int $buildNumber
	 */
	public function __construct(JenkinsAPI $jenkins, stdClass $response, string $jobName, int $buildNumber){
		$this->jenkins = $jenkins;
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		$this->jobName = $jobName;
		$this->buildNumber = $buildNumber;
	}
	/**
	 * @return string
	 */
	public function getOriginalTestReport(): string{
		return json_encode($this);
	}
	/**
	 * @return string
	 */
	public function getJobName(): string{
		return $this->jobName;
	}
	/**
	 * @return int
	 */
	public function getBuildNumber(): int{
		return $this->buildNumber;
	}
	/**
	 * @return float
	 */
	public function getDuration(): float{
		return $this->duration;
	}
	/**
	 * @return int
	 */
	public function getFailCount(): int{
		return $this->failCount;
	}
	/**
	 * @return int
	 */
	public function getPassCount(): int{
		return $this->passCount;
	}
	/**
	 * @return int
	 */
	public function getSkipCount(): int{
		return $this->skipCount;
	}
	/**
	 * @return array
	 */
	public function getSuites(): array{
		return $this->suites;
	}
	/**
	 * @param $id
	 * @return string
	 */
	public function getSuiteStatus($id): string{
		$suite = $this->getSuite($id);
		$status = 'PASSED';
		foreach($suite->cases as $case){
			if($case->status == 'FAILED'){
				$status = 'FAILED';
				break;
			}
		}
		return $status;
	}
	/**
	 * @param $id
	 * @return stdClass
	 */
	public function getSuite($id): stdClass{
		return $this->suites[$id];
	}
}

