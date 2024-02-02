<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Slim\View\Request\Correlation;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
class GetCorrelationRequest extends Request {
	/**
	 * @var string Optional ORIGINAL variable name of the effect variable for which the user desires user_variable_relationships.
	 */
	private $effectName;
	/**
	 * @var string Optional ORIGINAL variable name of the cause variable for which the user desires user_variable_relationships.
	 */
	private $causeName;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 *  When neither the cause or effect parameters are specified
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$this->setCauseName($this->getParam('cause', null));
		$this->setEffectName($this->getParam('effect', null));
	}
	/**
	 * @return string
	 */
	public function getEffectName(){
		return $this->effectName;
	}
	/**
	 * @return string
	 */
	public function getCauseName(){
		return $this->causeName;
	}
	/**
	 * @param string $effectName
	 */
	private function setEffectName($effectName){
		$this->effectName = $effectName;
	}
	/**
	 * @param string $causeName
	 */
	private function setCauseName($causeName){
		$this->causeName = $causeName;
	}
}
