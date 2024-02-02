<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\Exceptions\UnauthorizedException;
use App\Models\Study;
use App\Models\User;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Controller\Controller;
use App\Slim\Controller\Correlation\GetCorrelationController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMCohortStudy;
use App\Studies\QMStudy;
use App\Studies\StudyListResponseBody;
class GetStudiesController extends GetCorrelationController {
	public function get(){
		$this->setCacheControlHeader(60 * 60);
		if(QMRequest::getParam('downvoted')){
			$r = $this->getDownVotedStudies();
		} elseif($studyId = StudyIdProperty::fromRequest()){
			$r = $this->getStudyById($studyId);
		} elseif($investigatorId = $this->getPrincipalInvestigatorUserId()){
			$r = $this->getStudiesForPrincipalInvestigator($investigatorId);
		} elseif($this->getUserIdParam()){
			$r = $this->getStudiesResponseBody();
		} elseif($this->getParamBool('open') || QMRequest::urlContains('/open')){
			$r = $this->getOpenStudies();
		} elseif($this->getParamBool('joined') || QMRequest::urlContains('/joined')){
			$r = $this->getJoinedStudies();
		} elseif($this->getParamBool('created') || QMRequest::urlContains('/created')){
			$r = $this->getCreatedStudies();
		} elseif($this->getParamBool('population')){
			$r = $this->getPopulationStudiesResponseBody();
		} else{
			$r = $this->getStudiesResponseBody();
		}
		return $this->writeJsonWithGlobalFields(200, $r);
	}
	/**
	 * @return int|null
	 */
	public function getPrincipalInvestigatorUserId(): ?int{
		$id = $this->getParamInt('principalInvestigatorUserId');
		return $id;
	}
	/**
	 * @return StudyListResponseBody
	 * @throws UnauthorizedException
	 */
	protected function getDownVotedStudies(){
		$r = $this->getPopulationStudiesResponseBody();
		$r->description = "These are studies that you previously down-voted.";
		$r->summary = "Down-Voted Studies";
		return $r;
	}
	/**
	 * @param string $studyId
	 * @return StudyListResponseBody
	 * @throws UnauthorizedException
	 */
	protected function getStudyById(string $studyId): StudyListResponseBody{
		$study = QMStudy::find($studyId);
		if(!$study->getIsPublic()){
			$user = QMAuth::getAuthenticatedUserOrThrowException();
			if($user->id !== $study->getUserId()){
				throw new UnauthorizedException("Please contact the owner of this study and ask them to make it public");
			}
		}
		$r = new StudyListResponseBody();
		$r->setStudies([$study]);
		return $r;
	}
	/**
	 * @param int $investigatorId
	 * @return StudyListResponseBody
	 */
	protected function getStudiesForPrincipalInvestigator(int $investigatorId): StudyListResponseBody{
		$r = new StudyListResponseBody();
		$studies = QMCohortStudy::getCohortStudies(BaseCauseVariableIdProperty::fromRequest(),
			BaseEffectVariableIdProperty::fromRequest(), $investigatorId);
		$r->setStudies($studies);
		return $r;
	}
	/**
	 * @return StudyListResponseBody
	 * @throws UnauthorizedException
	 */
	protected function getOpenStudies(){
		$r = $this->getPopulationStudiesResponseBody();
		if(empty($r->studies)){
			$r->setStudies(QMCohortStudy::getCohortStudies());  // TODO: Maybe uncomment once we have more cohort studies?
		}
		$r->description = "These are studies that anyone can join.";
		$r->summary = "Open Studies";
		return $r;
	}
	/**
	 * @return StudyListResponseBody
	 */
	protected function getJoinedStudies(): StudyListResponseBody{
		$r = new StudyListResponseBody();
		$studies = QMAuth::getQMUser()->getStudiesJoined();
		$r->setStudies($studies);
		$r->description = "These are studies that you are currently sharing your data with.";
		$r->summary = "Studies Joined";
		return $r;
	}
	/**
	 * @return StudyListResponseBody
	 * @throws UnauthorizedException
	 */
	protected function getCreatedStudies(): StudyListResponseBody{
		$r = new StudyListResponseBody();
		$id = UserIdProperty::fromRequestOrAuthenticated(true);
		$user = User::findInMemoryOrDB($id);
		if(!$user){
			le("User $id not found!");
		}
		$hasMany = $user->studies();
		$limit = $this->getLimit();
		$offset = $this->getOffset();
		$hasMany->limit($limit);
		$hasMany->offset($offset);
		$studies = $hasMany->get();
		$studies = Study::toDBModels($studies);
		$r->setStudies($studies);
		$r->description = "These are studies that you have created.";
		$r->summary = "Your Studies";
		$r->setPrincipalInvestigator(Controller::getUserIdParamOrAuthenticatedUserId());
		return $r;
	}
}
