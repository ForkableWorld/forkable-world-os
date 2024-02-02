<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\Http\Urls\Sharing\FaceBookShareUrl;
use App\UI\FontAwesome;
class FacebookSharingButton extends FacebookButton {
	public $fontAwesome = FontAwesome::FACEBOOK;
	/**
	 * FacebookSharingButton constructor.
	 * @param string $linkToShare
	 * @param bool $includeText
	 */
	public function __construct(string $linkToShare, bool $includeText = true){
		parent::__construct(self::getFacebookShareLink($linkToShare), "Share on Facebook", $includeText);
		$this->setAction("share");
		$this->setId("facebook-share");
	}
	/**
	 * @param $linkToShare
	 * @return string
	 */
	public static function getFacebookShareLink($linkToShare): string{
		return "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($linkToShare);
	}
}
