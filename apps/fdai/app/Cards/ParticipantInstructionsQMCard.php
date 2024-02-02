<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Cards;
use App\Buttons\States\ImportStateButton;
use App\Buttons\States\RemindersInboxStateButton;
use App\Traits\HasCauseAndEffect;
class ParticipantInstructionsQMCard extends QMCard {
	/**
	 * @param HasCauseAndEffect $hasCauseAndEffect
	 */
	public function __construct($hasCauseAndEffect){
		parent::__construct('participant-instructions');
		$this->setContentAndHtmlContent($hasCauseAndEffect->getCauseVariable()->getTrackingInstructionsHtml() . '<br><br>' .
			$hasCauseAndEffect->getEffectVariable()->getTrackingInstructionsHtml());
		$this->buttons = [
			new ImportStateButton(),
			new RemindersInboxStateButton(),
		];
		$this->sharingButtons = $hasCauseAndEffect->getStudySharing()->getSharingButtons(false);
		$this->setIonIcon($hasCauseAndEffect->getIonIcon());
		$this->setImage($hasCauseAndEffect->getImage());
		$this->sharingTitle = $hasCauseAndEffect->getSharingTitle();
		$this->sharingBody = $hasCauseAndEffect->getSharingDescription();
		$this->title = $hasCauseAndEffect->getStudyQuestion();
		$this->setUrl($hasCauseAndEffect->getJoinUrl());
	}
}
