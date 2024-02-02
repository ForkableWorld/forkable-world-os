<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class OpenSourceButton extends QMButton {
	public $title = "Open Source";
	public $link = "https://github.com/decentralized-fda/decentralized-fda";
	public $image = ImageUrls::DATA_SOURCES_GITHUB_SMALL_MFUESC;
	public $fontAwesome = FontAwesome::GITHUB;
	public $tooltip = "A Better World Through Data";
}
