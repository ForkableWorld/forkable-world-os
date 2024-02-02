<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\Admin;
use App\Menus\DataLab\DataLabIndexRoutesMenu;
use App\Menus\QMMenu;
use App\Menus\RoleBased\AdminMenu;
use App\Menus\RoleBased\GuestMenu;
use App\Menus\Routes\DevRoutesMenu;
use App\Menus\Routes\ExamplesRoutesMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Utils\EnvOverride;
class AdminSearchMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Admin Searchable Menu"; }
	public function getImage(): string{ return ImageUrls::ADMIN; }
	public function getFontAwesome(): string{ return FontAwesome::ADMIN; }
	public function getTooltip(): string{ return "Search for an administrative resource..."; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons = array_merge($buttons, AdminMenu::buttons());
		$buttons = array_merge($buttons, DataLabIndexRoutesMenu::buttons());
		if(EnvOverride::isLocal() && !AppMode::isUnitOrStagingUnitTest()){
			$buttons = array_merge($buttons, DevRoutesMenu::buttons());
			$buttons = array_merge($buttons, ExamplesRoutesMenu::buttons());
		}
		$buttons = array_merge($buttons, GuestMenu::buttons());
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
