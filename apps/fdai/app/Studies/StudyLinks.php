<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\Sharing\EmailSharingButton;
use App\Buttons\Sharing\FacebookSharingButton;
use App\Buttons\Sharing\RedditSharingButton;
use App\Buttons\Sharing\TwitterSharingButton;
use App\Buttons\States\StudyJoinStateButton;
use App\Buttons\States\StudyStateButton;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Vote;
use App\Slim\Model\StaticModel;
use App\Slim\View\Request\QMRequest;
use App\Traits\HasCauseAndEffect;
use App\Utils\IonicHelper;
use App\Utils\UrlHelper;
use InvalidArgumentException;
class StudyLinks extends StaticModel {
    private $hasCauseAndEffect;
    public $studyJoinLink;
    public $studyLinkDynamic;
    public $studyLinkEmail;
    public $studyLinkFacebook;
    public $studyLinkReddit;
    public $studyLinkStatic;
    public $studyLinkTwitter;
    public $downVoteUrl;
    public $upVoteUrl;
    /**
     * StudyImages constructor.
     * @param HasCauseAndEffect|QMStudy $hasCauseAndEffect
     */
    public function __construct($hasCauseAndEffect = null){
        if(!$hasCauseAndEffect){return;}
        $this->setHasCauseAndEffect($hasCauseAndEffect);
        $this->getStudyLinkStatic();
        $this->getStudyUrlDynamic();
        $this->getStudyLinkFacebook();
        $this->getStudyLinkReddit();
        $this->getStudyLinkTwitter();
        $this->getStudyLinkEmail();
        $this->getStudyJoinUrl();
		$this->getDownVoteUrl();
		$this->getUpVoteUrl();
		if(!$this->studyLinkDynamic){le("no studyLinkDynamic");}
    }
    /**
     * @return string
     */
    private function getStudyId(): string{
        return $this->getHasCauseAndEffect()->getStudyId();
    }
    /**
     * @return string
     */
    private function getStudyTitle(): string{
        if($this->hasCauseAndEffect && $this->getHasCauseAndEffect()->studyText){
            return $this->getHasCauseAndEffect()->getStudyTitle();
        }
	    return "Help us determine the effects of ".$this->getCauseVariableName()." on ".$this->getEffectVariableName()."!";
    }
    /**
     * @return string
     */
    private function getCauseVariableName(): string{
        if(!$this->hasCauseAndEffect && $this->hasCauseAndEffect){
            return $this->getHasCauseAndEffect()->getCauseVariableName();
        }
        $study = $this->getHasCauseAndEffect();
        if(!$study){
            le("No study!");
        }
        return $study->getCauseVariableName();
    }
    /**
     * @return string
     */
    private function getEffectVariableName(): string{
        if(!$this->hasCauseAndEffect && $this->hasCauseAndEffect){
            return $this->getHasCauseAndEffect()->getEffectVariableName();
        }
        return $this->getHasCauseAndEffect()->getEffectVariableName();
    }
    /**
     * @return int
     */
    private function getUserId(): ?int {
        $correlation = $this->getHasCauseAndEffect();
        if($correlation && isset($correlation->userId)){
            return $correlation->userId;
        }
        $study = $this->getHasCauseAndEffect();
        if($study && $study->getUserId()){
            return $study->getUserId();
        }
        return null;
    }
    /**
     * @return string
     */
    public function getStudyLinkEmail(): string{
        return $this->studyLinkEmail =
	        EmailSharingButton::getEmailShareLink($this->getStudyLinkStatic(), $this->getStudyTitle(),
	                                              "Check out my study at ");
    }
    /**
     * @return string
     */
    public function getStudyLinkFacebook(): string{
        return $this->studyLinkFacebook = FacebookSharingButton::getFacebookShareLink($this->getStudyLinkStatic());
    }
    /**
     * @return string
     */
    public function getStudyLinkReddit(): string{
        return $this->studyLinkReddit =
	        RedditSharingButton::getRedditTextLink($this->getStudyTitle(), $this->getStudyInvitationText());
    }
    public function getStudyInvitationTextShort(): string{
        return "Please donate 30 seconds/day to help us discover the short and long-term effects of {$this->getCauseVariableName()} on {$this->getEffectVariableName()}!  Thank you!";
    }
    public function getStudyInvitationText(): string{
        $create = IonicHelper::getStudyCreationUrl();
        return "
Hi!  

I'm doing an observational study based on donated {$this->getCauseVariableName()} and {$this->getEffectVariableName()} data.  
I'd be very grateful if you'd join to help us discover the short and long-term effects and what factors make it more or less effective.  
It only requires about 30 seconds to click 2 buttons a day to record your {$this->getCauseVariableName()} and {$this->getEffectVariableName()}.  

{$this->getStudyLinkStatic()}
";

//You can:
//Join this Study: {$this->getStudyJoinUrl()}
//or
//See Current Results: {$this->getStudyLinkStatic()}
//or
//Create a Different Study: {$create}
//
//Please let me know if you have any issues.
//Thank you!
//        ";
    }
    /**
     * @return string $studyLinkStatic
     */
    public function getStudyLinkStatic(array $params = []): string{
        $link = self::generateStudyLinkStatic($this->getStudyId(), $params);
        return $this->studyLinkStatic = $link;
    }
    /**
     * @param string|null $studyId
     * @return string
     */
    public static function generateStudyLinkStatic(string $studyId, array $params = []): string{
        // For some reason study links keep having utopia.quantimo.do and local.quantimo.do in them
        return QMRequest::getAppHostUrl("/study/$studyId", $params);
    }
    /**
     * @return string $studyLinkTwitter
     */
    public function getStudyLinkTwitter(): string{
        return $this->studyLinkTwitter = TwitterSharingButton::getTwitterShareLink($this->getStudyLinkStatic(), $this->getStudyInvitationTextShort());
    }
    /**
     * @param array $additionalParams
     * @return string
     */
    public function getStudyUrlDynamic(array $additionalParams = []): string{
        $url = self::generateStudyUrlDynamic($this->getCauseVariableId(),
            $this->getEffectVariableId(), $this->getUserId(), $this->getStudyId());
        if($additionalParams){$url = UrlHelper::addParams($url, $additionalParams);}
        return $this->studyLinkDynamic = $url;
    }
    /**
     * @return string
     */
    public function getRecalculateStudyUrl(): string{
        $url = self::generateStudyUrlDynamic($this->getCauseVariableId(), $this->getEffectVariableId(), $this->getUserId(), $this->getStudyId());
        return $url."&recalculate=true";
    }
    /**
     * @return string
     */
    public function getStudyJoinUrl(): string{
        return $this->studyJoinLink = $this->getButton()->getUrl();
    }

    /**
     * @return StudyJoinStateButton
     */
	public function getButton(): StudyJoinStateButton {
		return new StudyJoinStateButton($this->hasCauseAndEffect);
	}
    /**
     * @return array
     */
    private function getStudyJoinUrlParams(): array{
        $params = [];
        if($id = $this->getCauseVariableId()){$params['causeVariableId'] = $id;}
        if($name = $this->getCauseVariableName()){ // Need name for populating text
            $params['causeVariableName'] = rawurlencode($name);
        }
        if($id = $this->getEffectVariableId()){$params['effectVariableId'] = $id;}
        if($name = $this->getEffectVariableName()){ // Need name for populating text
            $params['effectVariableName'] = rawurlencode($name);
        }
        if($id = $this->getStudyId()){$params['studyId'] = rawurlencode($id);}
        return $params;
    }
    /**
     * @param HasCauseAndEffect|\App\Studies\QMStudy $study
     */
    public function setHasCauseAndEffect($study){
        $this->hasCauseAndEffect = $study;
    }
    /**
     * @return string
     */
    public function getDownVoteUrl(): string{
        $url = Vote::generateVoteUrl($this->getStudyUrlParams());
        $url = UrlHelper::addParam($url, 'vote', 'down');
        return $this->downVoteUrl = $url;
    }
    /**
     * @return string
     */
    public function getUpVoteUrl(): string {
        $url = Vote::generateVoteUrl($this->getStudyUrlParams());
        $url = UrlHelper::addParam($url, 'vote', 'up');
        return $this->upVoteUrl = $url;
    }
    /**
     * @return array
     */
    public function getStudyUrlParams(): array{
        $params = $this->getStudyJoinUrlParams();
        // Study ID should contain user id if necessary and adding this param could cause confusion
        //if($this->getUserId()){$params['userId'] = $this->getUserId();}
        return $params;
    }
    /**
     * @param string|int $causeNameOrId
     * @param string|int $effectNameOrId
     * @param int|null $userId
     * @param string|null $studyId
     * @return string
     */
    public static function generateStudyUrlDynamic($causeNameOrId, $effectNameOrId, int $userId = null,
                                                   string $studyId = null): string{
        $params = [];
        if(is_string($causeNameOrId)){
            $params['causeVariableName'] = rawurlencode($causeNameOrId);
        }else{
            $params['causeVariableId'] = $causeNameOrId;
        }
        if(is_string($effectNameOrId)){
            $params['effectVariableName'] = rawurlencode($effectNameOrId);
        }else{
            $params['effectVariableId'] = $effectNameOrId;
        }
        if($userId){
            $params['userId'] = rawurlencode($userId);
        }
        if($studyId){
            $params['studyId'] = rawurlencode($studyId);
        }
        if($studyId && is_numeric($studyId)){
            throw new InvalidArgumentException("study id should not be numeric!");
        }
        return StudyStateButton::getStudyUrl($params);
    }
    /**
     * @return QMStudy|HasCauseAndEffect
     */
    public function getHasCauseAndEffect() {
        if (!$this->hasCauseAndEffect) {
            $this->hasCauseAndEffect = $this->getHasCauseAndEffect()->findInMemoryOrNewQMStudy();
        }
        return $this->hasCauseAndEffect;
    }
    /**
     * @return int
     */
    private function getCauseVariableId(): int{
        if($this->hasCauseAndEffect){
            return $this->getHasCauseAndEffect()->getCauseVariableId();
        }
        return $this->getHasCauseAndEffect()->getCauseVariableId();
    }
    /**
     * @return int
     */
    private function getEffectVariableId(): int{
        if($this->hasCauseAndEffect){
            return $this->getHasCauseAndEffect()->getEffectVariableId();
        }
        return $this->getHasCauseAndEffect()->getEffectVariableId();
    }
	public function getTitleAttribute(): string{
		return "Study Links for ".$this->getStudyTitle();
	}
	public function getUrl(array $params = []): string{
		return $this->getStudyLinkStatic();
	}
}
