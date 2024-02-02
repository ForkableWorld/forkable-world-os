<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\UI\HtmlHelper;
class DownloadButtonsQMCard extends QMCard {
	/**
	 * @return string
	 */
	public static function getDownloadButtonsHtml(): string{
		return HtmlHelper::renderView(view('download-buttons'));
	}
}
