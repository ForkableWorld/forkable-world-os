<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class HelpButton extends QMButton {
	public $title = "Need Help?";
	public $link = "https://help.quantimo.do";
	public $image = ImageUrls::BASIC_FLAT_ICONS_HELP;
	public $fontAwesome = FontAwesome::QUESTION_CIRCLE;
	public $tooltip = "Get Help";
	/**
	 * @return string
	 */
	public static function getHelpButtonHtml(): string{
		$b = new HelpButton();
		return $b->getRectangleWPButton();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public static function url($params = []): string{
		return "https://help.quantimo.do";
	}
}
