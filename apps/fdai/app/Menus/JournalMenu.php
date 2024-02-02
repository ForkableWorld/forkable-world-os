<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\Admin\VariableSearchButton;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\Links\ContactUsButton;
use App\Buttons\States\OnboardingStateButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class JournalMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Menu"; }
	public function getImage(): string{ return ImageUrls::STUDY; }
	public function getFontAwesome(): string{ return FontAwesome::STUDY; }
	public function getTooltip(): string{ return "Links"; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$this->addButton(ContactUsButton::instance());
		$b = new OnboardingStateButton();
		$b->setTextAndTitle("Your Data");
		$this->addButton($b);
		$this->addButton(new VariableSearchButton());
		$this->addButton(new AboutUsButton());
		return $this->buttons;
	}
}
