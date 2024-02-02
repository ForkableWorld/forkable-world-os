<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Routes;
class IndexRoutesMenu extends RoutesMenu {
	public function getButtons(): array{
		$buttons = parent::getButtons();
		foreach($buttons as $button){
			$button->setTextAndTitle(str_replace('List ', '', $button->getTitleAttribute()));
		}
		return $this->buttons;
	}
}
