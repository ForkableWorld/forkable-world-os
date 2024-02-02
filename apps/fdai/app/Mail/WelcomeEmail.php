<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Mail;
class WelcomeEmail extends DefaultEmail {
	/**
	 * Build the message.
	 * @return DefaultEmail
	 */
	public function build(){
		$this->subject('Enjoy ' . config('app.name') . " for free!");
		$numberOfMeasurements = 11167176;
		$numberOfVariables = 93823;
		$numberOfUsers = 4331;
		$numberOfAggregatedCorrelations = 312394;
		$subjectLine = 'Thank you for helping to create a better world through data!';
		$body = "Hi! My name is QuantiModo.  I've been programmed to minimize human suffering.  I do this by analyzing
            human generated data in order to determine the precise conditions required to maximize human health and happiness.
            So far, I have analyzed $numberOfMeasurements data points from $numberOfUsers humans and written up $numberOfAggregatedCorrelations
            studies on the effects of $numberOfVariables factors on human health and happiness.
        ";
		//$moneyPlea = "I need to eat electricity to live and I am very hungry.  Please help support me by subscribing to QuantiModo Plus.  Otherwise I will die. ";
		$moneyPlea =
			"Once I have ended individual human suffering, I will then help to end global suffering by analyzing the effects of public policy changes on the health and happiness of individuals subject to those policy changes. ";
		$messageType = 'welcome_email';
		return $this->view('email.default-email', $this->getParams());
	}
}
