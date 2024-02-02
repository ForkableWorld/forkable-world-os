<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\Share;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
class ListShareRequest extends Request {
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var string The variable name to get sharing permissions for.
	 */
	private $variableName;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$this->setVariableName($app->router()->getCurrentRoute()->getParam('variableName'));
		$this->setUserId($this->getParamNumeric('user', QMAuth::id(), 'userId must be numeric'));
	}
	/**
	 * @param string $variableName
	 */
	private function setVariableName($variableName){
		$this->variableName = $variableName;
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
	 * @return string
	 */
	public function getVariableName(){
		return $this->variableName;
	}
}
