<?php
namespace App\Menus\Admin;
use App\Buttons\Admin\AAPanelButton;
use App\Buttons\Admin\AdminerButton;
use App\Buttons\Admin\BugsnagButton;
use App\Buttons\Admin\ClockworkButton;
use App\Buttons\Admin\CurrentPhpUnitTestButton;
use App\Buttons\Admin\HorizonButton;
use App\Buttons\Admin\TelescopeButton;
use App\Computers\JenkinsSlave;
use App\Computers\ThisComputer;
use App\Logging\QMClockwork;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use App\Utils\Env;
class DebugMenu extends QMMenu {
	/**
	 * @var JenkinsSlave|null
	 */
	private ?JenkinsSlave $computer;
	public function __construct(JenkinsSlave $computer = null){
		$this->computer = $computer ?? ThisComputer::instance();
	}
	public function getButtons(): array{
		$buttons = [];
		if(QMClockwork::enabled()){
			$buttons[] = new ClockworkButton();
		}
		$buttons[] = new AAPanelButton();
		if($t = AppMode::getCurrentTest()){
			$buttons[] = new CurrentPhpUnitTestButton($t);
		}
		if(Env::get('TELESCOPE_ENABLED')){
			$buttons[] = new TelescopeButton($this->computer);
		}
		$buttons[] = new HorizonButton($this->computer);
		$buttons[] = new AdminerButton($this->computer);
		if(AppMode::isStagingOrProductionApiRequest()){
			$buttons[] = new BugsnagButton();
		}
		return $this->buttons = $buttons;
	}
	public function getTitleAttribute(): string{
		return "Debug Menu";
	}
	public function getImage(): string{
		return ImageUrls::PHPSTORM;
	}
	public function getFontAwesome(): string{
		return FontAwesome::BUG_SOLID;
	}
	public function getTooltip(): string{
		return "Useful debugging links";
	}
}
