<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\StudyButton;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\UnauthorizedException;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\Measurement;
use App\Models\Study;
use App\Models\User;
use App\Models\UserVariable;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyIsPublicProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\User\UserIdProperty;
use App\Slim\Middleware\QMAuth;
use App\Storage\S3\S3Private;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasModel\HasUserCauseAndEffect;
use App\Types\QMArr;
use App\UI\IonIcon;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
/** Class UserStudy
 * @package app/Studies
 */
class QMUserStudy extends QMStudy {
	use HasUserCauseAndEffect;
	const TYPE = StudyTypeProperty::TYPE_INDIVIDUAL;
    public const CLASS_PARENT_CATEGORY = Study::CLASS_CATEGORY;
    public const COLLECTION_NAME = "UserStudy";
    public static function getS3Bucket():string{return S3Private::getBucketName();}
    private $variableRequestParams = [];
    public $userId;
	/**
	 * Study constructor.
	 * @param Study|null $study
	 */
    public function __construct(Study $study = null){
        $this->setType(StudyTypeProperty::TYPE_INDIVIDUAL);
        parent::__construct($study);
    }
    /**
     * @return bool
     *
     */
    public function getJoined(): bool{
        if($this->userIdIsLoggedInUser()){
            $this->joined = true;
        }
        return $this->joined;
    }
    /**
     * @return QMUserVariableRelationship
     */
    public function setHasCorrelationCoefficientFromDatabase(): ?QMUserVariableRelationship{
        $c = QMUserVariableRelationship::findByNamesOrIds($this->getUserId(),
            $this->getCauseVariableId(), $this->getEffectVariableId());
        return $this->correlationFromDatabase = $c;
    }
    /**
     * @return QMUserVariableRelationship
     * @throws NotEnoughDataException
     */
    public function getCreateOrRecalculateStatistics(): QMVariableRelationship {
        if ($this->getHasCorrelationCoefficientIfSet()) {
			$this->getStudyHtml()->getStudyAbstractHtml();
            return $this->statistics;
        }
        if ($this->weShouldRecalculate()) {
            $c = $this->createStatistics();
        } else {
            $c = $this->getHasCorrelationCoefficientFromDatabase();
            if (!$c) {
                $c = $this->createStatistics();
            }
        }
        return $this->setStatistics($c);
    }
    /**
     * @param string|int $causeNameOrId
     * @return QMUserVariable
     * @throws UnauthorizedException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setCauseQMVariable($causeNameOrId = null): QMUserVariable{
        if(!$causeNameOrId){
            $causeNameOrId = $this->getCauseVariableNameOrId();
        }
        if(!QMStudy::weShouldGenerateFullStudyWithChartsCssAndInstructions()){
            $causeVariable = QMUserVariable::findOrCreateByNameOrId($this->getUserId(),
                $causeNameOrId, $this->getVariableRequestParams());
        }else{
            $causeVariable = QMUserVariable::findWithCharts($this->getUserId(),
                $causeNameOrId, $this->getVariableRequestParams());
        }
        if(!$causeVariable){
            $this->notAuthorizedOrNotFoundException('cause', $causeNameOrId);
        }
        $this->causeVariableName = $causeVariable->name;
        return $this->causeVariable = $causeVariable;
    }
    /**
     * @param string $causeOrEffect
     * @param int|string $nameOrId
     * @throws UnauthorizedException
     */
    private function notAuthorizedOrNotFoundException(string $causeOrEffect, $nameOrId){
        if($this->loggedInUserMatchesUserId()){
            throw new NotFoundHttpException("$causeOrEffect variable $nameOrId not found for user ".$this->getUserId());
        }
        $loggedInUser = QMAuth::getQMUser();
        throw new UnauthorizedException("User $loggedInUser->id is not authorized to get $causeOrEffect ".
            "variable matching: $nameOrId for user ".$this->getUserId());
    }
    /**
     * @return bool
     *
     */
    private function loggedInUserMatchesUserId(): bool{
        return $this->userIdIsLoggedInUser();
    }
    /**
     * @param string|int $effectNameOrId
     * @return QMUserVariable
     * @throws UnauthorizedException
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    public function setEffectQMVariable($effectNameOrId = null): QMUserVariable{
        if(!$effectNameOrId){
            $effectNameOrId = $this->getEffectVariableNameOrId();
        }
        if(!QMStudy::weShouldGenerateFullStudyWithChartsCssAndInstructions()){
            $effectVariable = QMUserVariable::findOrCreateByNameOrId($this->getUserId(),
                $effectNameOrId, $this->getVariableRequestParams());
        }else{
            $effectVariable = QMUserVariable::findWithCharts($this->getUserId(), $effectNameOrId,
                $this->getVariableRequestParams());
        }
        if(!$effectVariable){
            $this->notAuthorizedOrNotFoundException('effect', $effectNameOrId);
        }
        $this->effectVariableName = $effectVariable->name;
        return $this->effectVariable = $effectVariable;
    }
    /**
     * @return array
     *
     */
    private function getVariableRequestParams(): array{
        if(!QMAuth::loggedInUserIsAuthorizedToAccessAllDataForUserId($this->getUserId())){
            $this->variableRequestParams['isPublic'] = true;
        }
        $this->variableRequestParams['includeTags'] = true;
        return $this->variableRequestParams;
    }
    /**
     * @return bool
     * @throws UnauthorizedException
     */
    private function userIdIsLoggedInUser(): bool{
        if(!QMAuth::getQMUser()){
            return false;
        }
        if(QMAuth::getQMUser()->id === $this->getUserId()){
            return true;
        }
        return false;
    }
    /**
     * @return CorrelationChartGroup
     */
    public function setCharts(): ChartGroup {
        try {
            $statistics = $this->getHasCorrelationCoefficient();
            $charts = new CorrelationChartGroup($statistics);
            return $this->studyCharts = $charts;
        } catch (NotEnoughDataException $e) {
            $this->setErrorMessage($e->getMessage());
        }
        $charts = new CorrelationChartGroup();
        return $this->studyCharts = $charts;
    }
    /**
     * @return int
     *
     */
    public function getUserId(): ?int {
        if($this->userId){
            return (int)$this->userId;
        }
        /** @var QMUserVariableRelationship $c */
        if($c = $this->getHasCorrelationCoefficientIfSet()){
            return $this->userId = $c->getUserId();
        }
        if($this->causeVariable){
            return $this->userId = $this->getOrSetCauseQMVariable()->getUserId();
        }
        if(UserIdProperty::fromRequestOrAuthenticated()){
            $this->userId = UserIdProperty::fromRequestOrAuthenticated();
        }else{
            $message = "Please log in or provide userId in request";
            QMLog::errorOrInfoIfTesting($message);
            le($message);
        }
        return $this->userId;
    }
	/**
	 * @return QMUserStudy[]
	 * @throws NoUserVariableRelationshipsToAggregateException
	 * @throws NotEnoughDataException
	 * @throws \App\Exceptions\AlreadyAnalyzedException
	 * @throws \App\Exceptions\AlreadyAnalyzingException
	 * @throws \App\Exceptions\DuplicateFailedAnalysisException
	 * @throws \App\Exceptions\TooSlowToAnalyzeException
	 */
    public static function getUserStudies(): array{
        $requestParams = qm_request()->query();
        $userVariableRelationships = QMUserVariableRelationship::getOrCreateUserOrGlobalVariableRelationships($requestParams);
        $studies = QMStudy::convertCorrelationsToStudies($userVariableRelationships);
        return $studies;
    }
    /**
     * @param string int|string $causeNameOrId
     * @param string int|string $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return string
     */
    public static function generateStudyId($causeNameOrId, $effectNameOrId, int $userId = null, string $type = null): string{
        $id = StudyIdProperty::generateStudyIdPrefix($causeNameOrId, $effectNameOrId) .'-user-'.$userId.
            StudyIdProperty::INDIVIDUAL_STUDY_ID_SUFFIX; // Can't use username because they aren't sub-domain url-safe and url encode adds %
        return $id;
    }
	/**
	 * @param null $causeNameOrId
	 * @param null $effectNameOrId
	 * @param int|null $userId
	 */
	public static function authorizeStudy($causeNameOrId = null, $effectNameOrId = null, int $userId = null){
        if(!$causeNameOrId){$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$effectNameOrId){$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$userId){$userId = UserIdProperty::fromRequestOrAuthenticated();}
        $auth = QMAuth::loggedInUserIsAuthorizedToAccessAllDataForUserId($userId);
        if($auth){return;}
        if(!self::userVariablesAreShared($causeNameOrId, $effectNameOrId, $userId)){
            QMLog::info("Not authorized to view user $userId study");
            throw new UnauthorizedException("Not authorized to view user $userId study");
        }
    }
	/**
	 * @param null $causeNameOrId
	 * @param null $effectNameOrId
	 * @param int|null $userId
	 * @return bool
	 */
	public static function userVariablesAreShared($causeNameOrId = null, $effectNameOrId = null, int $userId = null): bool{
        if(!$causeNameOrId){$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$effectNameOrId){$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$userId){$userId = UserIdProperty::fromRequestOrAuthenticated();}
		$owner = User::findInMemoryOrDB($userId);
		if($owner->getShareAllData()){return true;}
        $cause = QMUserVariable::findByNameIdSynonymOrSpending($userId, $causeNameOrId);
		if(!$cause){return false;}
        $effect = QMUserVariable::findByNameIdSynonymOrSpending($userId, $effectNameOrId);
		if(!$effect){return false;}
        return $cause->shareUserMeasurements && $effect->shareUserMeasurements;
    }
    /**
     * @param string|int|null $causeNameOrId
     * @param string|int|null $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return QMUserStudy
     */
    public static function findOrCreateQMStudy($causeNameOrId = null,
                                            $effectNameOrId = null,
                                            int $userId = null,
                                            string $type = null): QMStudy {
		if(is_array($causeNameOrId) && is_array($effectNameOrId)){
			$causeNameOrId['user_id'] = $userId;
			$effectNameOrId['user_id'] = $userId;
			Measurement::savePostData($causeNameOrId);
			Measurement::savePostData($effectNameOrId);
			$causeNameOrId = $causeNameOrId['variable_name'];
			$effectNameOrId = $effectNameOrId['variable_name'];
		}
        if(!$causeNameOrId){$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$effectNameOrId){$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest(true);}
        if(!$userId){$userId = UserIdProperty::fromRequestOrAuthenticated();}
        if(AppMode::isApiRequest()){
            self::authorizeStudy($causeNameOrId, $effectNameOrId, $userId);
        }
        // Getting user variables now prevents redundant common variables query for id's
        $cause = QMUserVariable::findByNameIdSynonymOrSpending($userId, $causeNameOrId);
        if(!$cause && is_int($causeNameOrId)){$cause = QMUserVariable::getOrCreateById($userId, $causeNameOrId);}
        if(!$cause){$cause = QMUserVariable::findOrCreateByNameOrIdOrSynonym($userId, $causeNameOrId);}
        $effect = QMUserVariable::findByNameIdSynonymOrSpending($userId, $effectNameOrId);
        if(!$effect && is_int($effectNameOrId)){$effect = QMUserVariable::getOrCreateById($userId, $effectNameOrId);}
        if(!$effect){$effect = QMUserVariable::findOrCreateByNameOrIdOrSynonym($userId, $effectNameOrId);}
		$id = static::generateStudyId($cause, $effect, $userId, $type);
	    $s = Study::findInMemoryOrDB($id);
		if(!$s){
			$s = Study::new([
				Study::FIELD_ID => $id,
				Study::FIELD_TYPE => StudyTypeProperty::TYPE_INDIVIDUAL,
				Study::FIELD_CAUSE_VARIABLE_ID => $cause->getVariableIdAttribute(),
				Study::FIELD_EFFECT_VARIABLE_ID => $effect->getVariableIdAttribute(),
			]);
			$s->user_id = $userId;
			$s->id = $id;
			$s->is_public = StudyIsPublicProperty::calculate($s);
			$s->save();
		}
        $qmStudy = $s->getOrSetQMStudy();
		return $qmStudy;
    }
	/**
	 * @param string|int|null $causeNameOrId
	 * @param string|int|null $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|HasCauseAndEffect|QMUserStudy
	 */
	public static function findOrNewQMStudy($causeNameOrId = null,
		$effectNameOrId = null,
		int $userId = null,
		string $type = null): QMStudy {
		if(!$causeNameOrId){$causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest(true);}
		if(!$effectNameOrId){$effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest(true);}
		if(!$userId){$userId = UserIdProperty::fromRequestOrAuthenticated();}
		if(AppMode::isApiRequest()){
			self::authorizeStudy($causeNameOrId, $effectNameOrId, $userId);
		}
		// Getting user variables now prevents redundant common variables query for id's
		$cause = QMUserVariable::findByNameIdSynonymOrSpending($userId, $causeNameOrId);
		if(!$cause && is_int($causeNameOrId)){$cause = QMUserVariable::getOrCreateById($userId, $causeNameOrId);}
		if(!$cause){$cause = QMUserVariable::findOrCreateByNameOrIdOrSynonym($userId, $causeNameOrId);}
		$effect = QMUserVariable::findByNameIdSynonymOrSpending($userId, $effectNameOrId);
		if(!$effect && is_int($effectNameOrId)){$effect = QMUserVariable::getOrCreateById($userId, $effectNameOrId);}
		if(!$effect){$effect = QMUserVariable::findOrCreateByNameOrIdOrSynonym($userId, $effectNameOrId);}
		$id = static::generateStudyId($cause, $effect, $userId, $type);
		$s = Study::findInMemoryOrDB($id);
		if(!$s){
			$s = Study::new([
				Study::FIELD_ID => $id,
				Study::FIELD_TYPE => StudyTypeProperty::TYPE_INDIVIDUAL,
				Study::FIELD_CAUSE_VARIABLE_ID => $cause->getVariableIdAttribute(),
				Study::FIELD_EFFECT_VARIABLE_ID => $effect->getVariableIdAttribute(),
			]);
			$s->user_id = $userId;
			$s->id = $id;
			$s->is_public = StudyIsPublicProperty::calculate($s);
		}
		$qmStudy = $s->getOrSetQMStudy();
		return $qmStudy;
	}
    /**
     * @return array
     */
    public static function getJsonArray(): array{
        $array = parent::getJsonArray();
        $array = QMArr::filter($array, ['type' => StudyTypeProperty::TYPE_INDIVIDUAL]); // For some reason we were saving population studies in UserStudies.json
        return $array;
    }
    /**
     * @param null $causeNameOrId
     * @param null $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return bool|QMCohortStudy|QMPopulationStudy|QMStudy|QMUserStudy
     */
    public static function getStudyIfExists($causeNameOrId = null, $effectNameOrId = null, int $userId = null, string
    $type = null): ?self {
        return parent::getStudyIfExists($causeNameOrId, $effectNameOrId, $userId, StudyTypeProperty::TYPE_INDIVIDUAL);
    }
	/**
	 * @return QMUserVariableRelationship
	 * @throws \App\Exceptions\AnalysisException
	 * @throws \App\Exceptions\NotEnoughDataException
	 * @throws \App\Exceptions\StupidVariableNameException
	 */
    public function createStatistics(): QMVariableRelationship {
        $cause = $this->getOrSetCauseQMVariable();
        $effect = $this->getOrSetEffectQMVariable();
        $c = new QMUserVariableRelationship(null, $cause, $effect);
        $c->analyzeFullyOrQueue("A study is being created");
        return $c;
    }
    /**
     * @param null $causeNameOrId
     * @param null $effectNameOrId
     * @param int|null $userId
     */
    public static function deleteStudyAndCorrelation($causeNameOrId = null, $effectNameOrId = null, int $userId = null){
        $cause = QMCommonVariable::findByNameOrId($causeNameOrId);
		if(!$cause){le( "Could not find cause variable like $causeNameOrId");}
        $effect = QMCommonVariable::findByNameOrId($effectNameOrId);
		if(!$cause){le( "Could not find effect variable like $effectNameOrId");}
        UserVariableRelationship::query()
            ->where(UserVariableRelationship::FIELD_CAUSE_VARIABLE_ID, $cause->getVariableIdAttribute())
            ->where(UserVariableRelationship::FIELD_EFFECT_VARIABLE_ID, $effect->getVariableIdAttribute())
            ->where(UserVariableRelationship::FIELD_USER_ID, $userId)
            ->forceDelete();
        Study::query()
            ->where(Study::FIELD_CAUSE_VARIABLE_ID, $cause->getVariableIdAttribute())
            ->where(Study::FIELD_EFFECT_VARIABLE_ID, $effect->getVariableIdAttribute())
            ->where(Study::FIELD_USER_ID, $userId)
            ->forceDelete();
    }
    /**
     * @return string
     */
    public function getCategoryDescription(): string{
        return UserVariableRelationship::CLASS_DESCRIPTION;
    }
    /**
     * @return string
     */
    public function getTitleWithUserName(): string {
        $user = $this->getQMUser();
        return $this->getTitleAttribute()." for ".$user->getAnonymousDescription();
    }
    public function getIonIcon(): string {
        return IonIcon::person;
    }
    /**
     * @return QMUserVariableRelationship
     * @throws NotEnoughDataException
     */
    public function getQMUserVariableRelationship():QMUserVariableRelationship{
        return $this->getHasCorrelationCoefficient();
    }
    protected static function generateIndexButtons(): array{
        $correlations = UserVariableRelationship::getMikesUpVotedCorrelations();
        $buttons = StudyButton::toButtons($correlations);
        return $buttons;
    }
    public function getStudyType(): string{
        return StudyTypeProperty::TYPE_INDIVIDUAL;
    }
    public function getQMUserVariableRelationshipIfPossible():?QMUserVariableRelationship{
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->getHasCorrelationCoefficientIfSet();
    }
    public function getShowContentView(array $params = []): View{
        try {
            $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
        }
        return view('user-study-content', $this->getShowParams($params));
    }
	public function getShowParams(array $params = []): array{
		$params['study'] = $params['model'] = $this;
		return $params;
	}
    protected function getShowPageView(array $params = []): View{
        try {
            $this->getHasCorrelationCoefficient();
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
        }
        return view('user-study', $this->getShowParams($params));
    }
    protected static function getIndexPageView(): View{
        return view('studies-index', [
            'buttons' => static::generateIndexButtons(),
            'heading' => "Individual Case Studies"
        ]);
    }
    public function getCauseUserVariable(): UserVariable{
        return UserVariable::findOrCreateByNameOrId($this->getUserId(), $this->getCauseVariableId());
    }
    public function getEffectUserVariable(): UserVariable{
        return UserVariable::findOrCreateByNameOrId($this->getUserId(), $this->getEffectVariableId());
    }
    public function getCauseQMUserVariable(): QMUserVariable{
        return QMUserVariable::findOrCreateByNameOrId($this->getUserId(), $this->getCauseVariableId());
    }
    public function getEffectQMUserVariable(): QMUserVariable{
        return QMUserVariable::findOrCreateByNameOrId($this->getUserId(), $this->getEffectVariableId());
    }
	public function getCauseUserVariableId(): int{
		return $this->getCauseUserVariable()->getId();
	}
	public function getEffectUserVariableId(): int{
		return $this->getEffectUserVariable()->getId();
	}
}
