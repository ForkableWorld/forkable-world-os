<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\Links\ContactUsButton;
use App\Buttons\States\OnboardingStateButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class TopMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Menu"; }
	public function getImage(): string{ return ImageUrls::ADMIN; }
	public function getFontAwesome(): string{ return FontAwesome::ADMIN; }
	public function getTooltip(): string{ return "Useful links"; }
	public function getButtons(): array{
		return [
			AboutUsButton::instance(),
			ContactUsButton::instance(),
			OnboardingStateButton::instance(),
		];
	}
}
