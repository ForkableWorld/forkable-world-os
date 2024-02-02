<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\UI\CssHelper;
use App\UI\QMColor;
trait HasColor {
	public function getBootstrapColor(): string{
		return QMColor::toBootstrap($this->getColor());
	}
	public function getHexColor(): string{
		return QMColor::toHex($this->getColor());
	}
	public function getColorGradientCss(): string{
		$color = $this->getColor();
		return CssHelper::generateGradientBackground($color);
	}
	abstract public function getColor(): string;
}
