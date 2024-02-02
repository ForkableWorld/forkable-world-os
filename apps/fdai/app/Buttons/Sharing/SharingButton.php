<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn 
 */

namespace App\Buttons\Sharing;
use App\Buttons\QMButton;
abstract class SharingButton extends QMButton {
	/**
	 * @param string $link
	 * @param string $title
	 * @param string $body
	 * @param bool $includeText
	 * @return QMButton[]
	 */
	public static function getSharingButtons(string $link, string $title, string $body,
		bool $includeText = true): array{
		$buttons = [];
		$buttons[] = new EmailSharingButton($link, $title, $body, $includeText);
		$buttons[] = new FacebookSharingButton($link, $includeText);
		//$buttons[] = new GoogleSharingButton($link, $includeText);
		$buttons[] = new TwitterSharingButton($link, $title, $includeText);
		return $buttons;
	}
}
