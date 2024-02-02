<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class APIDocsButton extends QMButton {
	public $fontAwesome = FontAwesome::BOOK_SOLID;
	public $image = ImageUrls::DIALOGUE_ASSETS_DOCUMENT;
	public $link = "https://docs.quantimo.do";
	public $title = 'API Docs';
	public $tooltip = "Interactive documentation for the Application Programming Interface (API)";
}
