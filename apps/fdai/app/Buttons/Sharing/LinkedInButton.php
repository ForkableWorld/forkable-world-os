<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Buttons\Sharing;
use App\UI\FontAwesome;
class LinkedInButton extends EmailSharingButton {
	public $fontAwesome = FontAwesome::LINKEDIN;
	/**
	 * FacebookSharingButton constructor.
	 */
	public function __construct(string $linkToShare, string $subjectLine, string $bodyText, bool $includeText = true){
		parent::__construct($linkToShare, $subjectLine, $bodyText, $includeText);
		$this->setAction("share");
		$this->setId("linkedin-share");
		$this->link = self::getLinkedInShareLink($linkToShare, $subjectLine, $bodyText);
	}
	/**
	 * @param string $linkToShare
	 * @param string $subjectLine
	 * @param string $bodyText
	 * @return string
	 */
	public static function getLinkedInShareLink(string $linkToShare, string $subjectLine, string $bodyText): string{
		$body = rawurlencode($bodyText);
		$subject = rawurlencode($subjectLine);
		return "https://www.linkedin.com/shareArticle?mini=true&url={$linkToShare}&title={$subject}&summary={$body}&source={$linkToShare}";
	}
}
