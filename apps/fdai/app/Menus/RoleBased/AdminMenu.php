<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\RoleBased;
use App\Buttons\Admin\AdminSearchButton;
use App\Buttons\Admin\BugsnagButton;
use App\Buttons\Admin\ClockworkButton;
use App\Buttons\Admin\HorizonButton;
use App\Buttons\Admin\IssuesButton;
use App\Buttons\Admin\TelescopeButton;
use App\Menus\QMMenu;
use App\Astral\OAAccessTokenBaseAstralResource;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class AdminMenu extends QMMenu {
	public $badge;
	public $expanded = false;
	public $fontAwesome = FontAwesome::ADMIN;
	public $image = ImageUrls::ADMIN;
	public $ionIcon = IonIcon::locked;
	public $rememberMenuState = true;
	public $title = "Admin";
	public $tooltip = "Links to administrative resources.";
	public function getTitleAttribute(): string{ return $this->title; }
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons[] = ClockworkButton::instance();
		$buttons[] = HorizonButton::instance();
		$buttons[] = IssuesButton::instance();
		//$buttons[] = OAAccessTokenBaseAstralResource::button();
		$buttons[] = TelescopeButton::instance();
		$buttons[] = AdminSearchButton::instance();
		$buttons[] = BugsnagButton::instance();
		//$buttons[] = LiveProfButton::instance();
		return $this->buttons = $buttons;
	}
}
