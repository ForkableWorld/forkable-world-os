<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Ionic;
use App\Buttons\IonicButton;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class StatesMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Ionic States"; }
	public function getImage(): string{ return ImageUrls::ADMIN; }
	public function getFontAwesome(): string{ return FontAwesome::ADMIN; }
	public function getTooltip(): string{ return "Search for a page in the Ionic app..."; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$this->addButtons(IonicButton::getStateButtons());
		return $this->buttons;
	}
}
