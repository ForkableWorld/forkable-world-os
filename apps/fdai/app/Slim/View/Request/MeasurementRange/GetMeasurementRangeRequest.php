<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\MeasurementRange;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
class GetMeasurementRangeRequest extends Request {
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var string
	 */
	private $causeVariableName;
	/**
	 * @var string
	 */
	private $effectVariableName;
	/**
	 * @var array Array containing names of sources to filter on.
	 */
	private $sources;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$rawSources = $this->getParam('sources', null);
		if($rawSources != null){
			$this->setSources(explode(',', $rawSources));
		}
		$causeVariableName = $this->getParam('causeVariableName', null);
		if($causeVariableName != null){
			$this->setCauseVariableName($causeVariableName);
		}
		$effectVariableName = $this->getParam('effectVariableName', null);
		if($effectVariableName != null){
			$this->setEffectVariableName($effectVariableName);
		}
		$this->setUserId($this->getParamNumeric('user', QMAuth::id(), 'userId must be numeric'));
	}
	/**
	 * @param array $sources Array containing names of sources to filter on.
	 */
	private function setSources($sources){
		$this->sources = $sources;
	}
	/**
	 * @param int $userId The userId to get measurements for.
	 */
	private function setUserId($userId){
		$this->userId = $userId;
	}
	/**
	 * @return int The userId to get measurements for.
	 */
	public function getUserId(){
		return $this->userId;
	}
	/**
	 * @param $effectVariableName
	 * @return void The userId to get measurements for.
	 */
	private function setEffectVariableName($effectVariableName){
		$this->effectVariableName = $effectVariableName;
	}
	/**
	 * @param $causeVariableName
	 * @return void The userId to get measurements for.
	 */
	private function setCauseVariableName($causeVariableName){
		$this->causeVariableName = $causeVariableName;
	}
	/**
	 * @return int The userId to get measurements for.
	 */
	public function getEffectVariableName(){
		return $this->effectVariableName;
	}
	/**
	 * @return int The userId to get measurements for.
	 */
	public function getCauseVariableName(){
		return $this->causeVariableName;
	}
	/**
	 * @return array
	 */
	public function getSources(){
		return $this->sources;
	}
}
