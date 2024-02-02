<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\QMButton;
use App\Buttons\Sharing\SharingButton;
use App\Traits\HasCauseAndEffect;
class StudySharing {
    public $shareUserMeasurements;
    public $sharingDescription;
    public $sharingTitle;
    private $hasCauseAndEffect;
    /**
     * @param HasCauseAndEffect|\App\Models\UserVariableRelationship $hasCauseAndEffect
     */
    public function __construct($hasCauseAndEffect){
        $this->hasCauseAndEffect = $hasCauseAndEffect;
        $this->shareUserMeasurements = $hasCauseAndEffect->getIsPublic();
        $this->sharingDescription = $hasCauseAndEffect->getSharingDescription();
        $this->sharingTitle = $hasCauseAndEffect->getSharingTitle();
    }
    /**
     * @param bool $includeText
     * @return QMButton[]
     */
    public function getSharingButtons(bool $includeText = true): array{
        return SharingButton::getSharingButtons(StudyLinks::generateStudyLinkStatic(
			$this->getHasCauseAndEffect()->getStudyId()),
	        $this->sharingTitle, $this->sharingDescription, $includeText);
    }
    /**
     * @return \App\Models\UserVariableRelationship|HasCauseAndEffect
     */
    public function getHasCauseAndEffect(){
        return $this->hasCauseAndEffect;
    }
}
