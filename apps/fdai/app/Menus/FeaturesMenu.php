<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Menus;
use App\Buttons\Admin\AdminerButton;
use App\Buttons\Admin\AstralButton;
use App\Buttons\DemoExamples\GradeReportExampleButton;
use App\Buttons\DemoExamples\RootCauseAnalysisReportExampleButton;
use App\Buttons\Links\APIDocsButton;
use App\Buttons\Links\DataLabButton;
use App\Buttons\Links\MoneyModoButton;
use App\Buttons\Links\PhysicianDashboardButton;
use App\Buttons\States\OnboardingStateButton;
use App\DataSources\Connectors\AirQualityConnector;
use App\DataSources\Connectors\PollenCountConnector;
use App\DataSources\Connectors\WeatherConnector;
use App\UI\FontAwesome;
class FeaturesMenu extends QMMenu {
	public function getTitleAttribute(): string{
		return "Features";
	}
	public function getImage(): string{
		return app_icon();
	}
	public function getFontAwesome(): string{
		return FontAwesome::STAR;
	}
	public function getTooltip(): string{
		return "What we do";
	}
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$buttons[] = new AdminerButton();
		$buttons[] = new APIDocsButton();
		$buttons[] = new DataLabButton();
		//$buttons[] = new AstralButton();
		$buttons[] = new GradeReportExampleButton();
		$buttons[] = new OnboardingStateButton();
		$buttons[] = (new AirQualityConnector())->getButton();
		$buttons[] = (new PollenCountConnector())->getButton();
		$buttons[] = (new WeatherConnector())->getButton();
		$buttons[] = new PhysicianDashboardButton();
		$buttons[] = new RootCauseAnalysisReportExampleButton();
		$this->addButtons($buttons);
		return $this->buttons;
	}
}
