<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Slim\View\Request\UserTag;
use App\Slim\Middleware\QMAuth;
use App\Slim\QMSlim;
use App\Slim\View\Request\Request;
class GetUserTagRequest extends Request {
	/**
	 * @var int
	 */
	private $userId;
	/**
	 * @var int The original userTagVariableId name to get userTags of.
	 */
	private $userTagVariableId;
	/**
	 * @var int The timestamp from which to get userTags.
	 */
	private $userTaggedVariableId;
	/**
	 * Populate this request's properties from an Application instance.
	 * @param QMSlim $app
	 *  When one of the request parameters is invalid.
	 */
	public function populate(QMSlim $app){
		$this->setApplication($app);
		$this->setTagVariableId($this->getParam('userTagVariableId', null));
		$this->setTaggedVariableId($this->getParam('userTaggedVariableId', null));
		$this->setUserId($this->getParamNumeric('user', QMAuth::id(), 'userId must be numeric'));
	}
	/**
	 * @param int $userTaggedVariableId The start time.
	 */
	private function setTaggedVariableId($userTaggedVariableId){
		$this->userTaggedVariableId = $userTaggedVariableId;
	}
	/**
	 * @param int $userId The userId to get userTags for.
	 */
	private function setUserId($userId){
		$this->userId = $userId;
	}
	/**
	 * @return int The userId to get userTags for.
	 */
	public function getUserId(){
		return $this->userId;
	}
	/**
	 * @param int $userTagVariableId The userTagVariableId to get userTags for.
	 */
	private function setTagVariableId($userTagVariableId){
		$this->userTagVariableId = $userTagVariableId;
	}
	/**
	 * @return int The start time.
	 */
	public function getTaggedVariableId(){
		return $this->userTaggedVariableId;
	}
	/**
	 * @return int The start time.
	 */
	public function getTagVariableId(){
		return $this->userTagVariableId;
	}
}
