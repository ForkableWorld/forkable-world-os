<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use RuntimeException;
use stdClass;
class Executor {
	public $number;
	public $progress;
	/**
	 * @var JenkinsAPI
	 */
	protected $jenkins;
	/**
	 * @var string
	 */
	protected $computer;
	/**
	 * @param stdClass $response
	 * @param string $computer
	 * @param JenkinsAPI $jenkins
	 */
	public function __construct(stdClass $response, string $computer, JenkinsAPI $jenkins){
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		$this->computer = $computer;
		$this->setJenkins($jenkins);
	}
	/**
	 * @return int
	 */
	public function getProgress(): int{
		return $this->progress;
	}
	/**
	 * @return int|null
	 */
	public function getBuildNumber(): ?int{
		$number = null;
		if(isset($this->currentExecutable)){
			$number = $this->currentExecutable->number;
		}
		return $number;
	}
	/**
	 * @return null|string
	 */
	public function getBuildUrl(): ?string{
		$url = null;
		if(isset($this->currentExecutable)){
			$url = $this->currentExecutable->url;
		}
		return $url;
	}
	/**
	 * @return void
	 */
	public function stop(){
		self::stopExecutor($this);
	}
	/**
	 * @paramExecutor $executor
	 * @throws RuntimeException
	 */
	public static function stopExecutor(Executor $executor){
		$url = sprintf('%s/computer/%s/executors/%s/stop', JenkinsAPI::$baseUrl, $executor->getComputer(),
			$executor->getNumber());
		$curl = Jenkins::post($url);
		JenkinsAPI::validateCurl($curl, sprintf('Error during stopping executor #%s', $executor->getNumber()), $url);
	}
	/**
	 * @return string
	 */
	public function getComputer(): string{
		return $this->computer;
	}
	/**
	 * @return int
	 */
	public function getNumber(): int{
		return $this->number;
	}
	/**
	 * @return JenkinsAPI
	 */
	public function getJenkins(): JenkinsAPI{
		return $this->jenkins;
	}
	/**
	 * @param JenkinsAPI $jenkins
	 * @return void
	 */
	public function setJenkins(JenkinsAPI $jenkins){
		$this->jenkins = $jenkins;
	}
}
