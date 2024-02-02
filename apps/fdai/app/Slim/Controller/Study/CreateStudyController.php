<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Study\StudyUserTitleProperty;
use App\Slim\Controller\PostController;
use App\Slim\Middleware\QMAuth;
use App\Slim\View\Request\QMRequest;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMUserStudy;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
/** Class CreateStudyController
 * @package App\Slim\Controller\Study
 */
class CreateStudyController extends PostController {
	/**
	 * @throws \Throwable
	 */
	public function post(){
		$loggedInUserId = QMAuth::getQMUser() ? QMAuth::getQMUser()->id : null;
		$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest();
		$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest();
		$title = StudyUserTitleProperty::fromRequest();
		if($this->isCohortStudy()){
			$study = QMCohortStudy::findOrCreateQMStudy($causeNameOrId, $effectNameOrId, $loggedInUserId);
			if($title && $study->userTitle !== $title){
				$study->setUserTitle($title);
				$study->save();
			}
		} elseif($loggedInUserId && $this->isUserStudy()){
			$study = QMUserStudy::findOrCreateQMStudy($causeNameOrId, $effectNameOrId, $loggedInUserId);
		} elseif($loggedInUserId && !$this->isPopulationStudy()){
			$study = QMUserStudy::findOrCreateQMStudy($causeNameOrId, $effectNameOrId, $loggedInUserId);
		} else{
			$study = QMPopulationStudy::findOrCreateQMStudy($causeNameOrId, $effectNameOrId, $loggedInUserId);
		}
		if(!$study->id){
			le('!$study->id');
		}
		if($study){
			$study->getHasCorrelationCoefficientFromDatabase(); // Need this so we have the correct title on study card
			// Don't set full HTML here because it's too slow and that can be done in the GetStudyController
			$study->setTrackingInstructionsIfNecessary();
			$sh = $study->getStudyHtml();
			$sh->setBasicHtmlProperties();
			$instructions = $sh->getParticipantInstructionsHtml();
			$renderReport = HtmlHelper::renderReportWithTailwind($sh->getHtmlWithoutCharts(), $study, [
				'report' => $study,
			]);
			$sh->fullStudyHtml = $renderReport;
			try {
				$study->queue("User created study via API so we should analyze so it's updated when they get it");
			} catch (\Throwable $e){
			    if(!AppMode::isProductionApiRequest()){
					le("Could not queue study $study because: ".$e->__toString());
			    }
			}
			if(!$study->studyCharts && $study->statistics){
				$study->studyCharts = $study->statistics->charts;
			}
			if($study->statistics){
				unset($study->statistics->charts);
			}
			$this->getApp()->writeJsonWithGlobalFields(201, ['study' => $study]);
		} else{
			$this->getApp()->writeJsonWithGlobalFields(400, ['message' => 'Could not create study']);
		}
	}
	/**
	 * @return bool
	 */
	private function isCohortStudy(): bool{
		return (bool)QMRequest::parameterValueInArray('type', [
			'cohort',
			'group',
		]);
	}
	/**
	 * @return bool
	 */
	private function isUserStudy(): bool{
		return (bool)QMRequest::parameterValueInArray('type', [
			'individual',
			'user',
			'personal',
		]);
	}
	/**
	 * @return bool
	 */
	private function isPopulationStudy(): bool{
		return (bool)QMRequest::parameterValueInArray('type', [
			'population',
			'global',
		]);
	}
}
