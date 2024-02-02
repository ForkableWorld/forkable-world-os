<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Slim\Model\User\QMUser;
class UserRelatedQMCard extends QMCard {
	private $userId;
	/**
	 * UserRelatedCard constructor.
	 * @param $id
	 * @param int $userId
	 */
	public function __construct($id, int $userId){
		$this->userId = $userId;
		parent::__construct($id);
	}
	/**
	 * @return QMUser
	 */
	public function getUser(): ?QMUser{
		return QMUser::find($this->getUserId());
	}
	/**
	 * @return int
	 */
	public function getUserId(): ?int{
		return $this->userId;
	}
}
