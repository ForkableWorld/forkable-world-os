<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\UI\FontAwesome;
use App\UI\ImageUrls;
class RedditSharingButton extends SharingButton {
	public $fontAwesome = FontAwesome::REDDIT;
	public $image = ImageUrls::REDDIT_SQUARE;
	/**
	 * @param string $linkToShare
	 * @param string $text
	 * @param bool $includeText
	 */
	public function __construct(string $linkToShare, string $text, bool $includeText = true){
		parent::__construct(self::getRedditTextLink($linkToShare, $text), "Share on Reddit", $includeText);
		$this->setAction("share");
		$this->setId("reddit-share");
	}
	/**
	 * @param string $title
	 * @param string $body
	 * @return string
	 */
	public static function getRedditTextLink(string $title, string $body): string{
		$body = rawurlencode($body);
		$title = rawurlencode($title);
		return "https://www.reddit.com/r/test/submit?title={$title}&text={$body}";
	}
	/**
	 * @param string $title
	 * @param string $url
	 * @return string
	 */
	public static function getRedditUrlLink(string $title, string $url): string{
		$body = rawurlencode($url);
		$title = rawurlencode($title);
		return "https://www.reddit.com/r/test/submit?title={$title}&url={$body}";
	}
}
