<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\QMButton;
use App\Cards\ParticipantInstructionsQMCard;
use App\Slim\Model\StaticModel;
use App\Traits\HasCauseAndEffect;
class StudyParticipantInstructions extends StaticModel {
    public $instructionsForCauseVariable;
    public $instructionsForEffectVariable;
    public $card;
    /**
     * @param HasCauseAndEffect|QMStudy $study
     */
    public function __construct($study = null){
        if(!$study){
            return;
        }
        $urlParams = ['afterConnectGoTo' => $study->getStudyLinks()->getStudyJoinUrl()];
        $this->instructionsForCauseVariable = $study->getOrSetCauseQMVariable()->setTrackingInstructionsHtml($urlParams);
        $this->instructionsForEffectVariable = $study->getOrSetEffectQMVariable()->setTrackingInstructionsHtml($urlParams);
        $card = new ParticipantInstructionsQMCard($study);
        $this->card = $card;
    }
    /**
     * @return ParticipantInstructionsQMCard
     */
    public function getCard(): ParticipantInstructionsQMCard{
        return $this->card;
    }
    public function getButton(array $params = []): QMButton{
        return $this->getCard()->getButton();
    }
	public function getUrl(array $params = []): string{
		return $this->getButton()->getUrl();
	}
	public function getTitleAttribute(): string{
		return $this->getCard()->getTitleAttribute();
	}
}
