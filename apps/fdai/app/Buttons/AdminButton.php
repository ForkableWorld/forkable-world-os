<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons;
use App\Slim\Middleware\QMAuth;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
use App\UI\QMColor;
use App\Utils\AppMode;
abstract class AdminButton extends QMButton {
	public $image = ImageUrls::ADMIN;
	public $fontAwesome = FontAwesome::ADMIN;
	public $color = QMColor::HEX_RED;
	/**
	 * @param string|null $text
	 */
	public function __construct(string $text = null){
		parent::__construct($text);
		if(AppMode::isApiRequest()){
			$this->visible = QMAuth::isLoggedInAdmin(); // Don't use isAdmin or we get infinite loop on login error
		}
	}
	public function requiresAdmin(): bool{
		return true;
	}
}
