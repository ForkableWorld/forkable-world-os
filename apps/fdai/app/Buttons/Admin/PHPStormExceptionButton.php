<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Types\QMStr;
use App\UI\ImageUrls;
use Throwable;
class PHPStormExceptionButton extends PHPStormButton {
	/**
	 * @param \Throwable $e
	 */
	public function __construct(Throwable $e){
		$type = QMStr::toShortClassName(get_class($e));
		parent::__construct("Jump to $type", self::urlForException($e));
		$this->tooltip = "Go to the location where $type was thrown in PHPStorm";
		$this->setImage(ImageUrls::PHPSTORM);
	}
	/**
	 * @param Throwable $e
	 * @return string
	 */
	public static function urlForException(Throwable $e): string{
		return PHPStormButton::redirectUrl($e->getFile(), $e->getLine());
	}
}
