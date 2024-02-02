<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\AdminButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use PHPUnit\Framework\TestCase;
class CurrentPhpUnitTestButton extends AdminButton {
	public $fontAwesome = FontAwesome::PHP;
	public $image = ImageUrls::PHPUNIT;
	public $parameters = [];
	/**
	 * @param QMBaseTestCase|TestCase $t
	 */
	public function __construct($t){
		parent::__construct("Open Test");
		$this->setTooltip($t->getName());
		$this->setUrl($t->getPHPStormUrl());
	}
}
