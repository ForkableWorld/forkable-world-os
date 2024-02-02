<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus\RoleBased;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\Links\ContactUsButton;
use App\Buttons\Links\HelpButton;
use App\Buttons\Links\OpenSourceButton;
use App\Buttons\Links\StudiesButton;
use App\Buttons\States\OnboardingStateButton;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\IonIcon;
class GuestMenu extends QMMenu {
	public $badge;
	public $expanded = false;
	public $fontAwesome = FontAwesome::LINK;
	public $image = ImageUrls::LINK;
	public $ionIcon = IonIcon::link;
	public $rememberMenuState = true;
	public $title = "Links";
	public $tooltip = "Helpful links.";
	public function getTitleAttribute(): string{ return $this->title; }
	public function getImage(): string{ return $this->image; }
	public function getFontAwesome(): string{ return $this->fontAwesome; }
	public function getTooltip(): string{ return $this->tooltip; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [
			new StudiesButton(),
			new AboutUsButton(),
			new OpenSourceButton(),
			new HelpButton(),
			new ContactUsButton(),
			new OnboardingStateButton(),
		];
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
