<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\IonicButton;
use App\Menus\DataLab\DataLabIndexRoutesMenu;
use App\Menus\RoleBased\GuestMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class UserMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Menu"; }
	public function getImage(): string{ return ImageUrls::LINK; }
	public function getFontAwesome(): string{ return FontAwesome::LINK; }
	public function getTooltip(): string{ return "Links"; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons = array_merge($buttons, DataLabIndexRoutesMenu::buttons());
		$buttons = array_merge($buttons, IonicButton::exceptVariableDependent());
		$buttons = array_merge($buttons, GuestMenu::buttons());
		//$buttons = array_merge($buttons, MoneyModoMenu::buttons()); // Slow to load a zillion strategies all the time
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
