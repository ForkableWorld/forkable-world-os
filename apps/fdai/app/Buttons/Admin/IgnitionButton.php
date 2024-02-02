<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Admin;
use App\Buttons\QMButton;
use App\Logging\QMIgnition;
use App\Types\QMStr;
use App\UI\ImageUrls;
use App\UI\IonIcon;
use App\UI\Markdown;
use App\UI\QMColor;
class IgnitionButton extends QMButton {
	/**
	 * @param \Throwable|null $e
	 */
	public function __construct(\Throwable $e){
		$type = QMStr::toShortClassName(get_class($e));
		parent::__construct("$type Details", QMIgnition::getUrlOrGenerateAndOpen($e), QMColor::HEX_ORANGE, IonIcon::bug);
		$this->markdownBadgeLogo = Markdown::PHP;
		$this->tooltip = "View $type details and stack trace on the Ignition Error Page";
		$this->setImage(ImageUrls::LARAVEL_IGNITION_FLARE);
	}
}
