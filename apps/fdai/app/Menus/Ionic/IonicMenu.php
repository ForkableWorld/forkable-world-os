<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Ionic;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\IonicHelper;
class IonicMenu extends QMMenu {
	public function getTitleAttribute(): string{
		return "Ionic Menu";
	}
	public function getImage(): string{
		return ImageUrls::APPLICATION;
	}
	public function getFontAwesome(): string{
		return FontAwesome::APPLICATION;
	}
	public function getTooltip(): string{
		return "Ionic app states";
	}
	public function getButtons(): array{
		if($this->buttons){
			return $this->buttons;
		}
		$buttons = IonicHelper::getIonicButtons();
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
