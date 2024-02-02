<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\UI\FontAwesome;
class TwitterSharingButton extends TwitterButton {
	public $fontAwesome = FontAwesome::TWITTER;
	/**
	 * TwitterSharingButton constructor.
	 * @param string $linkToShare
	 * @param string $text
	 * @param bool $includeText
	 */
	public function __construct(string $linkToShare, string $text, bool $includeText = true){
		parent::__construct(TwitterSharingButton::getTwitterShareLink($linkToShare, $text), "Share on Twitter",
			$includeText);
		$this->setAction("share");
		$this->setId("twitter-share");
	}
	/**
	 * @param string $linkToShare
	 * @param string $text
	 * @return string
	 */
	public static function getTwitterShareLink(string $linkToShare, string $text): string{
		return "https://twitter.com/intent/tweet?text=" . rawurlencode($text) . '&url=' . rawurlencode($linkToShare) .
			'&via=quantimodo';
	}
}
