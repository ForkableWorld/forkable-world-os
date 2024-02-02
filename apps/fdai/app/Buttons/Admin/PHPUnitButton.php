<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\Slim\View\Request\QMRequest;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\Markdown;
use App\UI\QMColor;
use App\Utils\AppMode;
use Tests\QMBaseTestCase;
class PHPUnitButton extends QMButton {
	public $color = QMColor::HEX_PURPLE;
	public $fontAwesome = FontAwesome::CODE_SOLID;
	public $image = ImageUrls::PHPUNIT;
	//public $ionIcon = IonIcon::bug;
	public function __construct(string $name = null,
		string $url = null){ // Always pass url so we don't try to get test url in non-test situation
		if(!$name && !$url && AppMode::isApiRequest()){
			$name = "Generate PHPUnit";
			$url = QMRequest::current([QMRequest::PARAM_GENERATE_PHPUNIT => true]);
		}
		$this->markdownBadgeLogo = Markdown::PHP;
		parent::__construct($name);
		$this->setUrl($url);
		$this->setTooltip("Run $name PHPUnit Test");
	}
	/**
	 * @param string $url
	 * @param array $params
	 * @param bool $allowPaths
	 * @return static
	 */
	public function setUrl(string $url, array $params = [], bool $allowPaths = false): self{
		$this->link =
			$url; // Don't use parent setUrl because it incorrectly says my phpunit test urls are invalid for some reason
		return $this;
	}
	public static function getForCurrentTest(): ?self{
		$name = \App\Utils\AppMode::getCurrentTestName();
		if(!$name){
			return null;
		}
		return new self($name, \App\Utils\AppMode::getPHPStormUrlStatic());
	}
	public static function getPathOrUrlToCurrentTest(): string{
		return \App\Files\FileFinder::getPathOrUrlToCurrentTest();
	}
}
