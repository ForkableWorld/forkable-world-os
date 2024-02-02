<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\UI\ImageHelper;
use App\Utils\IonicHelper;
use App\Variables\QMVariable;
class FacesRatingQMCard extends QMCard {
	/**
	 * FacesRatingCard constructor.
	 * @param QMVariable $v
	 * @param string $footerHtml
	 */
	public function __construct(QMVariable $v, string $footerHtml = ''){
		parent::__construct();
		$this->setTitle($v->getQuestion());
		$html = self::getFacesRatingButtonsHtml() . "<div>$footerHtml</div>";
		$this->setContentAndHtmlContent($html);
		$this->setUrl(IonicHelper::getInboxUrl());
	}
	/**
	 * @param string $question
	 * @param string|null $footer
	 * @return string
	 */
	public static function getFacesRatingCardHtml(string $question, string $footer = null): string{
		$faces = self::getFacesRatingButtonsHtml();
		return QMCard::generateCardHtml($question, $faces, $footer);
	}
	/**
	 * @return string
	 */
	public static function getFacesRatingButtonsHtml(): string{
		$baseUrl = ImageHelper::BASE_URL . "rating/100/face_rating_button_100";
		$inboxUrl = IonicHelper::getIntroUrl(['existingUser' => true]);
		return "
            <a class=\"mcnButton \" title=\"Open Reminder Inbox\"
                href=\"$inboxUrl\"
                target=\"_blank\"
                style=\"

                    text-decoration: none;\">
                <div id=\"sectionRate\" class=\"rating-section\" style='margin: auto;'>
                    <img src=\"" . $baseUrl . "_depressed.png\"
                        style='width: 18%; display: inline-block;'
                        id=\"buttonMoodDepressed\"><span>&nbsp;</span>
                    <img src=\"" . $baseUrl . "_sad.png\"
                        style='width: 18%; display: inline-block;'
                        id=\"buttonMoodSad\"><span>&nbsp;</span>
                    <img src=\"" . $baseUrl . "_ok.png\"
                        style='width: 18%; display: inline-block;'
                        id=\"buttonMoodOk\"><span>&nbsp;</span>
                    <img src=\"" . $baseUrl . "_happy.png\"
                        style='width: 18%; display: inline-block;'
                        id=\"buttonMoodHappy\"><span>&nbsp;</span>
                    <img src=\"" . $baseUrl . "_ecstatic.png\"
                        style='width: 18%; display: inline-block;'
                        id=\"buttonMoodEcstatic\">
                </div>
            </a>
        ";
	}
}
