<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Menus\Admin\AdminSearchMenu;
use App\Slim\Middleware\QMAuth;
use App\UI\FontAwesome;
use App\UI\HtmlHelper;
use App\UI\ImageUrls;
class SearchMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Searchable Menu"; }
	public function getImage(): string{ return ImageUrls::BASIC_FLAT_ICONS_SEARCH; }
	public function getFontAwesome(): string{ return FontAwesome::SEARCH_SOLID; }
	public function getTooltip(): string{ return "Search for anything..."; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		if($this->buttons){
			return $this->buttons;
		}
		if(QMAuth::isAdmin()){
			$buttons = AdminSearchMenu::buttons();
		} elseif(QMAuth::getQMUser()){
			$buttons = UserMenu::buttons();
		} else{
			// Why should guest buttons be different?  $buttons = GuestMenu::buttons();
			$buttons = UserMenu::buttons();
		}
		$this->addButtons($buttons);
		return $this->buttons;
	}
	/**
	 * @return string
	 */
	public function getSearchPageHtml(): string{
		return HtmlHelper::renderBlade(view('chip-search-page', ['buttons' => $this->getButtons()]));
	}
}
