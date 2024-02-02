<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Unit;
use App\Slim\QMSlim;
/** Class ListUnitRequest
 * @package App\Slim\View\Request\Unit
 */
class ListUnitForVariableRequest extends ListUnitRequest {
	/**
	 * @var string The name of the variable to get supported units for.
	 */
	private $variableName;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		parent::populate($app);
		$this->setApplication($app);
		$this->setVariableName($this->getParam('variable', $this->getParam('variableName', null)));
	}
	/**
	 * @param string $variableName
	 */
	private function setVariableName($variableName){
		$this->variableName = $variableName;
	}
	/**
	 * @return string
	 */
	public function getVariableName(){
		return $this->variableName;
	}
}
