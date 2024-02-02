<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Studies;
use App\Buttons\States\StudyStateButton;
use App\Cards\ParticipantInstructionsQMCard;
use App\Cards\QMCard;
use App\Cards\StudyCard;
use App\Charts\ChartGroup;
use App\Charts\CorrelationCharts\CorrelationChartGroup;
use App\VariableRelationships\QMGlobalVariableRelationship;
use App\VariableRelationships\QMVariableRelationship;
use App\VariableRelationships\QMUserVariableRelationship;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\BadRequestException;
use App\Exceptions\CommonVariableNotFoundException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\InsufficientVarianceException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\NotEnoughMeasurementsForCorrelationException;
use App\Exceptions\NoUserVariableRelationshipsToAggregateException;
use App\Exceptions\SecretException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\UserVariableNotFoundException;
use App\Files\FileFinder;
use App\Files\FileHelper;
use App\Jobs\AnalyzeStudyJob;
use App\Logging\ConsoleLog;
use App\Logging\QMLog;
use App\Mail\EmailsDisabledException;
use App\Mail\StudyJoinEmail;
use App\Mail\TooManyEmailsException;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\Study;
use App\Models\User;
use App\Models\WpPost;
use App\Properties\Base\BaseCauseVariableIdProperty;
use App\Properties\Base\BaseEffectVariableIdProperty;
use App\Properties\Base\BasePostStatusProperty;
use App\Properties\UserVariableRelationship\CorrelationReversePearsonCorrelationCoefficientProperty;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Properties\Study\StudyCauseVariableIdProperty;
use App\Properties\Study\StudyEffectVariableIdProperty;
use App\Properties\Study\StudyIdProperty;
use App\Properties\Study\StudyIsPublicProperty;
use App\Properties\Study\StudyTypeProperty;
use App\Properties\Study\StudyUserIdProperty;
use App\Properties\Study\StudyUserStudyTextProperty;
use App\Properties\User\UserIdProperty;
use App\Reports\StudyReport;
use App\Repos\StudiesRepo;
use App\Slim\Controller\Study\GetStudyController;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\QMUnit;
use App\Slim\Model\Reminders\QMTrackingReminderNotification;
use App\Slim\Model\User\PublicUser;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\Memory;
use App\Storage\S3\S3Images;
use App\Traits\HasButton;
use App\Traits\HasCauseAndEffect;
use App\Traits\HasCorrelationCoefficient;
use App\Traits\HasModel\HasUser;
use App\Traits\HasName;
use App\Traits\QMAnalyzableTrait;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\FontAwesome;
use App\Utils\AppMode;
use App\Variables\QMCommonVariable;
use App\Variables\QMUserVariable;
use App\Variables\QMVariable;
use App\Variables\QMVariableCategory;
use BadMethodCallException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use LogicException;
use SendGrid\Mail\TypeException;
use stdClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;
use Tests\QMBaseTestCase;
/**
 * @mixin Study
 */
abstract class QMStudy extends DBModel {
    use QMAnalyzableTrait;
	use HasCauseAndEffect, HasUser, HasButton, HasName;
    //public const FIELD_STATISTICS = 'statistics';  // FIELD_STATISTICS  is a waste of DB space and might be out of date
	const TYPE = null;
	const USE_STATIC_CHART_IMAGES = false;
    private $causeVariableDisplayName;
    private $effectVariableDisplayName;
    private $report;
    private const USE_MONGO_CACHING = false; // This is too complicated and we can use static assets from S3 instead
    private static $alreadyPosted = [];
    private static $alreadyPostedTestPost;
    protected $correlationFromDatabase;
    protected $pairsOfAveragesForAllUsers;
    protected $userVariableRelationships = [];
    protected $userId;
    public $analysisEndedAt;
    public $analysisRequestedAt;
    public ?string $analysisSettingsModifiedAt = null;
    public $analysisStartedAt;
    public $newestDataAt;
    public $reasonForAnalysis;
    public $userErrorMessage;
    public $analysisParameters;
    public $analyzedAt; // Fixes Undefined property: App\Studies\QMUserStudy::$analyzedAt
    public $causeVariable;
    public $causeVariableId;
    public $causeVariableName;
    public $commentStatus = WpPost::COMMENT_STATUS_OPEN;
    public $effectVariable;
    public $effectVariableId;
    public $effectVariableName;
    public $errorMessage;
    public $fontAwesome = FontAwesome::STUDY;
    public $id;
    public $isPublic;
    public $joined = false;
    public $participantInstructions;
    public $principalInvestigator;
    public $publishedAt;
    public $status;
    public $statistics;
    public $studyCard;
    public $studyCharts;
    /**
     * @var StudyHtml
     */
    public $studyHtml;
    public $studyImages;
    public $studyLinks;
    public $studyPassword;
    public $studySharing;
    public $studyStatus;
    public $studyText;
    public $studyVotes;
    public $title;
    public $trackingReminderNotifications;
    public $trackingReminders;
    public $type;
    public $userStudyText;
    public $userTitle;
    public $userVote;
    public $wpPostId;
    public const DEFAULT_LIMIT = 10;
    public const DEFAULT_PRINCIPAL_INVESTIGATOR_ID = UserIdProperty::USER_ID_MIKE;
    public const FIELD_ANALYSIS_PARAMETERS = 'analysis_parameters';
    public const FIELD_ANALYSIS_REQUESTED_AT = 'analysis_requested_at';
    public const FIELD_ANALYZED_AT = 'analyzed_at';
    public const FIELD_ANALYSIS_STARTED_AT = Study::FIELD_ANALYSIS_STARTED_AT;
    public const FIELD_CAUSE_VARIABLE_ID = 'cause_variable_id';
    public const FIELD_CLIENT_ID = 'client_id';
    public const FIELD_COMMENT_STATUS = 'comment_status';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_DELETED_AT = 'deleted_at';
    public const FIELD_EFFECT_VARIABLE_ID = 'effect_variable_id';
    public const FIELD_ID = 'id';
    public const FIELD_NEWEST_DATA_AT = 'newest_data_at';
    public const FIELD_PUBLISHED_AT = 'published_at';
    public const FIELD_REASON_FOR_ANALYSIS = 'reason_for_analysis';
    public const FIELD_STUDY_IMAGES = 'study_images';
    public const FIELD_STUDY_PASSWORD = 'study_password';
    public const FIELD_STUDY_STATUS = 'study_status';
    public const FIELD_TYPE = 'type';
    public const FIELD_UPDATED_AT = 'updated_at';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_USER_STUDY_TEXT = 'user_study_text';
    public const FIELD_USER_TITLE = 'user_title';
    public const FIELD_WP_POST_ID = 'wp_post_id';
    public const LARAVEL_CLASS = Study::class;
    public const STUDIES_URL = "https://studies.quantimo.do";
    public const STUDY_PDF_DIR = "tmp/study-pdfs";
    public const STATUS_UPDATED = CorrelationStatusProperty::STATUS_UPDATED;
    public const STATUS_WAITING = 'WAITING';
    public const TABLE = 'studies';
    public static $repoName = 'qm-studies';
    public const JEKYLL_PUBLISH_ENABLED = false;
    protected static $requiredFields = [
        self::FIELD_USER_ID,
        self::FIELD_CAUSE_VARIABLE_ID,
        self::FIELD_EFFECT_VARIABLE_ID,
        self::FIELD_ID
    ];
	/**
	 * @var true
	 */
	public bool $success;
	/**
	 * Study constructor.
	 * @param Study|null $study
	 */
    public function __construct(Study $study = null){
		if(!$study){return;}
        $this->setCauseNameOrId($study->cause_variable_id);
        $this->setEffectNameOrId($study->effect_variable_id);
        $this->populateByLaravelModel($study);
        $this->populateDefaultFields();
        $this->addToMemory();
    }
	protected static function getMemoryPrimaryKey(): string{ // Children should all have same primary key
		return (new \ReflectionClass(QMStudy::class))->getShortName();
	}
    /**
     * @return static
     * @noinspection PhpMissingReturnTypeInspection
     */
    public static function fromRequest() {
        $study = Study::findOrNewByRequest();
        $study = $study->getDBModel();
        return $study;
    }
    public function populateDefaultFields(){
        $c = $this->getHasCorrelationCoefficientIfSet();
        $this->getCauseVariableId();
        $this->getEffectVariableId();
        $this->setStudyImages(); // Get general study images before statistics in case we can't calculate
        $this->getStudyLinks();
        if(self::weShouldGenerateFullStudyWithChartsCssAndInstructions()){
            $this->getParticipantInstructions(); // Makes lots of variable requests
        }
        $this->getJoined();
        $this->getStudyText();
        $this->getPrincipalInvestigator();
        $this->studySharing = new StudySharing($this->findHasCorrelationCoefficient() ?? $this);
        if($c){
            $this->setStatistics($c);
        }
        $this->getStudyCard();
        $this->setStudySharing();
        $this->title = $this->getStudyTitle();
        $this->addToMemory();
    }
    /**
     * @return bool
     */
    public function getJoined(): bool{
        return $this->joined;
    }
    /**
     * @return StudyImages
     */
    public function setStudyImages(): StudyImages{
        return $this->studyImages = $this->getStudyImages();
    }
	/**
	 * @return StudyImages
	 */
	public function getStudyImages(): StudyImages{
		return new StudyImages($this->getHasCorrelationCoefficientIfSet(),
			$this);
	}
	/**
	 * @return QMStudy|HasCauseAndEffect
	 */
	public function getOrSetQMStudy(): QMStudy{
		return $this;
	}
    /**
     * @param string|int $variableNameOrId
     * @return QMCommonVariable|QMUserVariable
     */
    public function getOrSetEffectQMVariable($variableNameOrId = null){
        if($this->effectVariable){
            return $this->effectVariable;
        }
        /** @var QMVariableRelationship $c */
        if($c = $this->getHasCorrelationCoefficientIfSet()){
	        return $this->effectVariable = $c->getOrSetEffectQMVariable();
        }
        return $this->setEffectQMVariable($variableNameOrId);
    }
    /**
     * @param string|int $variableNameOrId
     * @return QMCommonVariable|QMUserVariable
     */
    public function getOrSetCauseQMVariable($variableNameOrId = null){
        if($this->causeVariable){
            return $this->causeVariable;
        }
        /** @var QMVariableRelationship $c */
        if($c = $this->getHasCorrelationCoefficientIfSet()){
	        return $this->causeVariable = $c->getOrSetCauseQMVariable();
        }
        return $this->setCauseQMVariable($variableNameOrId);
    }
    /**
     * @param string|int|null $causeVariableNameOrId
     * @return QMCommonVariable|QMUserVariable
     */
    public function setCauseQMVariable($causeVariableNameOrId = null){
        if(!$causeVariableNameOrId){
            $causeVariableNameOrId = $this->getCauseVariableNameOrId();
        }
        $causeVariable = QMCommonVariable::findByNameIdOrSynonym($causeVariableNameOrId);
        if(!$causeVariable){
            throw new CommonVariableNotFoundException("Could not find cause variable with name or id $causeVariableNameOrId");
        }
        $this->causeVariableName = $causeVariable->name;
        return $this->causeVariable = $causeVariable;
    }
    /**
     * @param string|int $effectVariableNameOrId
     * @return QMCommonVariable
     */
    public function setEffectQMVariable($effectVariableNameOrId = null): QMVariable{
        if(!$effectVariableNameOrId){
            $effectVariableNameOrId = $this->getEffectVariableNameOrId();
        }
        $effectVariable = QMCommonVariable::findByNameIdOrSynonym($effectVariableNameOrId);
        if(!$effectVariable){
            throw new NotFoundHttpException("Could not find effect variable with name or id $effectVariableNameOrId");
        }
        $this->effectVariableName = $effectVariable->name;
        return $this->effectVariable = $effectVariable;
    }
    /**
     * @return QMStudy|HasCauseAndEffect
     */
    public static function getUserStudyAndFallbackToPopulationStudy(): QMStudy{
        Memory::setStartTime();
	    $userStudy = QMUserStudy::findOrCreateQMStudy(BaseCauseVariableIdProperty::nameOrIdFromRequest(),
            BaseEffectVariableIdProperty::nameOrIdFromRequest());
	    if(!$userStudy->getErrorMessage()){
            return $userStudy;
        }
        try {
            $populationStudy = QMPopulationStudy::findOrCreateQMStudy(BaseCauseVariableIdProperty::nameOrIdFromRequest(),
                BaseEffectVariableIdProperty::nameOrIdFromRequest(),
	            QMStudy::DEFAULT_PRINCIPAL_INVESTIGATOR_ID,
                StudyTypeProperty::TYPE_POPULATION);
            return $populationStudy;
        } catch (Exception $e) {
            QMLog::error($e->getMessage(), ['exception' => $e]);
        }
        return $userStudy;
    }
    /**
     * @return int
     */
    public function getUserId(): ?int{
        return $this->userId;
    }
	/**
	 * @param $id
	 * @return QMStudy|HasCauseAndEffect
	 * @throws UnauthorizedException
	 */
    public static function find($id): ?DBModel{
        if(empty($id)){le("No id provided to getById");}
        if($fromMemory = static::findInMemory($id)){return $fromMemory;}
        if($l = Study::findInMemoryOrDB($id)){
            if(AppMode::isApiRequest()){
                $l->authorizeView();
            }
            return $l->getDBModel();
        }
        QMRequest::setParam('studyId', $id);
        $study = self::getCohortPopulationOrUserStudy($id);
        return $study;
    }
    /**
     * @return string
     */
    public static function getCohortPopulationOrUserStudyHTML():string {
        QMAuth::getQMUser();
        QMRequest::setMaximumApiRequestTimeLimit(60);
        $studyClientId = StudyIdProperty::fromRequest();
        $type = StudyTypeProperty::fromIdOrUrl($studyClientId);
        if($type === StudyTypeProperty::TYPE_COHORT){
		if(!$studyClientId){le('!$studyClientId');}
            $study = QMCohortStudy::getOrCreateByClientId($studyClientId);
            $html = $study->getHtmlPage();
        }else if($type === StudyTypeProperty::TYPE_INDIVIDUAL){
            $html = QMUserStudy::getOrCreateStudyHTML();
        }else if($type === StudyTypeProperty::TYPE_POPULATION){
            $html = QMPopulationStudy::getOrCreateStudyHTML();
        }else{
            $html = self::getOrCreateUserStudyAndFallbackToPopulationStudyHTML();
        }
        try {
            self::validateStudyHtml($html);
        } catch (\Throwable $e){
            QMLog::error($e->getMessage());
            if(!AppMode::isProductionApiRequest()){
                /** @var LogicException $e */
                throw $e;
            }
        }
        return $html;
    }
    /**
     * @param string $html
     */
    public static function validateStudyHtml(string $html){
        StudyUserStudyTextProperty::validateStudyHtml($html);
    }
    /**
     * @param string|null $id
     * @return QMCohortStudy|QMPopulationStudy|QMStudy|QMUserStudy
     */
    public static function getCohortPopulationOrUserStudy(string $id = null): QMStudy{
        QMAuth::getQMUser();
        QMRequest::setMaximumApiRequestTimeLimit(60);
        $id = $id ?? StudyIdProperty::fromRequest();
        $type = StudyTypeProperty::fromIdOrUrl($id);
        $userIdParam = UserIdProperty::fromQuery(false);
        if(!$userIdParam){$userIdParam = StudyUserIdProperty::fromId($id);}
        $causeId = StudyCauseVariableIdProperty::fromStudyId($id);
        $effectId = StudyEffectVariableIdProperty::fromStudyId($id);
        if($type === StudyTypeProperty::TYPE_COHORT){
		if(!$id){le('!$id');}
            $study = QMCohortStudy::getOrCreateByClientId($id);
        }else if($type === StudyTypeProperty::TYPE_INDIVIDUAL){
            $study = QMUserStudy::findOrCreateQMStudy($causeId, $effectId, $userIdParam);
        }else if($type === StudyTypeProperty::TYPE_POPULATION){
            $study = QMPopulationStudy::findOrCreateQMStudy($causeId, $effectId);
        }elseif($userIdParam){
            $study = QMUserStudy::findOrCreateQMStudy($causeId, $effectId, $userIdParam);
        }else{
            $study = self::getOrCreateUserStudyAndFallbackToPopulationStudy();
        }
        $study->setTrackingInstructionsIfNecessary();
        return $study;
    }
    /**
     * @return null|QMPopulationStudy|QMUserStudy
     */
    public static function getOrCreateUserStudyAndFallbackToPopulationStudy(){
        Memory::setStartTime();
        if(StudyTypeProperty::weShouldGetPopulationStudy()){
            return QMPopulationStudy::findOrCreateQMStudy();
        }
        try {
            if(StudyTypeProperty::loggedInUserHasCauseEffectData()){
                $study = QMUserStudy::findOrCreateQMStudy();
                if(!$study->getErrorMessage()){
                    return $study;
                }
            }
        } catch (UnauthorizedException $e) {
            GetStudyController::$userStudyError = $e->getMessage();
            QMLog::info($e->getMessage());
        }
        $populationStudy = QMPopulationStudy::findOrCreateQMStudy();
	    $populationStudy = self::addUserStudyErrorMessageToPopulationStudyAbstract($populationStudy);
	    return $populationStudy;
    }
    /**
     * @return string
     */
    public static function getOrCreateUserStudyAndFallbackToPopulationStudyHTML(): string {
        if(StudyTypeProperty::weShouldGetPopulationStudy()){
            return QMPopulationStudy::getOrCreateStudyHTML();
        }
        if(StudyTypeProperty::loggedInUserHasCauseEffectData()){
            return QMUserStudy::getOrCreateStudyHTML();
        }else{
            return QMPopulationStudy::getOrCreateStudyHTML();
        }
    }
    /**
     * @param QMPopulationStudy $populationStudy
     * @return mixed
     */
    public static function addUserStudyErrorMessageToPopulationStudyAbstract(QMPopulationStudy $populationStudy): QMPopulationStudy{
        if(!GetStudyController::$userStudyError){
            return $populationStudy;
        }
        $populationStudy->errorMessage = GetStudyController::$userStudyError.
            ".  However, I do have some aggregated data. ".
            $populationStudy->getStudyText()->studyAbstract;
        $populationStudy->setStudyHtml();
        return $populationStudy;
    }
    /**
     * @return string
     */
    private static function getStudyPdfDirectory(): string{
        return FileHelper::absPath(self::STUDY_PDF_DIR);
    }
    /**
     * @param int $getUserId
     * @param string $variableName
     * @return array
     */
    public static function getExistingStudyPDFsForUser(int $getUserId, string $variableName): array{
        $dir = self::getStudyPdfDirectory();
        if(!file_exists($dir)){
            return [];
        }
        $paths = FileFinder::listFilesAndFoldersNonRecursively($dir, true, 'individual-' . $getUserId . '.pdf');
        if(!$paths){
            return [];
        }
        $arr = [];
        foreach($paths as $path){
            $title = str_replace($dir.'/', '', $path);
            $title = QMStr::before('-for-', $title);
            $title = QMStr::titleCaseSlow(str_replace('-', ' ', $title));
            if(stripos($title, $variableName) === false){
                continue;
            }
            $arr[$title] = $path;
        }
        return $arr;
    }
	/**
     * @param bool $allowDbQueries
     * @return string
     */
    public function getCauseVariableDisplayNameWithSuffix(bool $allowDbQueries = true): string{
        if(!$this->causeVariableDisplayName && !$allowDbQueries && !$this->causeVariable && AppMode::isApiRequest()){
            return $this->getCauseVariableName();
        }
        $v = $this->getCauseQMVariable();
	    $name = $v->getDisplayNameWithCategoryOrUnitSuffix();
        return $this->causeVariableDisplayName = $name;
    }
    /**
     * @param bool $allowDbQueries
     * @return string
     */
    public function getEffectVariableDisplayNameWithSuffix(bool $allowDbQueries = true): string{
        if(!$this->effectVariableDisplayName && !$this->effectVariable && !$allowDbQueries && AppMode::isApiRequest()){
            return $this->getEffectVariableName();
        }
        $v = $this->getEffectQMVariable();
	    $name = $v->getDisplayNameWithCategoryOrUnitSuffix();
        return $this->effectVariableDisplayName = $name;
    }
    /**
     * @return QMGlobalVariableRelationship|QMUserVariableRelationship
     * @throws InsufficientVarianceException
     * @throws NotEnoughMeasurementsForCorrelationException
     * @throws NoUserVariableRelationshipsToAggregateException
     */
    abstract public function getCreateOrRecalculateStatistics(): QMVariableRelationship;
    /**
     * @return StudyParticipantInstructions
     */
    public function getParticipantInstructions(): StudyParticipantInstructions{
        if(!$this->participantInstructions){
            $this->setParticipantInstructions();
        }
        $i = StudyParticipantInstructions::instantiateIfNecessary($this->participantInstructions);
        return $i;
    }
    /**
     * @return StudyParticipantInstructions
     */
    public function setParticipantInstructions(): StudyParticipantInstructions{
        $this->participantInstructions = new StudyParticipantInstructions($this->findHasCorrelationCoefficient() ?? $this);
        $this->getStudyHtml()->setParticipantInstructionsHtml();
        return $this->participantInstructions;
    }
    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }
    /**
     * @param string $type
     * @return string
     */
    public function setType(string $type): string{
        return $this->type = $type;
    }
    /**
     * @return StudyText
     */
    public function getStudyText(): StudyText {
        return $this->studyText ?: $this->setStudyText();
    }
    /**
     * @return StudyText
     */
    public function setStudyText(): StudyText{
        return $this->studyText = new StudyText($this->findHasCorrelationCoefficient(), $this);
    }
    /**
     * @return QMPopulationStudy|QMUserStudy|null
     * @throws NoUserVariableRelationshipsToAggregateException
     * @throws NotEnoughDataException
     * @throws UserVariableNotFoundException
     */
    public static function publishUserOrPopulationStudy(){
        $user = QMAuth::getQMUserIfSet();
        $causeNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest();
		if(!$causeNameOrId){le('Please provide causeVariableName');}
        $cause = QMUserVariable::getByNameOrId($user->id, $causeNameOrId);
	    $effectNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest();
		if(!$effectNameOrId){le('Please provide effectVariableName');}
        $effect = QMUserVariable::getByNameOrId($user->id, $effectNameOrId);
	    $cause->setSharing(true);
        $effect->setSharing(true);
        $study = self::getOrCreateUserStudyAndFallbackToPopulationStudy();
        $study->upVote($user->getId());
        $study->setTrackingInstructionsIfNecessary();
        $study->setAttribute(Study::FIELD_IS_PUBLIC, true);
        $sHtml = $study->getStudyHtml();
        $sHtml->getFullStudyHtml();
		if(!$study->getStudyHtml()->studyAbstractHtml){le('!$study->getStudyHtml()->studyAbstractHtml');}
        $study->setPublishedAtAttribute(now_at());
        $uc = $study->getCreateOrRecalculateStatistics();
		if(!$study->getStudyHtml()->studyAbstractHtml){
			$uc = $study->getCreateOrRecalculateStatistics();
			le('!$study->getStudyHtml()->studyAbstractHtml');
		}
		if(!isset($uc->reversePearsonCorrelationCoefficient)){
			CorrelationReversePearsonCorrelationCoefficientProperty::calculate($uc);
		}
	    if(!isset($uc->reversePearsonCorrelationCoefficient)){
		    le("No reversePearsonCorrelationCoefficient");
	    }
		$uc->save();
		try {
			QMGlobalVariableRelationship::getOrCreateByIds($uc->getCauseVariableId(),
			                                         $uc->getEffectVariableId());
		} catch (TooSlowToAnalyzeException $e){
		    QMLog::warning($e->getMessage());
		}
        $sHtml->generateFullStudyHtml();
		if(!$study->getStudyHtml()->studyAbstractHtml){le('!$study->getStudyHtml()->studyAbstractHtml');}
        if($study->highchartsPopulated()){
            $study->postToWordPress();
        } elseif (AppMode::isApiRequest()) {
            $study->queue("Tried to publish but highcharts weren't populated yet");
        } else {
            le("Why are we trying to publish without charts!");
        }
		if(!$study->getStudyHtml()->studyAbstractHtml){le('!$study->getStudyHtml()->studyAbstractHtml');}
        return $study;
    }
    public static function unPublishByRequest(){
        $user = QMAuth::getQMUserIfSet();
        $causeVariableNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest();
        if(!$causeVariableNameOrId){throw new BadRequestException('Please provide causeVariableName');}
        $effectVariableNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest();
        if(!$effectVariableNameOrId){throw new BadRequestException('Please provide effectVariableName');}
        $id = StudyIdProperty::generateStudyId($causeVariableNameOrId, $effectVariableNameOrId, $user->id,
	        static::TYPE);
        $s = self::find($id);
        $s->unPublish();
    }
    public function unPublish(){
        if($this->typeIsIndividual()){
            $effectVariable = $this->getEffectQMVariable();
            $causeVariable = $this->getCauseQMVariable();
            $causeVariable->setSharing(false);
            $effectVariable->setSharing(false);
        }
        $this->unPublishToJekyll();
        //self::deleteById($this->getId(), "user unpublished");
        $this->setAttribute(Study::FIELD_IS_PUBLIC, false);
        $this->updatePostAndStudyStatus(BasePostStatusProperty::STATUS_PRIVATE);
    }
    /**
     * @param bool $push
     * @throws NotEnoughDataException
     */
    public function publishToJekyll(bool $push = false){
        if(!self::JEKYLL_PUBLISH_ENABLED){
            $this->logWarning("Not running ".__FUNCTION__." because there are always git conflict issues");
            return;
        }
        $pageContent = $this->toYaml();
        $pageFilename = $this->getUniqueIndexIdsSlug().'.md';
        $correlation = $this->getCreateOrRecalculateStatistics();
        $postFilename = TimeHelper::YYYYmmddd($correlation->getCreatedAt()).'-'.$pageFilename;
        $this->deleteJekyllMDFiles();
        $pageContent = str_replace([
            "Your ",
            "https://d2u41rntmognc9.cloudfront.net"
        ],
            [
	            StudyText::TEXT_THIS_INDIVIDUAL_S,
                S3Images::S3_CACHED_ORIGIN
            ],
            $pageContent);
        FileHelper::writeByDirectoryAndFilename($this->getJekyllPostsFolder(), $postFilename, $pageContent);
        $this->addToJson();
        if($push){
            StudiesRepo::addAllCommitAndPush("Published ".$this->getNamesSlug());
        }
    }
	/**
	 * @param bool $push
	 * @param string|null $pageFileName
	 */
    public function unPublishToJekyll(bool $push = true, string $pageFileName = null){
        if(!self::JEKYLL_PUBLISH_ENABLED){
            $this->logWarning("Not running ".__FUNCTION__." because there are always git conflict issues");
            return;
        }
        $this->logInfo("UN-PUBLISHING from Jekyll");
        $this->deleteJekyllMDFiles($pageFileName);
        $this->deleteJekyllChartFiles();
        $this->deleteFromJson();
        if($push){
            StudiesRepo::clonePullAndOrUpdateRepo();
            StudiesRepo::stashPullAddCommitAndPush("Deleted ".$this->getNamesSlug());
        }
    }
    /**
     * @param string $filename
     * @return array
     */
    private static function getYamlArrayForOne(string $filename): array{
        $path = StudiesRepo::getAbsPath() . '/_posts';
        $file = $path.'/'.$filename;
        $yamlString = file_get_contents($file);
        $yamlString = QMStr::after('---', $yamlString);
        $yamlString = QMStr::before('---', $yamlString);
        $arr = Yaml::parse($yamlString);
        return $arr;
    }
    /**
     * @return array
     */
    public static function getStudyYamls(): array{
        $path = StudiesRepo::getAbsPath() . '/_posts';
        $files = FileFinder::listFilesAndFoldersNonRecursively($path, false);
        $yamls = [];
        foreach($files as $filename){
            if(stripos($filename, '.md') === false){
                continue;
            }
            $yamls[$filename] = self::getYamlArrayForOne($filename);
        }
        return $yamls;
    }
    /**
     * @param string $filename
     * @param string $reason
     */
    public static function deletePostFile(string $filename, string $reason){
        FileHelper::deleteFile(StudiesRepo::getAbsPath() . '/_posts/' . $filename, $reason);
    }
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 */
    private function toYaml(): string{
        $typeCategory = "Individual Case Studies";
        if(!$this->typeIsIndividual()){
            $typeCategory = "Global Population Studies";
        }
        $img = $this->setStudyImages();
        $string = '---
title: "'.
            $this->getTitleAttribute().
            '"
image:
    path: '.
            $img->getImage().
            '
    #thumbnail: '.
            $img->getGaugeImage().
            '
    thumbnail: '.
            $img->getImage().
            '
    caption: "[QuantiModo](https://quantimo.do)"
excerpt: "'.
            $this->getStudyText()->getTagLine().
            '"
sorting_score: '.
            $this->getSortingScore().
            '
study_type: "'.
            $this->getTypeHumanized().
            '"
id: "'.
            $this->getId().
            '"
cause_variable_name: "'.
            $this->getCauseVariableDisplayNameWithSuffix().
            '"
effect_variable_name: "'.
            $this->getEffectVariableDisplayNameWithSuffix().
            '"
cause_variable_svg: "'.
            $img->getCauseVariableSvgUrl().
            '"
effect_variable_svg: "'.
            $img->getEffectVariableSvgUrl().
            '"
gauge_png: "'.
            $img->getGaugeImage().
            '"
categories:'.
            //    - '.$this->getCauseVariableCategory()->getName().'
            //    - '.$this->getEffectVariableCategory()->getName().'
            '
    - '.
            $typeCategory.
            $this->getJekyllTagsSection().
            //last_modified_at: '.$this->getUpdatedAt().  // Remove this so we don't have a bunch or pointless commits when it's the only change
            '
---
        '.
            $this->getStudyHtml()->getJekyllStudyHtml();
        return $string;
    }
	/**
	 * @return null|QMUserVariableRelationship|QMGlobalVariableRelationship|QMVariableRelationship|HasCorrelationCoefficient
	 */
    abstract public function setHasCorrelationCoefficientFromDatabase();
    /**
     * @return null|QMUserVariableRelationship|QMGlobalVariableRelationship|QMVariableRelationship|HasCorrelationCoefficient
     */
    public function getHasCorrelationCoefficientFromDatabase(){
        $fromDB = $this->correlationFromDatabase;
        if($fromDB === false){return null;}
        if($fromDB !== null){return $fromDB;}
        $fromDB = $this->setHasCorrelationCoefficientFromDatabase();
        if(!$fromDB){
            $this->correlationFromDatabase = false;
            return null;
        }
        $this->setStatistics($fromDB);
        return $this->correlationFromDatabase = $fromDB;
    }
    /**
     * @return bool
     */
    public function weShouldRecalculate(): bool {
        if(QMRequest::recalculateRefreshOrAnalyze()){
            return true;
        }
        if(QMRequest::urlContains('/study/create')){
            return false;
        }
        if(QMRequest::urlContains('/studies')){
            return false;
        }
        $c = $this->getHasCorrelationCoefficientFromDatabase();
        if(!$c){
            return true;
        }
        if($this->weHaveEnoughNewMeasurementsToRecalculate()){
            return true;
        }
        if(!$c->analyzedInLast(30 * 24)){
            return true;
        }
        if($c->internalErrorMessage){
            return true;
        }
        return false;
    }
    /**
     * @param HasCauseAndEffect|QMStudy $study
     * @return bool
     */
    public static function weShouldGenerateFullStudyWithChartsCssAndInstructions($study = null): bool{
        if(QMRequest::urlContains('user_variable_relationships', true)){
            return false;
        }
        if(QMRequest::urlContains('/feed')){
            return false;
        }
        if(QMRequest::urlContains('/studies')){
            return false;
        }
        if(QMRequest::urlContains('/study/join')){
            return false;
        }
        if($study){
            $causeVariable = $study->getCauseQMVariable();
            //if(!isset($causeVariable->userId)){return true;}
            if($causeVariable->allTaggedMeasurementsAreSet()){
                return true;
            }
        }
        if(QMRequest::urlContains('/study/create')){
            return false;
        }
        if(!QMRequest::urlContains('/study')){
            return false;
        }
        return true;
    }
    /**
     * @return bool
     */
    public function isAnalyzing(): bool {
        return $this->getHasCorrelationCoefficientIfSet() && $this->getHasCorrelationCoefficientIfSet()->isAnalyzing();
    }
    /**
     * @return bool
     */
    public function weHaveEnoughNewMeasurementsToRecalculate(): bool {
        // TODO: Add to global variable relationships
        $c = $this->getHasCorrelationCoefficientFromDatabase();
        if(!isset($c->causeNumberOfRawMeasurements)){
            return false;
        }
        $cause = $this->getOrSetCauseQMVariable();
        $numberOfCauseMeasurementsWhenCorrelated = $c->getCauseNumberOfRawMeasurementsWhenCorrelated();
        if($cause->getNumberOfMeasurements() > (1.1 * $numberOfCauseMeasurementsWhenCorrelated)){
            $this->logInfo("Need to recalculate because we have $cause->numberOfMeasurements cause measurements".
                " and had $numberOfCauseMeasurementsWhenCorrelated at last correlation");
            return true;
        }
        $effect = $this->getOrSetEffectQMVariable();
        $numberOfEffectMeasurementsWhenCorrelated = $c->getEffectNumberOfRawMeasurementsWhenCorrelated();
        if($effect->getNumberOfMeasurements() > (1.1 * $numberOfEffectMeasurementsWhenCorrelated)){
            $this->logInfo("Need to recalculate because we have $effect->numberOfMeasurements cause measurements".
                " and had $numberOfEffectMeasurementsWhenCorrelated at last correlation");
            return true;
        }
        return false;
    }
    public function updatePostAndStudyStatus(string $status): void {
        $this->postStatus = $status;
	    $l = $this->l();
        $l->study_status = $status;
        try {
            $l->save();
        } catch (ModelValidationException $e) {
            le($e);
        }
        $this->updatePostStatusOrDeleteIfPrivate($status);
    }
	public function setIsPublicAttribute(bool $isPublic): void{
		$this->isPublic = $isPublic;
		$this->getStudy()->setIsPublicAttribute($isPublic);
	}
    /**
     * @return bool
     */
    public function getIsPublic(): bool {
		if($this->isPublic !== null){
			return $this->isPublic;
		}
        return $this->isPublic = (bool)$this->l()->is_public;  // Default to false for individuals
    }
    /**
     * @return int|string
     */
    public function getCauseVariableNameOrId(){
        if($this->causeVariableId){
            return $this->causeVariableId;
        }
        if($this->causeVariableName){
            return $this->causeVariableName;
        }
        $causeVariableNameOrId = BaseCauseVariableIdProperty::nameOrIdFromRequest(true);
        return $causeVariableNameOrId;
    }
    /**
     * @return int|string
     */
    public function getEffectVariableNameOrId(){
        if($this->effectVariableId){
            return $this->effectVariableId;
        }
        if($this->effectVariableName){
            return $this->effectVariableName;
        }
        $effectVariableNameOrId = BaseEffectVariableIdProperty::nameOrIdFromRequest(true);
        return $effectVariableNameOrId;
    }
    /**
     * @return StudyLinks
     */
    public function getStudyLinks(): StudyLinks{
        if(!$this->studyLinks){
            $this->setStudyLinks();
        }
        /** @var StudyLinks $studyLinks */
        $studyLinks = $this->studyLinks;
        $studyLinks = StudyLinks::instantiateIfNecessary($studyLinks);
        $studyLinks->setHasCauseAndEffect($this);
        return $this->studyLinks = $studyLinks;
    }
    /**
     * @return StudyLinks
     */
    public function setStudyLinks(): StudyLinks{
        $this->studyLinks = new StudyLinks($this->findHasCorrelationCoefficient() ?? $this);
        //unset($this->getHasCorrelationCoefficient()->studyLinks);
        //foreach ($this->studyLinks as $key => $value){unset($this->getHasCorrelationCoefficient()->$key);}  TODO: Uncomment and update tests when clients are migrated
        return $this->studyLinks;
    }
    /**
     * @return StudyHtml
     */
    public function getStudyHtml(): StudyHtml{
        $studyHtml = $this->studyHtml;
        if(!$studyHtml){
            $studyHtml = $this->setStudyHtml();
        }
        $studyHtml = StudyHtml::instantiateIfNecessary($studyHtml);
        $studyHtml->setHasCorrelationsCoefficient($this);
		$studyHtml->getStudyAbstractHtml();
        return $this->studyHtml = $studyHtml;
    }
    /**
     * @return array
     */
    public function getUrlParams(): array{
        $params = $this->getCauseEffectParams();
        $params['id'] = $this->getId();
        $params[Study::FIELD_TYPE] = $this->getType();
	    $params['title'] = $this->getTitleAttribute();
        return $params;
    }
    public function getStudyStateButton(): StudyStateButton {
        return (new StudyStateButton($this->getUrlParams()));
    }

	/**
	 * @param $sh
	 */
	protected function validateFullStudyHtml($sh){
        if(!$this->studyHtml){
            $this->studyHtml = $sh;
        }
		if($this->studyHtml->fullStudyHtml !== $sh->fullStudyHtml){le('$this->studyHtml->fullStudyHtml !== $sh->fullStudyHtml');}
    }
    /**
     * @return StudyHtml
     */
    public function setStudyHtml(): StudyHtml{
        $this->setStudyText();
		//if($this->studyHtml && $this->studyHtml->fullStudyHtml){le('$this->studyHtml
	    // &&$this->studyHtml->fullStudyHtml');}
        $sh = $this->studyHtml = new StudyHtml($this->findHasCorrelationCoefficient() ?? $this);
        $this->validateFullStudyHtml($sh);
        return $sh;
    }
    /**
     * @return string
     */
    public function getErrorMessage(): ?string{
        return $this->errorMessage;
    }
    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage(string $errorMessage){
        $this->errorMessage = $errorMessage;
        QMLog::error($this->errorMessage);
        $this->setStudyText();
    }
    /**
     * @return string
     * DO NOT CHANGE THIS BECAUSE WE BASE THE STUDY SLUGS ON THIS!
     */
    public function getLogMetaDataString(): string {
        $string =
            "Effect of ".$this->getCauseVariableName()." on ".$this->getEffectVariableName()." for ".$this->getType();
        if(!$this->typeIsPopulation()){
            $string .= " ".$this->getUserId();
        }
        return $string.": ";
    }
    /**
     * @return PublicUser
     */
    public function getPrincipalInvestigator(): PublicUser{
        if(ObjectHelper::isMongoOrStdClass($this->principalInvestigator)){
            $this->principalInvestigator = new PublicUser($this->principalInvestigator);
        }
        if($this->principalInvestigator){
            return $this->principalInvestigator;
        }
        if(!is_int($this->userId)){
            return $this->principalInvestigator = QMUser::getDefaultPrincipalInvestigator();
        }
        $user = $this->getUser();
	    return $this->principalInvestigator = $user->getPublicUser();
    }
    /**
     * @throws UnauthorizedException
     */
    public function joinStudy(){
        $user = QMAuth::getQMUser();
        if(!$user){
            throw new UnauthorizedException("No user to join study");
        }
        $reminders = [];
        $cause = $this->getOrSetCauseQMVariable();
        $cuv = $cause->findQMUserVariable($user->id);
        $this->causeVariable = $cuv;
        $uc = $this->statistics = $this->findQMUserVariableRelationship($user->getId());
        if($uc){$this->type = StudyTypeProperty::TYPE_INDIVIDUAL;}
        $euv = $this->getOrSetEffectQMVariable()->findQMUserVariable($user->id);
        $this->effectVariable = $euv;
        if(!$uc){
            if($reminder = $cuv->createReminderAndSetTrackingInstructions()){$reminders[] = $reminder->getDBModel();}
            if($reminder = $euv->createReminderAndSetTrackingInstructions()){$reminders[] = $reminder->getDBModel();}
            $this->trackingReminders = $reminders;
            $this->trackingReminderNotifications =
	            QMTrackingReminderNotification::getPastQMTrackingReminderNotifications([]);
            if(!$user->hasAndroidApp && !$user->hasIosApp){
	            $this->sendJoinStudyEmail($user->l());
            }
        }
        $this->joined = true;
        $this->getStudyHtml()->setBasicHtmlProperties();
    }
	/**
	 * @param User $user
	 * @return StudyJoinEmail
	 */
    private function sendJoinStudyEmail(User $user): ?StudyJoinEmail{
        try {
            $email = new StudyJoinEmail($user, $this->findHasCorrelationCoefficient() ?? $this);
            $email->send();
            return $email;
        } catch (EmailsDisabledException $e) {
            $this->logError('Could not send user study instructions email because '.$e->getMessage());
        } catch (TooManyEmailsException $e) {
            $this->logErrorOrDebugIfTesting('Could not send user study instructions email because '.$e->getMessage());
        } catch (TypeException $e) {
            le($e);
        }
        return null;
    }
	/**
     * @return string
     */
    public function getId(): string{
        $causeVariableId = $this->getCauseVariableId();
        $effectVariableId = $this->getEffectVariableId();
        return $this->id = StudyIdProperty::generateStudyId($causeVariableId, $effectVariableId, $this->getUserId(),
	        static::TYPE);
    }
    /**
     * @param QMGlobalVariableRelationship[]|QMUserVariableRelationship[] $correlations
     * @return QMUserStudy[]|QMPopulationStudy[]
     */
    public static function convertCorrelationsToStudies(array $correlations): array{
        $studies = [];
        foreach($correlations as $correlation){
	        $studies[] = $correlation->findInMemoryOrNewQMStudy();
        }
        return $studies;
    }
    /**
     * @param string|int|null $causeNameOrId
     * @param string|int|null $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return static|null
     */
    public static function getStudyIfExists($causeNameOrId = null,
                                            $effectNameOrId = null,
                                            int $userId = null,
                                            string $type = null): ?self{
		if(!$type){$type = StudyTypeProperty::fromClass(static::class);}
	    $id = StudyIdProperty::generateStudyIdFromApiIfNecessary($causeNameOrId,
		    $effectNameOrId, $userId, $type);
	    $study = Study::findInMemoryOrDB($id);
	    if(!$study){return null;}
		try {
			$study = $study->getOrSetQMStudy();
		} catch (\Throwable $e){
		    QMLog::info($e->getMessage());
			$study = Study::findInMemoryOrDB($id);
			$study->getOrSetQMStudy();
		    le($e);
		}
	    Memory::set($id, $study, static::TABLE);
        return $study;
    }
	/**
	 * @param string|int|null $causeNameOrId
	 * @param string|int|null $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy
	 */
	public static function findQMStudyInMemory($causeNameOrId = null,
		$effectNameOrId = null,
		int $userId = null,
		string $type = null): ?QMStudy{
		$id = StudyIdProperty::generateStudyIdFromApiIfNecessary($causeNameOrId, $effectNameOrId, $userId, $type);
		$study = Study::findInMemory($id);
		if(!$study){return null;}
		return $study->getOrSetQMStudy();
	}
	/**
	 * @param null $causeNameOrId
	 * @param null $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|QMUserStudy|QMPopulationStudy|QMCohortStudy
	 */
    public static function findOrCreateQMStudy($causeNameOrId = null,
                                            $effectNameOrId = null,
                                            int $userId = null,
                                            string $type = null): QMStudy{
        $study = self::getStudyIfExists($causeNameOrId, $effectNameOrId, $userId, $type);
        if(!$study){
            $study = static::createQMStudy($causeNameOrId, $effectNameOrId, $userId, $type ?? static::TYPE);
        }
        return $study;
    }
	/**
	 * @param null $causeNameOrId
	 * @param null $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|QMUserStudy|QMPopulationStudy|QMCohortStudy
	 */
	public static function findOrNewQMStudy($causeNameOrId = null,
		$effectNameOrId = null,
		int $userId = null,
		string $type = null): QMStudy{
		$study = self::getStudyIfExists($causeNameOrId, $effectNameOrId, $userId, $type);
		if(!$study){
			$study = static::newQMStudy($causeNameOrId, $effectNameOrId, $userId, $type);
		}
		return $study;
	}
	/**
	 * @param null $causeNameOrId
	 * @param null $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|QMUserStudy|QMPopulationStudy|QMCohortStudy
	 */
	public static function findInMemoryOrNewQMStudy($causeNameOrId = null,
		$effectNameOrId = null,
		int $userId = null,
		string $type = null): QMStudy{
		$study = self::findQMStudyInMemory($causeNameOrId, $effectNameOrId, $userId, $type);
		if(!$study){
			$study = static::newQMStudy($causeNameOrId, $effectNameOrId, $userId, $type);
		}
		return $study;
	}
    /**
     * @param null $causeNameOrId
     * @param null $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return QMCohortStudy|QMPopulationStudy
     */
    public static function getOrCreateStudyHTML($causeNameOrId = null,
                                            $effectNameOrId = null,
                                            int $userId = null,
                                            string $type = null): string {
	    $study = static::findOrCreateQMStudy($causeNameOrId, $effectNameOrId, $userId, $type);
	    return $study->getHtmlPage();
    }

    /**
     * @return ParticipantInstructionsQMCard|StudyCard
     */
    public function getStudyCard(): QMCard{
        if(!$this->getHasCorrelationCoefficientIfSet()){
            return $this->studyCard = $this->getParticipantInstructions()->getCard();
        }
        return $this->studyCard = new StudyCard($this->findHasCorrelationCoefficient() ?? $this);
    }
    /**
     * @return StudySharing
     */
    public function getStudySharing(): StudySharing{
        return $this->studySharing ?: $this->setStudySharing();
    }
    /**
     * @return StudySharing
     */
    public function setStudySharing(): StudySharing {
        return $this->studySharing = new StudySharing($this->findHasCorrelationCoefficient() ?? $this);
    }
    /**
     * @return QMVariable|QMUserVariable
     */
    public function getEffectQMVariable(): QMVariable {
        $v = $this->effectVariable;
        if(!$v){
            if($s = $this->statistics){$v = $this->effectVariable = $s->getEffectQMVariable();}
        }
        if($v){return $v;}
        return $this->setEffectQMVariable();
    }
    /**
     * @return QMVariable|QMUserVariable
     */
    public function getCauseQMVariable(): QMVariable {
        $v = $this->causeVariable;
        if(!$v){
            /** @var QMVariableRelationship $s */
            if($s = $this->statistics){$v = $this->causeVariable = $s->getCauseQMVariable();}
        }
        if($v){return $v;}
        return $this->setCauseQMVariable();
    }
    /**
     * @return int
     */
    public function getCauseVariableId(): int {
        if(!$this->causeVariableId){
            $this->causeVariableId = $this->getOrSetCauseQMVariable()->getVariableIdAttribute();
        }
        return $this->causeVariableId;
    }
    /**
     * @return int
     */
    public function getEffectVariableId(): int {
        if(!$this->effectVariableId){
            $this->effectVariableId = $this->getOrSetEffectQMVariable()->getVariableIdAttribute();
        }
        return $this->effectVariableId;
    }
    /**
     * @param Study $l
     * @param bool $setDbRow
     * @return QMCohortStudy|QMPopulationStudy|QMUserStudy
     */
    public static function convertRowToModel($l, bool $setDbRow = true){
        if($l->type === StudyTypeProperty::TYPE_POPULATION){
            $study = new QMPopulationStudy($l);
        }elseif($l->type === StudyTypeProperty::TYPE_INDIVIDUAL){
            $study = new QMUserStudy($l);
        }elseif($l->type === StudyTypeProperty::TYPE_COHORT){
            $study = new QMCohortStudy($l);
        }else{
            le("What type of study?");
        }
        return $study;
    }
    /**
     * @param array|object $arrayOrObject
     * @return void
     */
    public function populateFieldsByArrayOrObject(array|object $arrayOrObject): void {
		if($arrayOrObject instanceof BaseModel){
			$this->setLaravelModel($arrayOrObject);
		}
        parent::populateFieldsByArrayOrObject($arrayOrObject);
        if($this->getHasCorrelationCoefficientIfSet()){$this->setStatisticsDependentProperties($this->statistics);}
    }
	/**
	 * @param int|string $causeNameOrId
	 * @param int|string $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|QMUserStudy|QMPopulationStudy|QMCohortStudy
	 */
    public static function createQMStudy($causeNameOrId, $effectNameOrId, int $userId = null, string $type = null): QMStudy{
	    $study = static::newQMStudy($causeNameOrId, $effectNameOrId, $userId, $type);
	    try {
		    $study->save();
	    } catch (\Illuminate\Database\QueryException $e) {
		    $id = $study->getStudyId();
			$study = Study::whereId($id)->withTrashed()->first();
			QMLog::error("Restoring study $id because it was deleted and we tried to recreate it.");
			if($study){
				$study->restore();
			} else {
				le($e);
			}
	    } catch (\Throwable $e) {
		    $study->save();
		    le($e);
	    }
	    return $study->getOrSetQMStudy();
    }
	/**
	 * @param $causeNameOrId
	 * @param $effectNameOrId
	 * @param int|null $userId
	 * @param string|null $type
	 * @return QMStudy|QMUserStudy|QMPopulationStudy|QMCohortStudy
	 */
	public static function newQMStudy($causeNameOrId, $effectNameOrId, int $userId = null, string $type = null):
	QMStudy{
		$id = StudyIdProperty::generateStudyId($causeNameOrId, $effectNameOrId, $userId, $type);
		$study = Study::new([
			Study::FIELD_ID => $id,
			Study::FIELD_CAUSE_VARIABLE_ID => StudyCauseVariableIdProperty::pluckOrDefault($causeNameOrId),
			Study::FIELD_EFFECT_VARIABLE_ID => StudyEffectVariableIdProperty::pluckOrDefault($effectNameOrId),
			Study::FIELD_TYPE => StudyTypeProperty::fromId($id),
		]);
		$study->id = $id;
		$study->user_id = $userId ?? UserIdProperty::USER_ID_POPULATION;
		$study->is_public = StudyIsPublicProperty::calculate($study);
		return $study->getOrSetQMStudy();
	}
    /**
     * @param string|null $reason
     * @return bool
     */
    public function save(string $reason = null): bool {
        if(!isset($this->userId)){
            $this->userId = QMStudy::DEFAULT_PRINCIPAL_INVESTIGATOR_ID;
        }
        try {
            $result = parent::save();
        } catch (ModelValidationException $e) {
            le($e);
        }
        if($result === false){
            $qb = self::writable()
                ->whereLike('id', '%cohort%')
                ->whereLike('type', '%population%');
            $rows = $qb->getArray();
            if(count($rows)){
                QMLog::error(count($rows) . " have id cohort and type population.  Deleting now..");
                $qb->delete();
                try {
                    $result = parent::save();
                } catch (ModelValidationException $e) {
                    le($e);
                }
            }
        }
        if(!$result){
	        $this->logError("insertOrUpdate result is false");
        }
        return $result;
    }
    /**
     * @param int $userId
     * @param int|null $limit
     * @param int|null $offset
     * @return QMStudy[]
     */
    public static function getCreatedBy(int $userId, int $limit = null, int $offset = null): array{
        return self::qmWhere(self::FIELD_USER_ID, $userId, $limit, $offset);
    }
    /**
     * @param $causeNameOrIdOrId
     */
    private function setCauseNameOrId($causeNameOrIdOrId){
        if(is_int($causeNameOrIdOrId)){
            $this->causeVariableId = $causeNameOrIdOrId;
        }else{
            $this->causeVariableName = $causeNameOrIdOrId;
        }
    }
    /**
     * @param $effectNameOrIdOrId
     */
    private function setEffectNameOrId($effectNameOrIdOrId){
        if(is_int($effectNameOrIdOrId)){
            $this->effectVariableId = $effectNameOrIdOrId;
        }else{
            $this->effectVariableName = $effectNameOrIdOrId;
        }
    }
    /**
     * @return StudyReport
     */
    public function getReport(): StudyReport{
        if($this->report){
            return $this->report;
        }
        return $this->report = new StudyReport($this->l());
    }
    /**
     * @return QMVariableCategory
     */
    public function getCauseQMVariableCategory(): QMVariableCategory {
        return $this->getOrSetCauseQMVariable()->getQMVariableCategory();
    }
    /**
     * @return QMUnit
     */
    public function getCauseVariableCommonUnit(): QMUnit {
        return $this->getOrSetCauseQMVariable()->getCommonUnit();
    }
    /**
     * @return QMVariableCategory
     */
    public function getEffectQMVariableCategory(): QMVariableCategory {
        return $this->getOrSetEffectQMVariable()->getQMVariableCategory();
    }
    /**
     * @return string
     */
    public function getPublishedAt(): ?string{
        return $this->publishedAt = $this->getStudy()->published_at;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     */
    public function setSortingScore(): float{
        return $this->sortingScore = $this->getHasCorrelationCoefficient()->getQmScore();
    }
    /**
     * @throws NotEnoughDataException
     */
    public function getOrSetHighchartConfigs(): void{
        $studyCharts = $this->getOrSetCharts();
        $studyCharts->getOrSetHighchartConfigs();
        // This is too big!  this->getOrSetCauseQMVariable()->getChartGroup()->getOrSetHighchartConfigs();
        // Just Link to variable page!  $this->getOrSetEffectQMVariable()->getChartGroup()->getOrSetHighchartConfigs();
    }
    /**
     * @throws ModelValidationException
     */
    public function validate(): void {
        $c = $this->getHasCorrelationCoefficientIfSet();
        if($c && $c->id === 1398 && $c->effectVariableId === 1398){
            le("not valid because c->id === 1398 && c->effectVariableId === 1398");
        }
		if($c){
			$c->getPredictivePearsonCorrelationCoefficient();
		}
        if ($this->highchartsPopulated()) {
            $l = $this->l();
            $text = $this->getStudyText();
            $title = $text->getStudyTitle();
            if (str_contains($title, '?')) {
                $l->throwModelValidationException("title", $title, "Why is title $title even though highcharts are populated?");
            }
            $html = $this->getStudyHtml();
            try {
                self::validateStudyHtml($html);
            } catch (SecretException $e) {
                $l = $this->l();
                $l->addValidationError('studyHtml', $html, $e->getMessage());
                throw new ModelValidationException($l);
            }
            $header = $this->getTitleGaugesTagLineHeader(true, true);
            if (stripos($header, 'puzzled') !== false) {
                $url = $this->uploadStudyHtmlAndGetUrl(\App\Utils\AppMode::getCurrentTestName());
                QMLog::logicExceptionIfNotProductionApiRequest("Why does header contain puzzled robot if we
                    have charts? See study at
                    $url.");
            }
        }
    }
    public function getChartsIfSet(): ?ChartGroup {
        $charts = $this->studyCharts;
        if($charts){return $charts;}
        $statistics = $this->getHasCorrelationCoefficientIfSet();
        if(!$statistics){return null;}
        return $this->studyCharts = $statistics->getOrSetCharts();
    }
    /**
     * @return bool
     */
    public function highchartsPopulated(): bool {
        $charts = $this->getChartsIfSet();
        if(!$charts){return false;}
        if(!$charts->highchartsPopulated()){return false;}
        /** @var QMVariable $cause */
        $cause = $this->causeVariable;
        if(!$cause){return false;}
        if($cause->charts){ // Need to make sure it's set and then get instantiated version from getCharts()
            $causeCharts = $cause->getChartGroup();
        } else {
            return false;
        }
        if(!$causeCharts->highchartsPopulated()){return false;}
        /** @var QMVariable $effect */
        $effect = $this->effectVariable;
        if(!$effect){return false;}
        if($effect->charts){ // Need to make sure it's set and then get instantiated version from getCharts()
            $effectCharts = $effect->getChartGroup();
        } else {
            return false;
        }
        if(!$effectCharts->highchartsPopulated()){return false;}
        return true;
    }
    /**
     * @param QMVariableRelationship $statistics
     * @return QMVariableRelationship
     */
    public function setStatistics(QMVariableRelationship $statistics): QMVariableRelationship{
        if($existing = $this->getHasCorrelationCoefficientIfSet()){
            /** @var QMVariableRelationship $existing */
            if($existing->getUpdatedAt() === $statistics->getUpdatedAt() && // No need to setStatisticsDependentProperties again
                $existing->getCorrelationCoefficient() === $statistics->getCorrelationCoefficient()){
                return $this->statistics = $statistics; // Replaces the BSON if necessary
            }
        }
        $this->statistics = $statistics;
        $this->setStatisticsDependentProperties($statistics);
        return $this->statistics;
    }
    /**
     * @return float
     * @throws NotEnoughDataException
     */
    public function getSortingScore(): float{
        return $this->sortingScore = $this->getHasCorrelationCoefficient()->getQmScore();
    }
    /**
     * @return stdClass
     */
    public function getSearchItem(): stdClass{
        $item = parent::getSearchItem();
        $item = ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($this->setStudyImages(), $item);
        $item = ObjectHelper::copyPublicPropertiesFromOneObjectToAnother($this->getStudyLinks(), $item);
        $item->description = $this->getStudyText()->getTagLine();
        $item->html = $this->getTitleGaugesTagLineHeader(true, true);
        if(isset($item->publishedAt)){
            $item->publishedAt = TimeHelper::YYYYmmddd($item->publishedAt);
        }
        unset($item->fromMongo);
        return $item;
    }
    /**
     * @param $causeNameOrId
     * @param $effectNameOrId
     * @param int|null $userId
     * @param string|null $type
     * @return bool
     */
    public static function alreadyPublishedToGithub($causeNameOrId,
                                                    $effectNameOrId,
                                                    int $userId = null,
                                                    string $type = null): bool{
        $id = StudyIdProperty::generateStudyId($causeNameOrId, $effectNameOrId, $userId, $type);
        $studies = self::getJsonArray();
        foreach($studies as $study){
            if($study->id === $id){
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    protected static function getPathToJson(): string{
        $path = 'tmp/'.self::$repoName.'/data';
        return $path;
    }
    /**
     * @return string|null
     */
    public function getAnalysisSettingsModifiedAt(): ?string{
        $cause = $this->getCauseQMVariable()->getAnalysisSettingsModifiedAt();
        $effect = $this->getEffectQMVariable()->getAnalysisSettingsModifiedAt();
        if($cause > $effect){
            return $cause;
        }
        return $effect;
    }
    /**
     * @param QMVariableRelationship|HasCorrelationCoefficient $statistics
     */
    private function setStatisticsDependentProperties($statistics): void{
        $this->setStudyText();
        $this->setStudyImages();
        $this->setStudyHtml();
        $this->getStudyCard();
        $this->studyVotes = new StudyVotes($statistics);
    }
    public function setHtmlWithChartsIfPossible(){
        $h = $this->getStudyHtml();
        try {
            $h->getFullStudyHtml();
            if(!$this->getStudyHtml()->fullStudyHtml){
                $h->getFullStudyHtml();
                $this->getStudyHtml();
            }
            $this->getOrSetHighchartConfigs();
		if(!$this->getStudyHtml()->fullStudyHtml){
			le('!$this->getStudyHtml()->fullStudyHtml');}
        } catch (NotEnoughDataException $e) {
            $this->logError($e->getMessage());
			if(!$this->getStudyHtml()->fullStudyHtml){le('!$this->getStudyHtml()->fullStudyHtml');}
        }
		if(!$this->getStudyHtml()->fullStudyHtml){le('!$this->getStudyHtml()->fullStudyHtml');}
    }
    /**
     * @return QMGlobalVariableRelationship|null
     * @throws NotEnoughDataException
     */
    protected function getOrCreateGlobalVariableRelationship(): ?QMGlobalVariableRelationship {
        if($this->statistics instanceof NotEnoughDataException){
            throw $this->statistics;
        }
        $causeVariable = $this->getOrSetCauseQMVariable();
        $effectVariable = $this->getOrSetEffectQMVariable();
        $fromDb = $this->getHasCorrelationCoefficientFromDatabase();
        if(!$fromDb){
            try {
                $this->createStatistics();
            } catch (NotEnoughDataException $e) {
                $this->statistics = false;
                return null;
            }
        }
        $c = QMGlobalVariableRelationship::getOrCreateByIds($causeVariable->getVariableIdAttribute(),
            $effectVariable->getVariableIdAttribute());
        return $c;
    }
	/**
	 * @return QMGlobalVariableRelationship
	 * @throws DuplicateFailedAnalysisException
	 * @throws ModelValidationException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableNameException
	 * @throws TooSlowToAnalyzeException
	 */
    protected function createGlobalVariableRelationship(): QMGlobalVariableRelationship {
        if($this->statistics instanceof NotEnoughDataException){
            throw $this->statistics;
        }
        $c = new QMGlobalVariableRelationship(null, $this->getCauseVariableId(), $this->getEffectVariableId());
        try {
            $c->analyzeFullyAndSave(__FUNCTION__);
        } catch (AlreadyAnalyzedException | AlreadyAnalyzingException $e) {
            return $this->statistics = $c;
        } catch (NotEnoughDataException $e) {
            $this->statistics = $e;
            throw $e;
        }
        return $this->statistics = $c;
    }
    /**
     * @return array
     */
    public function getSpreadsheetRows(): array{
        $rows = $this->getCauseQMVariable()->getSpreadsheetRows();
        $rows = array_merge($rows, $this->getEffectQMVariable()->getSpreadsheetRows());
        return $rows;
    }
    /**
     * @return string
     */
    public function getTitleAttribute(): string{
        return $this->title = $this->getStudyTitle(false); // Arrows probably bad for SEO and may cause other problems
    }
    public function getChangeFromBaseline():?float{
        $c = $this->getHasCorrelationCoefficientIfSet();
        if(!$c){
            return null;
        }
        return $c->getChangeFromBaseline();
    }
    /**
     * @param string $reason
     * @throws NotEnoughDataException
     * @throws TooSlowToAnalyzeException
     */
    public function analyzeFullyIfNecessary(string $reason){
        $c = $this->getHasCorrelationCoefficient();
        $c->analyzeFullyIfNecessary($reason);
    }
    /**
     * @inheritDoc
     */
    public function analyzeFully(string $reason){
        $this->getHasCorrelationCoefficient()->analyzeFully($reason);
    }
	/**
	 * @inheritDoc
	 * @throws NotEnoughDataException
	 */
    public function getNewestDataAt(): ?string{
        return $this->getHasCorrelationCoefficient()->getNewestDataAt();
    }
	/**
	 * @inheritDoc
	 * @throws NotEnoughDataException
	 */
    public function getSourceObjects(): array{
        return $this->getHasCorrelationCoefficient()->getSourceObjects();
    }
    /**
     * @inheritDoc
     */
    public function getPHPUnitTestUrl(): string{
        if($this->typeIsIndividual()){
            return QMUserVariableRelationship::generatePHPUnitTestUrlForAnalyze($this->getCauseQMVariable(),
                $this->getEffectQMVariable());
        }
        return QMGlobalVariableRelationship::generatePHPUnitTestUrlForAnalyze($this->getCauseVariable(),
            $this->getEffectVariable());
    }
    /**
     * @return Study
     */
    public function l(): Study{
	    /** @noinspection PhpIncompatibleReturnTypeInspection */
	    return parent::l();
    }
	/**
	 * @inheritDoc
	 * @param string $reason
	 * @throws AlreadyAnalyzingException
	 * @throws NotEnoughDataException
	 */
    public function analyzePartially(string $reason){
        $this->logInfo(__METHOD__);
        $this->getHasCorrelationCoefficient()->analyzePartially($reason); // TODO:  Implement analyzePartially
    }
	/**
	 * @return QMVariableRelationship|HasCorrelationCoefficient|null
	 */
	public function findHasCorrelationCoefficient(){
		$s = $this->statistics;
		if($s === false){return null;}
		if($s instanceof NotEnoughDataException){
			return null;
		}
		if ($this->statistics) {
			return $this->statistics;
		}
		$c = $this->getHasCorrelationCoefficientFromDatabase();
		if(!$c){
            $this->statistics = false;
			return null;
        }
		return $this->setStatistics($c);
	}
    /**
     * @return HasCorrelationCoefficient
     * @throws NotEnoughDataException
     */
    public function getHasCorrelationCoefficient() {
        $s = $this->statistics;
        if($s instanceof NotEnoughDataException){
            throw $s;
        }
        if ($this->statistics) {
            return $this->statistics;
        }
        $c = $this->getHasCorrelationCoefficientFromDatabase();
        if (!$c) {
            try {
                $c = $this->createStatistics();
            } catch (NotEnoughDataException $e) {
                $this->statistics = $e;
                throw $e;
            }
        }
        return $this->setStatistics($c);
    }
    /**
     * @return QMVariableRelationship
     * @throws NotEnoughDataException
     */
    abstract public function createStatistics(): QMVariableRelationship;
    public function getStudyStatus(): string {
        if($this->studyStatus){
            return $this->studyStatus;
        }
        return $this->studyStatus = $this->getPostStatus();
    }
    public function removeRecursion(){
		$this->removeNonPublicProperties();
        foreach($this as $key => $value){
			if(!$value){continue;}
            if((is_object($value) || is_array($value))){
	            if(method_exists($value, 'removeRecursion')){
		            $value->removeRecursion();
	            }
            }
        }
        parent::removeRecursion();

    }
    /**
     * @return CorrelationChartGroup
     * @throws NotEnoughDataException
     */
    public function getOrSetCharts(): ChartGroup {
        if($this->studyCharts){
            return $this->studyCharts;
        }
        $c = $this->getHasCorrelationCoefficient();
        if($studyCharts = $this->studyCharts){
            /** @var ChartGroup $studyCharts */
            $studyCharts->setSourceObject($c);
            return $studyCharts;
        }
        $charts = $c->getOrSetCharts();
        return $this->studyCharts = $charts;
    }
    /**
     * @return QMVariableRelationship|null|HasCorrelationCoefficient
     */
    public function getHasCorrelationCoefficientIfSet(){
        if($this->statistics instanceof NotEnoughDataException){
            return null;
        }
        return $this->statistics;
    }
	/**
	 * @return mixed
	 */
	public function cleanup(){
        throw new LogicException(__FUNCTION__." not implemented for ".static::class);
    }
    public function unsetLargeStatisticsProperties(){
        if($c = $this->getHasCorrelationCoefficientIfSet()){
            unset($c->studyText);
            unset($c->charts);
            unset($c->causeVariableCharts);
            unset($c->effectVariableCharts);
            unset($c->effectDataSource);
            unset($c->causeDataSource);
        }
    }
    public function prepareResponse(): QMStudy{
        $this->setHtmlWithChartsIfPossible();
		// Let's just look at the variable pages separately because the response will be too big
	    // When you consider it's got to get all the other user_variable_relationships, etc.
//		$this->getCauseQMVariable()->getOrSetHighchartConfigs();
//	    $this->getEffectQMVariable()->getOrSetHighchartConfigs();
        $this->unsetLargeStatisticsProperties();
		if(!$this->getStudyHtml()->fullStudyHtml){le('!$this->getStudyHtml()->fullStudyHtml');}
        try {
            $this->validate();
        } catch (ModelValidationException $e) {
            le($e);
        }
        $this->success = true;
		$this->status = "OK";
        return $this;
    }
    public function getCauseVariableCategoryId(): int{
        return $this->getCauseVariable()->getVariableCategoryId();
    }
    public function getEffectVariableCategoryId(): int{
        return $this->getEffectVariable()->getVariableCategoryId();
    }
    /**
     * @return int
     */
    public function getMaxAgeInSeconds(): int{
        try {
            $statistics = $this->getHasCorrelationCoefficient();
            return $statistics->getMaxAgeInSeconds();
        } catch (NotEnoughDataException $e) {
        }
        $cause = $this->getCauseQMVariable()->getMaxAgeInSeconds();
        $effect = $this->getEffectQMVariable()->getMaxAgeInSeconds();
        $maxAge = ($effect < $cause) ? $effect : $cause;
        return $maxAge;
    }
    public function needToAnalyze(): bool{
        $c = $this->getHasCorrelationCoefficientFromDatabase();
        if(!$c){
            try {
                $c = $this->getHasCorrelationCoefficient();
            } catch (NotEnoughDataException $e) {
                return false;
            }
        }
        $needToAnalyze = $c->needToAnalyze();
        return $needToAnalyze;
    }
    public function hasIds(): bool{
        return isset($this->causeVariableId);
    }
    public function findUserVariableRelationship(int $userId): ?UserVariableRelationship {
        return UserVariableRelationship::findByIds($userId, $this->getCauseVariableId(),
            $this->getEffectVariableId());
    }
    public function findQMUserVariableRelationship(int $userId): ?QMUserVariableRelationship {
        return QMUserVariableRelationship::findByIds($userId, $this->getCauseVariableId(),
            $this->getEffectVariableId());
    }
	/**
	 * @param string $reason
	 * @return PendingDispatch
	 */
	public function queue(string $reason): ?PendingDispatch{
		if(AnalyzeStudyJob::alreadyQueued($this->l())){return null;}
		$this->saveAnalysisStatus($reason);
		return AnalyzeStudyJob::queueModel($this->l(), $reason);
	}
	/**
	 * @return Study
	 */
	public function getStudy(): Study {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $this->l();
	}
	public function getInvalidSourceData(): array{
		return $this->getStudy()->getInvalidSourceData();
	}
	public function generatePostContent(): string {
		return $this->getStudy()->generatePostContent();
	}
	public function getCategoryName(): string {
		return $this->getStudy()->getCategoryName();
	}
	public function getParentCategoryName(): ?string {
		return $this->getStudy()->getParentCategoryName();
	}
	/**
	 * @return Study|Builder
	 */
	public static function wherePostable(){
		return Study::wherePostable();
	}
	/**
	 * @throws NotEnoughDataException
	 */
	public function exceptionIfWeShouldNotPost(): void{
		$this->getStudy()->exceptionIfWeShouldNotPost();
	}
	public function getNameAttribute(): string{
		return $this->getStudy()->getNameAttribute();
	}
	public function getIcon(): string{
		return $this->getStudy()->getIcon();
	}
	public function getKeyWords(): array{
		return $this->getStudy()->getKeyWords();
	}
	public function getInterestingRelationshipButtons(): array {
		return $this->getStudy()->getInterestingRelationshipButtons();
	}
	/**
	 * @return string
	 */
	public function getImage(): string{
		if(!$this->hasIds()){
			return Study::DEFAULT_IMAGE;
		}
		$si = $this->getStudyImages();
		$img = $si->getImage();
		return $img;
	}
	public function getSubtitleAttribute(): string{
		return $this->getStudy()->getSubtitleAttribute();
	}
	/**
	 * @param array $params
	 * @return string
	 */
	public function getUrl(array $params = []): string{
		return StudyLinks::generateStudyLinkStatic($this->getStudyId(), $params);
	}
	public function getPostNameSlug(): string{return $this->getStudy()->getPostNameSlug();}
	/**
	 * @return ParticipantInstructionsQMCard|QMCard|StudyCard
	 */
	public function setCard(): QMCard {
		return $this->card = $this->getStudyCard();
	}
	/**
	 * @throws NotEnoughDataException
	 */
	public function getOnsetDelay(): int{
		return $this->getHasCorrelationCoefficient()->getOnsetDelay();
	}
	/**
	 * @throws NotEnoughDataException
	 */
	public function getDurationOfAction(): int{
		return $this->getHasCorrelationCoefficient()->getDurationOfAction();
	}
	/**
	 * @param int|null $precision
	 * @return float
	 * @throws NotEnoughDataException
	 */
	public function getCorrelationCoefficient(int $precision = null): ?float{
		return $this->getHasCorrelationCoefficient()->getCorrelationCoefficient($precision);
	}
	/**
	 * @return string
	 * @throws NotEnoughDataException
	 */
	public function getConfidenceLevel(): string{
		return $this->getHasCorrelationCoefficient()->getConfidenceLevel();
	}
	/**
	 * Handle dynamic method calls into the model.
	 * @param string $method
	 * @param array $parameters
	 * @return mixed
	 * @throws NotEnoughDataException
	 */
	public function __call(string $method, array $parameters){
		$l = $this->l();
		if(method_exists($l, $method)){
			return $this->forwardCallTo($l, $method, $parameters);
		}else {
			try {
				$c = $this->getHasCorrelationCoefficient();
				if(method_exists($c, $method)) {
					return $this->forwardCallTo($c, $method, $parameters);
				}
			} catch (NotEnoughDataException $e) {
                ConsoleLog::info("Could not getHasCorrelationCoefficient because: " . $e->getMessage());
				throw $e;
            }
			throw new BadMethodCallException(sprintf(
				'Call to undefined method %s::%s()', static::class, $method
			));
		}
	}
	public function getShowContent(bool $inlineJs = false): string{
		$studyHtml = $this->getStudyHtml();
		return $studyHtml->getShowContent();
	}
}
