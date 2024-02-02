<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\VariableRelationships\CorrelationListExplanationResponseBody;
use App\Slim\Model\User\PublicUser;
use App\Slim\View\Request\QMRequest;
class StudyListResponseBody extends CorrelationListExplanationResponseBody {
    public $studies;
    public $principalInvestigator;
    /**
     * StudiesAndExplanationResponseAndExplanationResponse constructor.
     * @param CorrelationListExplanationResponseBody $correlationResponse
     */
    public function __construct($correlationResponse = null){
        if($correlationResponse){
            parent::__construct($correlationResponse->getCorrelationsOrStudies());
            foreach($correlationResponse as $key => $value){
                $this->$key = $value;
            }
            $this->studies = QMStudy::convertCorrelationsToStudies($correlationResponse->correlations);
            unset($this->correlations);
        }else{
            parent::__construct(null);
        }
        $this->setPrincipalInvestigator();
        if($this->studies){$this->setListResponseHtml();}
    }
    /**
     * @return bool|QMStudy
     */
    protected function getFirstCorrelationOrStudy(){
        if(!isset($this->studies[0])){
            return false;
        }
        return $this->studies[0];
    }
    /**
     * @return QMStudy|HasCauseAndEffect[]
     */
    public function getCorrelationsOrStudies(): array{
        return $this->studies;
    }
    /**
     * @param QMPopulationStudy[]|QMUserStudy[]|QMCohortStudy[]|QMStudy[] $studies
     */
    public function setStudies(array $studies){
        $this->studies = $studies;
        $this->setListResponseHtml();
    }
    /**
     * @param int $userId
     * @return bool|PublicUser
     */
    public function setPrincipalInvestigator(int $userId = null){
        if(!$userId){
            $userId = QMRequest::getParamInt('principalInvestigatorUserId');
        }
        if($userId){
            $user = PublicUser::find($userId);
            $this->principalInvestigator = $user;
            $this->description = "These are studies created by ".$user->displayName.".";
            $this->summary = "Studies by ".$user->displayName;
            $this->setAvatar($user->getAvatar());
        }
        return $this->principalInvestigator;
    }
}
