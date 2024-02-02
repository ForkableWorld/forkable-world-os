<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Slim\View\Request\QMRequest;
use App\UI\FontAwesome;
use App\UI\IonIcon;
class UrlButton extends QMButton {
	public function __construct(string $url = null, string $title = null){
		if(!$url){
			$url = QMRequest::current();
		}
		parent::__construct($title ?? $url, $url, "purple", IonIcon::link);
		$this->tooltip = "Open " . $url;
		$this->setFontAwesome(FontAwesome::LINK_SOLID);
	}
}
