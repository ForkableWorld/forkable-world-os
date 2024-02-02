<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Menus\DataLab;
use App\Buttons\Admin\DeleteTestUsersButton;
use App\Models\User;
use App\Slim\Middleware\QMAuth;
class UsersDataLabIndexMenu extends DataLabIndexMenu {
	public function __construct(){
		parent::__construct(User::TABLE);
	}
	public function getButtons(): array{
		parent::getButtons();
		if(QMAuth::isAdmin()){
			$this->addButton(DeleteTestUsersButton::instance());
		}
		return $this->buttons;
	}
}
