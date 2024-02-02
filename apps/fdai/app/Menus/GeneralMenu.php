<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Menus;
use App\Buttons\Admin\PHPUnitButton;
use App\Buttons\Links\AboutUsButton;
use App\Buttons\Links\APIDocsButton;
use App\Buttons\Links\AppBuilderButton;
use App\Buttons\Links\ContactUsButton;
use App\Buttons\Links\DataLabButton;
use App\Buttons\Links\HelpButton;
use App\Buttons\Links\OpenSourceButton;
use App\Buttons\Links\PhysicianDashboardButton;
use App\Buttons\Links\PrivacyPolicyButton;
use App\Buttons\Links\StudiesButton;
use App\Buttons\Links\TermsOfServiceButton;
use App\Buttons\Links\WordPressPluginButton;
use App\Slim\Middleware\QMAuth;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
class GeneralMenu extends QMMenu {
	public function getTitleAttribute(): string{ return "Menu"; }
	public function getImage(): string{ return ImageUrls::LINK; }
	public function getFontAwesome(): string{ return FontAwesome::LINK; }
	public function getTooltip(): string{ return "Links"; }
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = AboutUsButton::instance();
		$buttons[] = APIDocsButton::instance();
		$buttons[] = AppBuilderButton::instance();
		$buttons[] = ContactUsButton::instance();
		$buttons[] = DataLabButton::instance();
		$buttons[] = HelpButton::instance();
		$buttons[] = OpenSourceButton::instance();
		$buttons[] = PhysicianDashboardButton::instance();
		$buttons[] = PrivacyPolicyButton::instance();
		$buttons[] = StudiesButton::instance();
		$buttons[] = TermsOfServiceButton::instance();
		$buttons[] = WordPressPluginButton::instance();
		if(AppMode::isApiRequest() && QMAuth::getQMUserIfSet() && QMAuth::isAdmin()){
			$buttons[] = PHPUnitButton::instance();
		}
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
