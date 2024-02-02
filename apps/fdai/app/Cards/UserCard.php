<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\QMButton;
use App\Slim\Model\User\QMUser;
use App\UI\IonIcon;
class UserCard extends UserRelatedQMCard {
	private $user;
	/**
	 * @param QMUser $user
	 */
	public function __construct($user){
		$this->user = $user;
		$this->setSubTitle($user->getTagLine() ?: $user->email);
		if(!empty($user->email)){
			$button = new QMButton("Contact", null, "mailto:$user->email", IonIcon::ion_icon_mail);
			$this->addButton($button);
		}
		$this->setAvatar($user->getAvatar());
		$this->setImage($user->getAvatar());
		$this->setLinkAndSharingButtons($user->userUrl, $user->displayName, $user->getTagLine());
		parent::__construct($user->getId(), $user->getId());
	}
}
