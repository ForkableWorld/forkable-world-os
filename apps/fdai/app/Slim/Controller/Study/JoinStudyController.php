<?php /*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */ /** @noinspection PhpUnused */
namespace App\Slim\Controller\Study;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Models\Study;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Study\StudyClientIdProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Slim\Controller\PostController;
use App\Studies\QMCohortStudy;
use App\Studies\QMPopulationStudy;
use App\Studies\QMStudy;
class JoinStudyController extends PostController {
	public function post(){
		$studyClientId = StudyClientIdProperty::fromRequest();
		if($studyClientId && stripos($studyClientId, StudyIdProperty::COHORT_STUDY_ID_SUFFIX) !== false){
			$this->joinCohortStudy($studyClientId);
		} else{
			$this->joinPopulationStudy();
		}
	}
	private function joinPopulationStudy(): void{
		$app = $this->getApp();
		$study = QMPopulationStudy::findOrCreateQMStudy(BaseCauseVariableIdProperty::nameOrIdFromRequest(true),
			BaseEffectVariableIdProperty::nameOrIdFromRequest(true), QMStudy::DEFAULT_PRINCIPAL_INVESTIGATOR_ID,
			StudyTypeProperty::TYPE_POPULATION);
		$study->joinStudy();
		if($study->statistics instanceof QMUserVariableRelationship){
			return;
		}
		$app->writeJsonWithoutGlobalFields(201, ['study' => $study]);
	}
	/**
	 * @param string|null $studyClientId
	 */
	public function joinCohortStudy(?string $studyClientId): void{
		if(!$studyClientId){
			le('!$studyClientId');
		}
		$cohortStudy = Study::whereClientId($studyClientId)->first();
		$cohortStudy->joinStudy();
		$this->getApp()->writeJsonWithGlobalFields(201, [
				'status' => 'ok',
				'success' => true,
				'study' => $cohortStudy->getOrSetQMStudy(),
			]);
	}
}
