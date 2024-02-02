<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\AppSettings\AppSettings;
use App\AppSettings\HostAppSettings;
use App\Buttons\StudyButton;
use App\Charts\GlobalVariableRelationshipCharts\GlobalVariableRelationshipChartGroup;
use App\Charts\ChartGroup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\DataSources\QMClient;
use App\Exceptions\ClientNotFoundException;
use App\Exceptions\NotEnoughDataException;
use App\Logging\QMLog;
use App\Models\Application;
use App\Models\Study;
use App\Properties\Base\BaseClientIdProperty;
use App\Properties\Study\StudyCauseVariableIdProperty;
use App\Properties\Study\StudyEffectVariableIdProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Slim\Middleware\QMAuth;
use App\Storage\S3\S3Private;
use App\UI\IonIcon;
use Illuminate\View\View;
class QMCohortStudy extends QMStudy {
	const TYPE = StudyTypeProperty::TYPE_COHORT;
    public const COLLECTION_NAME = "CohortStudy";
	public static function getS3Bucket():string{return S3Private::getBucketName();}
    public $client;
    public $scopes;
	/**
	 * CohortStudy constructor.
	 * @param Study|null $study
	 */
    public function __construct(Study $study = null){
        $this->setType(StudyTypeProperty::TYPE_COHORT);
        parent::__construct($study);
    }
    public function populateDefaultFields(){
        parent::populateDefaultFields();
        $this->getOrCreateClient();
        $this->getScopes();
    }
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return null|QMCohortStudy
	 */
    public static function createQMStudy($causeNameOrId, $effectNameOrId, int $userId = null, string $type = null):
    QMStudy {
        if(!$userId){
			$userId = QMAuth::getUserId();
			if(!$userId){
				QMAuth::throwUnauthorizedException("You must be logged in to create a study");
			}
        }
        $cohortStudy = parent::createQMStudy($causeNameOrId, $effectNameOrId, $userId,
	        $type);
        $cohortStudy->getOrCreateClient();
		if($causeNameOrId === $effectNameOrId){
			le("You cannot create a cohort study with the same cause and effect");
		}
        $cohortStudy->joinStudy();
        return $cohortStudy;
    }
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return null|QMCohortStudy
	 */
	public static function newQMStudy($causeNameOrId, $effectNameOrId, int $userId = null,
		string $type = null): QMStudy{
		if(!$userId){QMAuth::throwUnauthorizedException('You must be logged in to create a study');}
		$cohortStudy = parent::newQMStudy($causeNameOrId, $effectNameOrId, $userId, StudyTypeProperty::TYPE_COHORT);
		return $cohortStudy;
	}
    /**
     * @return bool
     */
    public function getJoined(): bool{
        if($u = QMAuth::getQMUser()){
            $token = $u->getValidAccessTokenForClient($this->getId());
            if($token){
                $this->joined = true;
            }
        }
        return $this->joined;
    }
    /**
     * @return QMClient
     */
    public function createClient(): QMClient{
        $redirectUris[] = HostAppSettings::instance()->additionalSettings->downloadLinks->webApp;
        $client = QMClient::createClient($this->getId(), $this->getQMUser(), $redirectUris);
//        $appData[AppSettings::FIELD_STUDY] = 1;
//        $appData[AppSettings::FIELD_BILLING_ENABLED] = 0;
//        $appData[AppSettings::FIELD_PREDICTOR_VARIABLE_ID] = $this->getOrSetCauseQMVariable()->getVariableId();
//        $appData[AppSettings::FIELD_OUTCOME_VARIABLE_ID] = $this->getOrSetEffectQMVariable()->getVariableId();
//        $appData[AppSettings::FIELD_APP_DISPLAY_NAME] = $this->getTitleAttribute();
        //$application = $client->createApplication($appData);
        $this->id = $client->clientId;
        return $this->client = $client;
    }
    /**
     * @return bool|null|AppSettings|QMClient
     */
    public function getOrCreateClient(){
        $client = $this->client;
        if(!$client){
            $id = $this->getId();
            $client = QMClient::find($id);
        }
        if(!$client){
            $client = $this->createClient();
        }
        $this->id = $client->clientId;
        return $this->client = $client;
    }
    /**
     * @param string $studyClientId
     * @return QMCohortStudy
     */
    public static function getOrCreateByClientId(string $studyClientId):QMCohortStudy {
	    BaseClientIdProperty::validateCohortStudyClientId($studyClientId);
	    try {
            $application = Application::getClientAppSettings($studyClientId);
            $study = new QMCohortStudy($application->predictorVariableId, $application->outcomeVariableId, $application->userId);
        } catch (ClientNotFoundException $e) {
            QMLog::error($e->getMessage());
			$cause = StudyCauseVariableIdProperty::fromStudyId($studyClientId);
			$effect = StudyEffectVariableIdProperty::fromStudyId($studyClientId);
            $study = self::findOrCreateQMStudy($cause, $effect, null, StudyTypeProperty::TYPE_COHORT);
        }
        return $study;
    }
    public function joinStudy(){
        if($this->getJoined()){
            return;
        }
        QMAuth::getQMUser()->getOrCreateAccessToken($this->getId(), $this->getScopes());
        parent::joinStudy();
    }
    /**
     * @return string
     */
    public function getScopes(): string{
        return $this->scopes = "measurements:read:".urlencode($this->getCauseVariableName())." "."measurements:read:".urlencode($this->getEffectVariableName());
    }
    /**
     * @param int|null $causeVariableId
     * @param int|null $effectVariableId
     * @param int|null $principalInvestigatorUserId
     * @return QMCohortStudy[]|QMStudy[]
     */
    public static function getCohortStudies(int $causeVariableId = null, int $effectVariableId = null,
                                            int $principalInvestigatorUserId = null): array{
        $qb = Study::query();
        $qb->where(Study::FIELD_TYPE, StudyTypeProperty::TYPE_COHORT);
        if($principalInvestigatorUserId){
            $qb->where(Study::FIELD_USER_ID, $principalInvestigatorUserId);
        }
        if($causeVariableId){
            $qb->where(Study::FIELD_CAUSE_VARIABLE_ID, $causeVariableId);
        }
        if($effectVariableId){
            $qb->where(Study::FIELD_EFFECT_VARIABLE_ID, $effectVariableId);
        }
        $rows = $qb->get();
        $studies = static::convertRowsToModels($rows->all(), true);
        return $studies;
    }
    /**
     * @param string|int $causeNameOrId
     * @param string|int $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return string
     */
    public static function generateStudyId($causeNameOrId, $effectNameOrId, int $userId = null, string $type = null): string{
        $id = StudyIdProperty::generateStudyIdPrefix($causeNameOrId, $effectNameOrId).'-user-'.$userId.'-'.
            StudyIdProperty::COHORT_STUDY_ID_SUFFIX; // Can't use username because they aren't sub-domain url-safe and url encode adds %
        return $id;
    }
    /**
     * @return GlobalVariableRelationshipChartGroup
     * @throws NotEnoughDataException
     */
    public function setCharts(): ChartGroup {
        return $this->studyCharts = new GlobalVariableRelationshipChartGroup($this->getHasCorrelationCoefficient());
    }
    /**
     * @return QMGlobalVariableRelationship
     */
    public function setHasCorrelationCoefficientFromDatabase(): ?QMGlobalVariableRelationship{
        return $this->correlationFromDatabase =
            QMGlobalVariableRelationship::getSingleAggregatedCorrelationByVariableNames($this->getCauseVariableName(),
                $this->getEffectVariableName());
    }
    /**
     * @return QMGlobalVariableRelationship|QMUserVariableRelationship
     * @throws NotEnoughDataException
     */
    public function getCreateOrRecalculateStatistics(): QMVariableRelationship{
        if($c = $this->getHasCorrelationCoefficientIfSet()){
            return $c;
        }
        $c = $this->getOrCreateGlobalVariableRelationship(); // TODO: Limit to people who joined the study
        return $this->setStatistics($c);
    }
    /**
     * @return string
     */
    public function getCategoryDescription(): string{
        return "Examination of the likely effects of a predictor variable on an outcome variable for an specific group of individuals";
    }
    /**
     * @return QMVariableRelationship
     * @throws NotEnoughDataException
     */
    public function createStatistics(): QMVariableRelationship{
        return $this->createGlobalVariableRelationship();
    }
    /**
     * @return QMGlobalVariableRelationship
     * @throws NotEnoughDataException
     */
    public function getHasCorrelationCoefficient(){
        return parent::getHasCorrelationCoefficient();
    }
    /**
     * @return string
     */
    public function getTitleWithUserName(): string {
        return $this->getTitleAttribute()." for Cohort";
    }
    public function getIonIcon(): string {
        return IonIcon::androidPeople;
    }
    protected static function getIndexPageView(): View{
        return view('studies-index', [
            'buttons' => static::generateIndexButtons(),
            'heading' => "Individual Case Studies"
        ]);
    }
    protected static function generateIndexButtons(): array{
        $studies = Study::whereType(StudyTypeProperty::TYPE_COHORT)->get();
        return StudyButton::toButtons($studies);
    }
    public static function getUrlFolder(): string{
        return "cohort-studies";
    }
    public function getShowContentView(array $params = []): View{
        try {
            $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
        }
        return view('population-study-content', $this->getShowParams($params));
    }
    public function getShowPageView(array $params = []): View{
        try {
            $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
        }
        return view('population-study', $this->getShowParams($params));
    }
    public function getUrlSubPath(): string{
        return $this->getId();
    }
    public function getStudyType(): string{
        return StudyTypeProperty::TYPE_COHORT;
    }
}
