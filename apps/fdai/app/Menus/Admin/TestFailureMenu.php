<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpMissingFieldTypeInspection */
namespace App\Menus\Admin;
use App\Buttons\Admin\JenkinsConsoleButton;
use App\Menus\QMMenu;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\AppMode;
use Tests\QMBaseTestCase;
use Throwable;
class TestFailureMenu extends QMMenu {
	private $exception;
	private $test;
	/**
	 * @param Throwable $e
	 * @param $test
	 */
	public function __construct(Throwable $e, $test){
		$this->exception = $e;
		$this->test = $test;
	}
	/**
	 * @inheritDoc
	 */
	public function getButtons(): array{
		$buttons = [];
		$e = $this->getException();
		// Handle errors before test started
		if($t = \App\Utils\AppMode::getCurrentTest()){
			$buttons[] = $t->getIgnitionButton($e);
			$buttons[] = $t->getPhpStormButton();

		}
		if(AppMode::isJenkins()){
			$buttons[] = JenkinsConsoleButton::instance();
		}
		return $this->buttons = $buttons;
	}
	/**
	 * @inheritDoc
	 */
	public function getTitleAttribute(): string{
		return $this->getTest()->getName();
	}
	/**
	 * @inheritDoc
	 */
	public function getImage(): string{
		return ImageUrls::PHPUNIT;
	}
	/**
	 * @inheritDoc
	 */
	public function getFontAwesome(): string{
		return FontAwesome::BUG_SOLID;
	}
	/**
	 * @inheritDoc
	 */
	public function getTooltip(): string{
		return "Failed Test Options: " . $this->getTest()->getTooltip();
	}
	/**
	 * @return \Throwable
	 */
	public function getException(): Throwable{
		return $this->exception;
	}
	/**
	 * @return QMBaseTestCase
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getTest(){
		return $this->test;
	}
}
