<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\DevOps\Jenkins;
use App\Slim\Model\StaticModel;
use stdClass;
class JenkinsQueueItem extends StaticModel {
	public $task;
	public $actions;
	/**
	 * @var JenkinsAPI
	 */
	protected $jenkins;
	/**
	 * @param stdClass $response
	 * @param JenkinsAPI $jenkins
	 */
	public function __construct(stdClass $response, JenkinsAPI $jenkins){
		foreach($response as $key => $value){
			$this->$key = $value;
		}
		$this->setJenkins($jenkins);
	}
	/**
	 * @return array
	 */
	public function getInputParameters(): array{
		$parameters = [];
		if(!property_exists($this->actions[0], 'parameters')){
			return $parameters;
		}
		foreach($this->actions[0]->parameters as $parameter){
			$parameters[$parameter->name] = $parameter->value;
		}
		return $parameters;
	}
	/**
	 * @return string
	 */
	public function getJobName(): string{
		return $this->task->name;
	}
	/**
	 * @return int
	 */
	public function getId(): int{
		return $this->id;
	}
	/**
	 * @return void
	 */
	public function cancel(){
		JenkinsQueue::cancelQueue($this);
	}
	/**
	 * @return JenkinsAPI
	 */
	public function getJenkins(): JenkinsAPI{
		return $this->jenkins;
	}
	/**
	 * @param JenkinsAPI $jenkins
	 */
	public function setJenkins(JenkinsAPI $jenkins){
		$this->jenkins = $jenkins;
	}
}
