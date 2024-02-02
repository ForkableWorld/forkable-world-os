<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\DataLab;
use App\Menus\Routes\RoutesMenu;
use App\Utils\QMRoute;
class DataLabIndexRoutesMenu extends RoutesMenu {
	/**
	 * @return QMRoute[]
	 */
	public static function getRoutes(): array{
		return QMRoute::getDataLabIndexRoutes();
	}
	public function getButtons(): array{
		$buttons = parent::getButtons();
		foreach($buttons as $button){
			$button->setTextAndTitle(str_replace('List ', '', $button->getTitleAttribute()));
		}
		return $buttons;
	}
}
