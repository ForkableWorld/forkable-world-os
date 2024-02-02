<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\States\OnboardingStateButton;
class StartTrackingQMCard extends QMCard {
	/**
	 * @return string
	 */
	public static function getStartTrackingHtml(): string{
		return "
            <h1>
                Personalized Results
            </h1>
            <p>
                Want to see what hidden factors could be influencing YOUR health and happiness?
            </p>
        " . self::getButtonHtml();
	}
	/**
	 * @return string
	 */
	public static function getWantPersonalizedResultsHtml(): string{
		return "
            <p>Want personalized results?</p>
        " . StartTrackingQMCard::getButtonHtml();
	}
	/**
	 * @return string
	 */
	public static function getButtonHtml(): string{
		$b = new OnboardingStateButton();
		$b->setTextAndTitle("Start Tracking");
		return $b->getRectangleWPButton();
	}
}
