<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Links;
use App\Buttons\QMButton;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\Utils\Env;
class AboutUsButton extends QMButton {
	const QM_INFO_URL = "https://quantimo.do";
	public $title = "About Us";
	public $link = self::QM_INFO_URL;
	public $image = ImageUrls::ESSENTIAL_COLLECTION_INFO;
	public $fontAwesome = FontAwesome::INFO_CIRCLE_SOLID;
	public $tooltip = "A Better World Through Data";
	/**
	 * @param string|null $text
	 * @param string|null $ionIcon
	 * @param string|null $backgroundColor
	 * @param string|null $url
	 * @param null $additionalInformation
	 */
	public function __construct(string $text = null, string $ionIcon = null, string $backgroundColor = null,
		string $url = null, $additionalInformation = null){
		if(!$url){
			$url = Env::get('APP_URL');
			$this->tooltip = Env::get('APP_DESCRIPTION');
		}
		parent::__construct($text, $url, $backgroundColor, $ionIcon, $additionalInformation);
	}
}
