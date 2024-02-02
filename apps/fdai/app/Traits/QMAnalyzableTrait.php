<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\DevOps\XDebug;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\AnalysisException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ExceptionHandler;
use App\Exceptions\InsufficientMemoryException;
use App\Exceptions\InvalidAttributeException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Formulas\SQLView;
use App\Jobs\AnalyzeJob;
use App\Logging\ConsoleLog;
use App\Logging\GlobalLogMeta;
use App\Logging\QMClockwork;
use App\Logging\QMIgnition;
use App\Logging\QMLog;
use App\Logging\SolutionButton;
use App\Models\GlobalVariableRelationship;
use App\Models\BaseModel;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Models\UserVariable;
use App\Models\Variable;
use App\Notifications\AnalysisCompletedNotification;
use App\PhpUnitJobs\JobTestCase;
use App\Properties\Base\BaseAnalysisStartedAtProperty;
use App\Properties\UserVariableRelationship\CorrelationStatusProperty;
use App\Properties\User\UserIdProperty;
use App\Properties\User\UserStatusProperty;
use App\Properties\UserVariable\UserVariableStatusProperty;
use App\Slim\Model\DBModel;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\Model\User\QMUser;
use App\Slim\View\Request\QMRequest;
use App\Storage\DB\QMQB;
use App\Storage\DB\Writable;
use App\Storage\Firebase\FirebaseGlobalPermanent;
use App\Storage\Memory;
use App\Storage\QMFileCache;
use App\Traits\PropertyTraits\IsCalculated;
use App\Types\ObjectHelper;
use App\Types\QMStr;
use App\Types\TimeHelper;
use App\UI\HtmlHelper;
use App\Utils\AppMode;
use App\Utils\Compare;
use App\Utils\UrlHelper;
use App\Variables\CommonVariables\PhysicalActivityCommonVariables\DailyStepCountCommonVariable;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;
/*
* @method \App\Models\BaseModel l()
*/
trait QMAnalyzableTrait {
	use LoggerTrait, TestableTrait, HasExceptions, HasErrors;
	private static $ANALYZE_STUCK_SEPARATELY = false; // Let's not ANALYZE STUCK separately because the query destroys the DB CPU
	protected $alreadyAnalyzed;
	protected $interestingRelationshipCounts;
	protected $internalErrorMessage;
	protected $invalidSourceData = [];
	protected $products;
	protected $valuesBeforeAnalysis;
	public $errorCards;
	public static $sqlCalculatedFields = [];
	public static function analyzeGloballyIfNecessary(): void{
		$rand = rand(1, 100);
		if($rand < 90){
			return; // TODO: see why QMCache doesn't work
		}
		$key = "analyzeGlobal:" . static::class;
		$last = FirebaseGlobalPermanent::get($key);
		if(!$last){
			/** @noinspection PhpUnhandledExceptionInspection */
			FirebaseGlobalPermanent::set($key, time());
			$lastCheck = FirebaseGlobalPermanent::get($key);
			if(!$lastCheck){
				le("Cache not working!");
			}
		}
		ConsoleLog::info("Last analyzeGlobal was " . TimeHelper::timeSinceHumanString($last) .
			" for " . static::class);
		if(!$last || (time() - $last) > 86400){
			ConsoleLog::info("Analyzing Globally...");
			QMFileCache::set($key, time());
			static::analyzeGlobal();
		} else{
			ConsoleLog::info("Skipping analyzeGlobal...");
		}
	}
	/**
	 * @return array
	 */
	public static function getSqlCalculatedFields(): array{
		return static::$sqlCalculatedFields;
	}
	/**
	 * @param $qb
	 * @return QMQB
	 */
	public static function excludeUnAnalyzableUsers(QMQB $qb): QMQB{
		$t = static::TABLE;
		if(defined(static::class . '::FIELD_USER_ID')){
			$qb->whereNotIn($t . '.' . static::FIELD_USER_ID, UserIdProperty::getTestSystemAndDeletedUserIds());
		}
		return $qb;
	}
	/**
	 * @param string $reason
	 * @throws AlreadyAnalyzingException
	 * @throws AlreadyAnalyzedException
	 * @throws TooSlowToAnalyzeException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableNameException
	 * @throws AnalysisException
	 */
	abstract public function analyzeFully(string $reason);
	public function getValuesBeforeAnalysis(): array{
		return $this->valuesBeforeAnalysis;
	}
	/**
	 * @return array
	 */
	public function getAnalysisResults(): array{
		$beforeArr = $this->getValuesBeforeAnalysis();
		/** @var BaseModel $l */
		$l = $this->l();
		$l->fresh();
		$afterArr = $l->attributesToArray();
		$comparison = [];
		foreach($beforeArr as $key => $before){
			$after = $afterArr[$key];
			if(is_numeric($before) && is_numeric($after)){
				if(!$before){
					$diff = 100;
				} else{
					$diff = ($after - $before) / $before * 100;
				}
			} elseif(is_string($before) && is_string($after)){
				$diff = similar_text(json_encode($before), json_encode($after));
			} else{
				$diff = similar_text(json_encode($before), json_encode($after));
			}
			$comparison[$key] = ['before' => $before, 'after', 'change' => $diff];
		}
		return $comparison;
	}
	/**
	 * @param string $reason
	 * @throws AnalysisException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableNameException
	 */
	public function analyzeFullyOrQueue(string $reason){
		$this->setReasonForAnalysis($reason);
		try {
			$this->analyzeFully($reason);
		} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
			le($e);
		} catch (TooSlowToAnalyzeException $e) {
			$this->queue("Too slow so had to queue analysis. Analyzing because " . $reason);
		} catch (InsufficientMemoryException $e) {
			$this->queue("Not enough memory so had to queue analysis. Analyzing because " . $reason);
		}
	}
	/**
	 * @param string $reason
	 * @throws AlreadyAnalyzingException
	 */
	abstract public function analyzePartially(string $reason);
	/**
	 * @param string $message
	 */
	public function slack(string $message){
		JobTestCase::slack("$this: \n" . $message);
	}
	/**
	 * @param string $reason
	 * @param string $status
	 * @return bool
	 */
	public function saveAnalysisStatus(string $reason,
		string $status = UserVariableStatusProperty::STATUS_WAITING): bool{
		$this->setAnalysisRequestedAt(now_at());
		$this->setReasonForAnalysis($reason);
		$this->setAlreadyAnalyzed(false);
		$this->status = $status;
		$this->logInfo("Settings status $status because $reason");
		$result = $this->save();
		return $result;
	}
	/**
	 * @param mixed $analysisStartedAt
	 */
	public function setAnalysisStartedAt(string $analysisStartedAt): void{
		$this->setAttribute(BaseAnalysisStartedAtProperty::NAME, $analysisStartedAt);
		$this->assertDBModelValMatchesLaravelValue(BaseAnalysisStartedAtProperty::NAME);
	}
	/**
	 * @param bool $alreadyAnalyzed
	 */
	public function setAlreadyAnalyzed(bool $alreadyAnalyzed): void{
		$this->alreadyAnalyzed = $alreadyAnalyzed;
	}

    /**
     * @return bool
     */
	public function needToAnalyze(): bool{
		if($this->alreadyAnalyzed){
			$this->logDebug("alreadyAnalyzed so don't need to analyze...");
			return false;
		}
		$analysisEndedAt = $this->getAnalysisEndedAt();
		$algorithmModified = static::getAlgorithmModifiedAt();
		if(strtotime($algorithmModified) > strtotime($analysisEndedAt)){
			$this->logInfo("Analyzing because algorithm was modified $algorithmModified and was last analyzed $analysisEndedAt");
			return true;
		}
		if($this->isAnalyzing()){
			$started = $this->getAnalysisStartedAt();
			$secondsAgo = time() - strtotime($started);
			if($secondsAgo < 300){
				$this->logError("Already started analyzing $secondsAgo seconds ago so shouldn't need to analyze...");
				//return false;
			}
		}
		if(!$analysisEndedAt){
			$this->logInfo("Analyzing because analysis_ended_at is null...");
			return true;
		}
		$newestDataAt = $this->getNewestDataAt();
		if(strtotime($newestDataAt) > strtotime($analysisEndedAt)){
			$this->logInfo("Analyzing because newest data is $newestDataAt and was last analyzed $analysisEndedAt");
			return true;
		}
		$settingsModifiedAt = $this->getAnalysisSettingsModifiedAt();
		if($settingsModifiedAt &&
		   $analysisEndedAt &&
		   strtotime($settingsModifiedAt) > strtotime($analysisEndedAt)){
			$this->logInfo("Analyzing because the settings were modified $settingsModifiedAt and was last analyzed $analysisEndedAt");
			return true;
		}
		$field = $this->requiredAnalysisFieldIsNull(false);
		if($field){
			$this->logInfo("Analyzing because $field is null");
			return true;
		}
		return false;
	}
	/**
	 * @return string
	 */
	abstract public function getNewestDataAt(): ?string;
	/**
	 * @return string|null
	 */
	public function getAnalysisSettingsModifiedAt(): ?string{
		if($this->analysisSettingsModifiedAt){
			return $this->analysisSettingsModifiedAt;
		}
		$settingsModifiedAt = [];
		$sources = $this->getSourceObjects();
		if(!$sources){
			return $this->analysisSettingsModifiedAt;
		}
		foreach($sources as $source){
			if(!property_exists($source, 'analysisSettingsModifiedAt')){
				return $this->analysisSettingsModifiedAt;
			}
			if(isset($source->analysisSettingsModifiedAt)){
				$settingsModifiedAt[] = $source->analysisSettingsModifiedAt;
			}
		}
		if(!$settingsModifiedAt){return null;}
		return max($settingsModifiedAt);
	}
	/**
	 * @return int
	 */
	public function getMaxAgeInSeconds(): int{
		$lastModified = static::getAlgorithmModifiedAt();
		$maxAgeInSeconds = time() - strtotime($lastModified);
		$secondsSinceNewest = $this->getSecondsSinceNewestData();
		if($secondsSinceNewest !== null && $secondsSinceNewest < $maxAgeInSeconds){
			$maxAgeInSeconds = $secondsSinceNewest;
		}
		$secondsSinceSettings = $this->getSecondsSinceAnalysisSettingsModifiedAt();
		if($secondsSinceSettings !== null && $secondsSinceSettings < $maxAgeInSeconds){
			$maxAgeInSeconds = $secondsSinceSettings;
		}
		return $maxAgeInSeconds;
	}
	/**
	 * @return QMAnalyzableTrait[]
	 */
	abstract public function getSourceObjects(): array;
	/**
	 * @return string
	 */
	public function getTimeSinceLastAnalyzedHumanString(): string{
		if(!isset($this->analysisEndedAt)){
			return "never";
		}
		$at = $this->analysisEndedAt;
		$secondsAgo = TimeHelper::secondsAgo($at);
		$str = TimeHelper::timeSinceHumanString($at);
		if($secondsAgo < 2){
			$this->logError($this . ": " . $str);
		}
		return $str;
	}
	abstract public function __toString();
	/**
	 * @return string
	 */
	public function setAnalysisEndedAtAndStatusUpdated(): string{
		$this->setAlreadyAnalyzed(true);
		$this->status = UserStatusProperty::STATUS_UPDATED;
		return $this->updatedAt = $this->setAnalysisEndedAt(now_at());
	}
	/**
	 * @param string $at
	 * @return string
	 */
	public function setAnalysisEndedAt(string $at): string{
		$start = strtotime($this->getAnalysisStartedAt());
		$duration = strtotime($at) - $start;
		if($duration > 1){
			QMClockwork::logDuration("$this ".static::getClassNameTitle(). "Analysis", $start,
				$at);
		}
		return $this->analysisEndedAt = $at;
	}
	/**
	 * @return int|null
	 */
	public function getSecondsSinceNewestData(): ?int{
		$at = $this->getNewestDataAt();
		if(!$at){
			return null;
		}
		return time() - strtotime($at);
	}
	/**
	 * @return int|null
	 */
	public function getSecondsSinceAnalysisSettingsModifiedAt(): ?int{
		$at = $this->getAnalysisSettingsModifiedAt();
		if(!$at){
			return null;
		}
		return time() - strtotime($at);
	}
	/**
	 * @param bool $updateDb
	 * @param string $reason
	 * @return string
	 */
	public function setAnalysisSettingsModifiedAt(bool $updateDb, string $reason): string{
		$at = now_at();
		if($updateDb){
			$this->updateDbRow([
				static::FIELD_ANALYSIS_SETTINGS_MODIFIED_AT => $at,
				static::FIELD_REASON_FOR_ANALYSIS => QMStr::truncate($reason, 254),
				static::FIELD_STATUS => UserStatusProperty::STATUS_WAITING,
			]);
		} else{
			$this->analysisSettingsModifiedAt = $at;
		}
		return $at;
	}
	/**
	 * @return int|null
	 */
	public function getMinutesSinceLastAnalysis(): ?int{
		$at = $this->getAnalysisEndedAt();
		if(!$at){
			return null;
		}
		return TimeHelper::minutesAgo($at);
	}
	/**
	 * @return bool
	 */
	public function lastAnalysisInLastHour(): bool{
		if(!isset($this->analysisEndedAt)){
			return false;
		}
		return strtotime($this->analysisEndedAt) > time() - 60 * 60;
	}
	/**
	 * @return string
	 */
	public function getTimeSinceAnalysisEndedAt(): string{
		$time = $this->getAnalysisEndedAt();
		return TimeHelper::timeSinceHumanString($time);
	}
	/**
	 * @return bool
	 */
	public function isAnalyzing(): bool{
		return $this->status === UserVariableStatusProperty::STATUS_ANALYZING;
	}
	/**
	 * @param string $reason
	 * @return void
	 * @throws TooSlowToAnalyzeException|NotEnoughDataException
	 */
	protected function analyzeIfNecessaryAndNotApiRequest(string $reason){
		if(AppMode::isApiRequest()){
			return;
		}
		if($this->getStatus() === UserStatusProperty::STATUS_ANALYZING){
			$this->logInfo("Already started analyzing " . TimeHelper::timeSinceHumanString($this->getUpdatedAt()));
			return;
		}
		$this->analyzeFullyIfNecessary($reason);
	}
	/**
	 * @param string $reason
	 * @return void
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeIfNecessary(string $reason){
		$this->analyzeFullyIfNecessary($reason);
	}
	/**
	 * @param string $reason
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeFullyIfNecessary(string $reason): void{
		if($this->needToAnalyze()){
			try {
				$this->analyzeFully($reason);
			} catch (AlreadyAnalyzingException | AlreadyAnalyzedException | NotEnoughDataException $e) {
				$this->logInfo($e->getMessage() . "\n" . $this->getAnalyzeUrl());
			} catch (AnalysisException | StupidVariableNameException $e) {
				$this->logError($e->getMessage() . "\n" . $this->getAnalyzeUrl());
				le($e);
			}
		} else{
			$this->logDebug("Don't need to analyze $this");
		}
	}
	/**
	 * @param string $reason
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeFullyIfNecessaryAndSave(string $reason): void{
		$needToAnalyze = $this->needToAnalyze();
		if($needToAnalyze){
			try {
				$this->analyzeFullyAndSave($reason);
			} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
				$this->logInfo(__METHOD__.": ".$e->getMessage());
			} catch (DuplicateFailedAnalysisException | ModelValidationException | StupidVariableNameException $e) {
				le($e);
			}
		} else{
			$this->logDebug("Don't need to analyze $this");
		}
	}
	/**
	 * @return QMQB
	 */
	public static function whereAlgorithmOutdated(): QMQB{
		$t = static::TABLE;
		$ended = $t . '.' . static::FIELD_ANALYSIS_ENDED_AT;
		$started = $t . '.' . static::FIELD_ANALYSIS_STARTED_AT;
		/** @var QMQB $qb */
		//$qb = static::where($ended, "<", static::getAlgorithmModifiedAt()); I think we should use $started or we keep retrying variables without enough data
		$qb = static::where($started, "<", static::getAlgorithmModifiedAt());
		if(self::$ANALYZE_STUCK_SEPARATELY){
			$qb = $qb->whereRaw($started . " < " . $ended); // Don't duplicate getting stuck variables
		}
		$qb->whereNotNull($ended);
		$qb->whereNotNull($started);
		$qb->orderBy($started, 'asc');
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function whereAnalysisStale(): QMQB{
		$t = static::TABLE;
		$ended = $t . '.' . static::FIELD_ANALYSIS_ENDED_AT;
		$started = $t . '.' . static::FIELD_ANALYSIS_STARTED_AT;
		$newest = $t . '.' . static::FIELD_NEWEST_DATA_AT;
		/** @var QMQB $qb */
		if(self::$ANALYZE_STUCK_SEPARATELY){
			$qb = static::whereRaw($ended . " < " . $newest); // Don't use started_at or we could duplicate analyzeStuck
		} else{
			$qb = static::whereRaw($started . " < " . $newest); // Using started_at makes analyzeStuck unnecessary
		}
		$qb->whereNotNull($newest);
		$qb->whereNotNull($ended);
		$qb->whereNotNull($started);
		$qb->orderBy($started, 'asc');
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function whereWaiting(): QMQB{
		$t = static::TABLE;
		$started = $t . '.' . static::FIELD_ANALYSIS_STARTED_AT;
		/** @var QMQB $qb */
		$qb = static::where($t . '.' . static::FIELD_STATUS, UserStatusProperty::STATUS_WAITING);
		$qb->orderBy($started, 'asc');
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function whereNeverStartedAnalyzing(): QMQB{
		$t = static::TABLE;
		/** @var QMQB $qb */
		$qb = static::whereNull($t . '.' . static::FIELD_ANALYSIS_STARTED_AT);
		$qb->whereNull($t . '.' . static::FIELD_DELETED_AT);
		$qb->orderBy($t . '.' . static::FIELD_UPDATED_AT, 'asc');
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function whereNeverFinishedAnalyzing(): QMQB{
		$t = static::TABLE;
		/** @var QMQB $qb */
		$qb = static::whereNull($t . '.' . static::FIELD_ANALYSIS_ENDED_AT);
		$qb->whereNull($t . '.' . static::FIELD_DELETED_AT);
		$qb->orderBy($t . '.' . static::FIELD_UPDATED_AT, 'asc');
		return $qb;
	}
	/**
	 * @return QMQB
	 */
	public static function whereStuck(): QMQB{
		$t = static::TABLE;
		$ended = $t . '.' . static::FIELD_ANALYSIS_ENDED_AT;
		$started = $t . '.' . static::FIELD_ANALYSIS_STARTED_AT;
		$qb = Writable::getTableStatic($t);
		//$qb->where($started, "<", db_date(time() - 86400));
		$qb->whereNotNull($ended);   // Don't duplicate never finished
		$qb->whereNotNull($started); // Don't duplicate never analyzed
		$qb->whereRaw($started . ' > ' . $ended);
		$qb->orderBy($started, 'asc');
		return $qb;
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeStale(array $wheres = null): array{
		$qb = static::whereAnalysisStale();
		return static::analyzeByQuery($qb, "STALE: analysis_ended_at before " . static::FIELD_NEWEST_DATA_AT, $wheres);
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeWhereAlgorithmOutdated(array $wheres = null): array{
		$at = static::getAlgorithmModifiedAt();
		$qb = static::whereAlgorithmOutdated();
		return static::analyzeByQuery($qb,
			"Algorithmically STALE: analysis_ended_at before Algorithm Modified At " . $at, $wheres);
	}
	public static function getAlgorithmModifiedAt(): string{
		$at = static::ALGORITHM_MODIFIED_AT;
		if(!$at){
			le("Please set ALGORITHM_MODIFIED_AT for " . static::class);
		}
		return db_date($at);
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeNeverStarted(array $wheres = null): array{
		$qb = static::whereNeverStartedAnalyzing();
		return static::analyzeByQuery($qb, "NEVER ANALYZED: analysis_started_at is null ", $wheres);
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeNeverFinished(array $wheres = null): array{
		$qb = static::whereNeverFinishedAnalyzing();
		return static::analyzeByQuery($qb, "NEVER FINISHED: analysis_ended_at is null ", $wheres);
	}
	public static function analyzeNeverAnalyzedUntilComplete(){
		$qb = static::whereNeverStartedAnalyzing();
		static::excludeUnAnalyzableUsers($qb);
		$qb->whereNull(static::TABLE . '.' . static::FIELD_DELETED_AT);
		while($count = $qb->count()){
			JobTestCase::resetStartTime();
			$title = QMStr::tableToTitle(static::TABLE);
			$m = " $count $title NEVER ANALYZED";
			JobTestCase::slack($m);
			try {
				static::analyzeNeverStarted();
			} catch (InsufficientMemoryException $e) {
				le($e);
				throw new \LogicException();
			}
		}
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeStuck(array $wheres = null): array{
		return static::analyzeByQuery(static::whereStuck(),
			"STUCK: analysis_started_at more than a day ago and analysis_ended_at before it started", $wheres);
	}
	/**
	 * @param QMQB $qb
	 * @param string $reason
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeByQuery(QMQB $qb, string $reason, array $wheres = null): array{
		$t = static::TABLE;
		self::excludeUnAnalyzableUsers($qb);
		if($wheres){
			$qb->addWheres($wheres);
		}
		//Why? That's dangerous!  $qb->logAdminerUrl();
		//$plucked = $qb->pluck(static::TABLE.'.id'); // Allowed memory size of 2147483648 bytes exhausted
		/** @var BaseModel $lClass */
		$lClass = static::getLaravelClassName();
		$analyzed = [];
		if(AppMode::isDebug()){$total = $qb->countAndLog();} else {$total = $qb->count();}
		while($one = $qb->limit(1)->pluck('id')->first()){
			ConsoleLog::info($qb->count() . " " . $t . " where $reason");
			/** @var Variable|UserVariable|UserVariableRelationship|GlobalVariableRelationship|User $l */
			$l = $lClass::findInMemoryOrDB($one);
			$started = $l->analysis_started_at;
			$newest = $l->newest_data_at;
			if(stripos($reason, 'stale') !== false && $started > $newest && $started > static::ALGORITHM_MODIFIED_AT){
				$l->logError("$l not stale!");
				continue;
			}
			$model = $l->getDBModel();
			try {
				$model->logStartOfAnalysis($reason);
				$model->analyzeFullyAndSave($reason);
			} catch (AlreadyAnalyzingException $e) {
				le("Fix me so I don't get " . static::TABLE . " already being analyzed! " . $e->getMessage());
			} catch (AlreadyAnalyzedException $e) {
				$model->logInfo(__METHOD__.": ".$e->getMessage());
				continue;
			} /** @noinspection PhpRedundantCatchClauseInspection */ catch (StupidVariableException $e) {
				$model->logError(__METHOD__.": ".$e->getMessage());
				continue;
			} catch (NotEnoughDataException $e) {
				$model->logInfo($model->getPHPUnitTestUrl());
				//Handler::logExceptionOrThrowIfLocalOrPHPUnitTest($e);
				continue;
			} catch (Throwable $e) {
				$model->logInfo($model->getPHPUnitTestUrl());
				/** @var RuntimeException $e */
				throw $e;
			}
			$analyzed[] = $model;
			if(!$model->getAnalysisEndedAt()){
				le("analysisEndedAt should have been set on $model");
			}
			if(JobTestCase::jobDurationExceedsTimeLimit()){
				ConsoleLog::info("Breaking " . __FUNCTION__ . " due to time limit");
				break;
			}
			if(static::class === QMUser::class){
				sleep(1);  // Needed for testing because we update newest_data_at on GLOBAL variable relationship after analyzing 2nd USER correlation
			}
		}
		return $analyzed;
	}
	/**
	 * @param AnalysisException|StupidVariableNameException|Throwable $e
	 * @throws DuplicateFailedAnalysisException
	 */
	public function setErroredInDB(Throwable $e){
		if(!$this->id){
			$this->logInfo("Can't save error in DB because we don't have an ID yet. Exception: " . $e->getMessage());
			return;
		}
		$userError = $e->getUserErrorMessageHtml();
		$previousStatus = $this->l()->status;
		$newStatus = CorrelationStatusProperty::STATUS_ERROR;
		if($previousStatus === $newStatus){
			throw new DuplicateFailedAnalysisException($this, $e);
		}
		if(AppMode::isUnitOrStagingUnitTest() && !ExceptionHandler::isExpectedException($e)){
			le($e);
		}
        try {
            $url = QMIgnition::getUrlOrGenerateAndOpen($e);
            $message = QMStr::truncate($e->getMessage(), 140);
            $tooltip = QMStr::truncate($e->getMessage(), 500);
            $ignitionLink = HtmlHelper::generateLink($message, $url, true, $tooltip);
        } catch (\Throwable $ignitionException) {
            ConsoleLog::error("Could not generate Ignition link because of " . $ignitionException->getMessage());
            $ignitionLink = $e->getMessage();
        }
		$this->updateDbRow([
			UserVariableRelationship::FIELD_USER_ERROR_MESSAGE => $userError,
			UserVariableRelationship::FIELD_INTERNAL_ERROR_MESSAGE => $ignitionLink,
			UserVariableRelationship::FIELD_STATUS => CorrelationStatusProperty::STATUS_ERROR,
			UserVariableRelationship::UPDATED_AT => now_at(),
		]);
	}

    /**
     * @param string $reason
     * @throws DuplicateFailedAnalysisException
     * @throws ModelValidationException
     * @throws NotEnoughDataException
     * @throws StupidVariableNameException
     * @throws TooSlowToAnalyzeException
     */
	public function analyzeFullyAndSave(string $reason){
		$this->setAnalysisStartedAt(now_at());
		$this->assertAttributeNotNull(BaseAnalysisStartedAtProperty::NAME);
		try {
			$this->analyzeFully($reason);
			$this->assertAttributeNotNull(BaseAnalysisStartedAtProperty::NAME);
		} catch (AlreadyAnalyzingException | AlreadyAnalyzedException $e) {
            $this->logInfo(__METHOD__.": ".$e->getMessage());
            return;
			//le($e);
		} catch (NotEnoughDataException $e) {
			$this->setErroredInDB($e);
			$this->logInfo($e);
			throw $e;
		} catch (StupidVariableNameException $e) {
			$this->setErroredInDB($e);
			$this->logError($e);
			throw $e;
		} catch (AnalysisException $analysisException) {
			$this->logError($analysisException->getMessage());
			$m = $analysisException->getMessage();
			$m = QMStr::truncate($m, 254); // Max length for column
			$this->setUserAndInternalErrorMessage($m);
			try {
				$this->save();
			} catch (Throwable $saveException) {
				$this->logInfo("Couldn't save error message from analyzeFully because: " .
					$saveException->getMessage());
			}
			/** @var RuntimeException $analysisException */
			throw $analysisException;
		}
		$this->validateAnalysisBeforeSave();
		$this->setAnalysisEndedAtAndStatusUpdated();
		$this->save();
	}
	/**
	 * @param array|null $wheres
	 * @return static[]
	 */
	public static function analyzeWaitingStaleStuck(array $wheres = null): array{
		JobTestCase::setStartTimeIfNecessary();
		if(!$wheres){
			static::analyzeGloballyIfNecessary();
		}
		$models = [];
		//$models = array_merge($models, static::analyzeWaiting($slackIt, $wheres));  // Too complicated to make sure we aren't incorrectly setting waiting everywhere
		$models = array_merge($models, static::analyzeNeverStarted($wheres));
		$models = array_merge($models, static::analyzeStale($wheres));
		$models = array_merge($models, static::analyzeWhereAlgorithmOutdated($wheres));
		if(self::$ANALYZE_STUCK_SEPARATELY){
			$models = array_merge($models, static::analyzeStuck($wheres));
		}
		return $models;
	}
	/**
	 * @param string $reason
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 * @throws StupidVariableNameException
	 */
	public function analyze(string $reason): void{
		try {
			$this->analyzeFullyAndSave($reason);
		} catch (ModelValidationException $e) {
			le($e);
		}
	}
	/**
	 * @param int $userId
	 * @param string|null $tableWithUserId
	 * @return static[]
	 */
	public static function analyzeWaitingStaleStuckForUser(int $userId, string $tableWithUserId = null): array{
		if(!$tableWithUserId){
			$tableWithUserId = static::TABLE;
		}
		return static::analyzeWaitingStaleStuck([
			$tableWithUserId . '.' . static::FIELD_USER_ID => $userId,
		]);
	}
	public function getSourceDataUrl(): string{
		return $this->getUrl();
	}
	public static function createWhereNecessary(){
		ConsoleLog::info(__METHOD__ . " not implemented...");
	}
	/**
	 * @param int $maxDuration
	 * @return QMAnalyzableTrait[]
	 */
	public static function analysisJobsTest(int $maxDuration = 1): array{
		ConsoleLog::info(static::TABLE . " " . __FUNCTION__ .
			" calling createWhereNecessary with max duration $maxDuration seconds...");
		static::createWhereNecessary();
		ConsoleLog::info(static::TABLE . " " . __FUNCTION__ .
			" calling analyzeGloballyIfNecessary...");
		static::analyzeGloballyIfNecessary();
		JobTestCase::setMaximumJobDuration($maxDuration);
		JobTestCase::resetStartTime();
		$waiting = [];
		//$waiting = static::analyzeWaiting(false); // Too complicated to make sure we aren't incorrectly setting waiting everywhere
		JobTestCase::resetStartTime();
		$new = static::analyzeNeverStarted();
		JobTestCase::resetStartTime();
		$stale = static::analyzeStale();
		JobTestCase::resetStartTime();
		$algorithm = static::analyzeWhereAlgorithmOutdated();
		if(self::$ANALYZE_STUCK_SEPARATELY){
			JobTestCase::resetStartTime();
			$stuck = static::analyzeStuck();
		} else{
			$stuck = [];
		}
		JobTestCase::resetStartTime();
		// TODO: uncomment $neverFinished = static::analyzeNeverFinished(false);
		$neverFinished = [];
		return array_merge($waiting, $new, $stale, $stuck, $neverFinished, $algorithm);
	}
	/**
	 * @return int
	 */
	public static function getNumberAnalyzedInLastDay(): int{
		$count =
			static::qb()->whereRaw(static::TABLE . '.' . static::FIELD_ANALYSIS_ENDED_AT, ">", Carbon::now()->subDay())
				->count();
		return $count;
	}
	/**
	 * @return static|DBModel
	 */
	public static function getMostRecentlyAnalyzed(): DBModel{
		/** @var static $model */
		$model = static::qb()->orderBy(static::TABLE . '.' . static::FIELD_ANALYSIS_ENDED_AT, 'DESC')->first();
		$model->logInfo(static::FIELD_ANALYSIS_ENDED_AT . ": " . $model->getAnalysisEndedAt());
		return $model;
	}
	/**
	 * @return int
	 */
	public static function logNumberAnalyzedInLastDay(): int{
		$numberUpdated = static::getNumberAnalyzedInLastDay();
		QMLog::info($numberUpdated . " " . (new \ReflectionClass(static::class))->getShortName() .
			"s analyzed in last 24 hours");
		return $numberUpdated;
	}
	/**
	 * @param int $epochSecondsUnixTime
	 * @return int
	 */
	public static function numberAnalyzedSince(int $epochSecondsUnixTime): int{
		return static::qb()->where(static::TABLE . '.' . static::FIELD_ANALYSIS_ENDED_AT, '>',
			db_date($epochSecondsUnixTime))->count();
	}

    /**
     * @return string
     * @throws NotEnoughDataException
     */
	public function calculateNewestDataAt(): string{
		$newest = null;
		$arr = $this->getSourceObjects();
		foreach($arr as $item){
			if(method_exists($item, 'calculateNewestDataAt')){
				$at = $item->calculateNewestDataAt();
			} elseif(isset($item->updatedAt)){ // Don't use startTime or we'll never analyze old imported data
				$at = $item->updatedAt;
			} elseif(isset($item->startTime)){ // Don't use startTime or we'll never analyze old imported data
				$at = db_date($item->startTime);
			}
			if(!isset($at)){
				le("No updatedAt or calculateNewestDataAt on: " . \App\Logging\QMLog::print_r($item, true));
			}
			if($at > $newest){
				$newest = $at;
			}
		}
		if(!$newest){
			le("No newestDataAt!");
		}
		return $this->newestDataAt = $newest;
	}
	private function setValuesBeforeAnalysis(){
		if(!$this->id){
			return;
		}
		$clone = clone $this; // Messes it up for some reason when we get laravel model so I clone here
		/** @var BaseModel $l */
		$l = $clone->l();
		$this->valuesBeforeAnalysis = $l->attributesToArray();
	}
	/**
	 * @param string|null $reason
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 */
	public function beforeAnalysis(string $reason): void{
		if($this->id === true){
			le('$this->id === true');
		}
		if($this->alreadyAnalyzed){ // This happens when we correlate lots of variables
			$this->logInfo("Already analyzed $this instance! AnalysisEndedAt: " . $this->getTimeSinceAnalysisEndedAt());
			throw new AlreadyAnalyzedException($this);
		}
		$min = $this->getMinutesSinceLastAnalysis();
		if($min !== null && $this->isAnalyzing() && $min < 5 && !XDebug::active()){
			throw new AlreadyAnalyzingException($this);
		}
		Memory::setStartTime();
		$this->setValuesBeforeAnalysis();
		$this->logStartOfAnalysis($reason);
		$this->setUserAndInternalErrorMessage(null);
		if($this->hasId()){
			$arr = $this->calculateInterestingNumberOfRelationCounts();
			$arr = array_merge($arr, [
				static::FIELD_STATUS => UserStatusProperty::STATUS_ANALYZING,
				static::FIELD_REASON_FOR_ANALYSIS => QMStr::truncate($reason, 254),
				static::FIELD_ANALYSIS_STARTED_AT => now_at(),
			]);
			$this->updateDbRow($arr, $reason);
		} else{
			$this->setReasonForAnalysis($reason);
			$this->setAnalysisStartedAt(now_at());
			$this->setStatus(UserStatusProperty::STATUS_ANALYZING);
		}
	}
	/**
	 * @return string
	 */
	public function getStatus(): string{
		return $this->status;
	}
	/**
	 * @param string $status
	 */
	public function setStatus(string $status): void{
		/** @var UserVariableRelationship $l */
		$l = $this->l();
		$l->status = $this->status = $status;
		$this->logInfo("Setting status to $status");
	}
	/**
	 * @param string $reason
	 */
	protected function logStartOfAnalysis(string $reason): void{
		$since = $this->getTimeSinceAnalysisEndedAt();
		$m = "Analyzing because: $reason | Last analysis: $since";
		if(!AppMode::isAnyKindOfUnitTest() || AppMode::isStagingUnitTesting()){
			$m .= " | PHPUnit Analysis => " . $this->getPHPUnitTestUrl();
		}
		GlobalLogMeta::addCustomGlobalMetaData($this->__toString() . " PHPUnit Test", $this->getPHPUnitTestUrl());
		GlobalLogMeta::addCustomGlobalMetaData($this->__toString() . " AnalyzeUrl", $this->getAnalyzeUrl());
		$m .= "\nBrowser Analysis @ " . $this->getDebugUrl() . "\n";
		if($this->lastAnalysisInLastHour()){
			$m = "Last analysis was $since! $m";
			$this->logErrorOrInfoIfTesting($m);
		} else{
			$this->logInfoWithoutObfuscation($m);
		}
		GoogleAnalyticsEvent::logEventToGoogleAnalytics(static::TABLE, 'analyzed-' . static::TABLE, 1,
			ObjectHelper::get($this, 'userId'), $this->clientId, $this);
	}
	/**
	 * @return string
	 */
	public function getDebugUrl(): string{
		$url = $this->getAnalyzeUrl();
		return UrlHelper::convertProductionToDevelopmentUrl($url);
	}
	/**
	 * @param array $params
	 * @return string|null
	 */
	public function getAnalyzeUrl(array $params = []): string{
		$params[QMRequest::PARAM_ANALYZE] = true;
		return $this->getUrl($params);
	}
	/**
	 * @throws ModelValidationException
	 * @noinspection PhpDocRedundantThrowsInspection
	 */
	public function validateAnalysisBeforeSave(){
		$this->requiredAnalysisFieldIsNull(true);
	}
	protected static function getRequiredAnalysisFields():array{return [];}
	protected function requiredAnalysisFieldIsNull(bool $exception): ?string{
		$required = static::getRequiredAnalysisFields();
		$required[] = UserVariableRelationship::FIELD_ANALYSIS_STARTED_AT;
		foreach($required as $field){
			$value = $this->getAttribute($field);
			if($value === null){
				if($exception){
					$this->assertAttributeNotNull($field);
				}
				return $field;
			}
		}
		return null;
	}
	protected function assertAttributeNotNull(string $field): void{
		$value = $this->getAttribute($field);
		if($value === null){
			$this->getAttribute($field);
			/** @var BaseModel $l */
			$l = $this->firstOrNewLaravelModel();
			$value = $l->getAttribute($field);
			le("this->getAttribute($field) is null in  " . __METHOD__ . "! l->getAttribute($field) is: " .
				\App\Logging\QMLog::print_r($value, true), [
				'attributes' => $this->attributesToArray(),
				static::class . '_getPHPUnitTestUrl' => $this->getPHPUnitTestUrl(),
			]);
		}
	}
	/**
	 * @param string $reason
	 * @throws AlreadyAnalyzedException
	 * @throws AlreadyAnalyzingException
	 * @throws DuplicateFailedAnalysisException
	 * @throws ModelValidationException
	 * @throws NotEnoughDataException
	 * @throws StupidVariableNameException
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeSourceObjects(string $reason){
		$sources = $this->getSourceObjects();
		$total = count($sources);
		$i = 0;
		foreach($sources as $source){
			$i++;
			ConsoleLog::info("=== Analyzed $i of $total ===");
			$source->analyzeFullyAndSave($reason);
		}
		$this->analyzeFullyAndSave($reason);
	}
	public static function analyzeGlobal(){
		$table = static::TABLE;
		$idField = static::FIELD_ID;
		$viewsByName = [];
		$formulas = static::getSqlCalculatedFields();
		foreach($formulas as $field => $arr){
			$sql = $arr['sql'] ?? null;
			$viewSourceTable = $arr['table'] ?? null;
			$duration = $arr['duration'] ?? null;
			if($duration > QMQB::MAX_WORKER_QUERY_DURATION){
				ConsoleLog::info("Not updating $field because it's too slow: " .
					\App\Logging\QMLog::print_r($arr, true));
				continue;
			}
			if(stripos($sql, "update $table") === 0){
				Writable::statementStatic($sql);
			} elseif(stripos($sql, "select ") === 0){
				ConsoleLog::info("Select calculations not yet implemented. Provided arr: " .
					\App\Logging\QMLog::print_r($arr, true));
			} elseif($viewSourceTable){
				$viewName = SQLView::generateName($viewSourceTable, $arr['foreign_key']);
				$v = $viewsByName[$viewName] ?? new SQLView($arr, $table, $idField);
				$v->columns[] = $sql . " as $field";
				$v->fields[] = $field;
				$viewsByName[$viewName] = $v;
			} else{
				QMLog::debug("Formula not set for $field global calculation. Provided arr: " . \App\Logging\QMLog::print_r($arr, true));
			}
		}
		/** @var SQLView $viewData */
		foreach($viewsByName as $viewName => $viewData){
            try {
                $viewData->executeUpdate();
            } catch (\Throwable $e) {
                ConsoleLog::warning(__METHOD__.": ".$e->getMessage());
            }
		}
	}
	/**
	 * @param string $reason
	 */
	public function setReasonForAnalysis(string $reason){
		/** @var UserVariableRelationship $l */
		$l = $this->l();
		$l->reason_for_analysis = $this->reasonForAnalysis = QMStr::truncate($reason, 254);
	}
	/**
	 * @param string $reason
	 * @return PendingDispatch
	 */
	public function queue(string $reason): ?PendingDispatch{
		if(AnalyzeJob::alreadyQueued($this)){
			return null;
		}
		$this->saveAnalysisStatus($reason);
		return AnalyzeJob::queueModel($this->l(), $reason);
	}
	/**
	 * @param int $hours
	 * @return mixed
	 */
	public function analyzedInLast(int $hours = 24): bool{
		return strtotime($this->analysisStartedAt) > time() - $hours * 60 * 60;
	}
	public static function getMigrationStatement(string $table): string{
		return "
            alter table $table add analysis_ended_at timestamp null;
            alter table $table add analysis_requested_at timestamp null;
            alter table $table add analysis_started_at timestamp null;
            alter table $table add internal_error_message varchar(255);
            alter table $table add newest_data_at timestamp null;
            alter table $table add reason_for_analysis varchar(255) null;
            alter table $table add user_error_message varchar(255);
            alter table $table add status varchar(25);
            alter table $table add analysis_settings_modified_at timestamp null;
        ";
	}
	public function updateNewestDataAt(){
		$this->setAlreadyAnalyzed(false);
		$this->status = UserStatusProperty::STATUS_WAITING;
		$this->updateDbRow([
			static::FIELD_NEWEST_DATA_AT => now_at(),
			static::FIELD_STATUS => UserStatusProperty::STATUS_WAITING,
		]);
	}
	abstract public function cleanup();
	public function getIdParams(): array{
		return [
			QMStr::singularize(static::TABLE) . "_id" => $this->getId(),
		];
	}
	protected function sendAnalysisCompletedNotification(){
		$u = User::mike();
		$u->notify(new AnalysisCompletedNotification($this->l()));
	}
	/**
	 * @param string $column
	 * @param $value
	 * @param string $reason
	 * @param array $params
	 * @return int
	 */
	public static function scheduleAnalysisWhere(string $column, $value, string $reason, array $params = []): int{
		if(!isset($params[Variable::FIELD_STATUS])){
			$params[Variable::FIELD_STATUS] = UserVariableStatusProperty::STATUS_WAITING;
		}
		$params[Variable::FIELD_REASON_FOR_ANALYSIS] = $reason;
		$params[Variable::FIELD_ANALYSIS_REQUESTED_AT] = $params[Variable::FIELD_NEWEST_DATA_AT] = now_at();
		/** @var DBModel $class */
		$class = static::class;
		return $class::writable()->where($column, $value)->update($params);
	}
	public function getUrls(array $params = []): array{
		return [
			'View ' . $this->getTitleAttribute() => $this->getUrl($params),
			'Analyze ' . $this->getTitleAttribute() => $this->getAnalyzeUrl($params),
			$this->getTitleAttribute() . " PHPUnit Test" => $this->getPHPUnitTestUrl(),
		];
	}
	public function getProfileUrl(array $params = []): string{
		return $this->getDataLabProfileButton($params)->getUrl();
	}
	/**
	 * @return void
	 * @noinspection PhpDocMissingThrowsInspection
	 */
	public function test(): void{
		/** @noinspection PhpUnhandledExceptionInspection */
		$this->analyzeFully(__FUNCTION__);
	}
	/**
	 * @return array
	 */
	public function calculateInterestingNumberOfRelationCounts(): array{
		if($this->interestingRelationshipCounts){
			return $this->interestingRelationshipCounts;
		}
		/** @var BaseModel $l */
		$l = $this->l();
		$fields = $l->updateInterestingRelationshipCountFields();
		$this->populateByDbFieldNames($fields, true);
		return $this->interestingRelationshipCounts = $fields;
	}
	/**
	 * @param string $message
	 * @param array $parameters
	 */
	public function addWarning(string $message, array $parameters = []){
		if(!$this->warnings){
			$this->warnings = [];
		}
		if(method_exists($this, 'getHyperParametersSentence')){
			$message .= " " . $this->getHyperParametersSentence();
		}
		if(!in_array($message, $this->warnings, false)){
			$url = ($this->getId()) ? $this->getUrl() : null;
			$this->logInfo($message . "
            " . $url, $parameters);
			$this->warnings[] = $message;
		}
	}
	public function getStatusAndTimeSince(): string{
		$status = $this->status;
		if($status === UserStatusProperty::STATUS_ANALYZING){
			$status .= " (started " . TimeHelper::timeSinceHumanString($this->getAnalysisStartedAt()) . ")";
		}
		if($status === UserStatusProperty::STATUS_WAITING){
			$status .= " (requested " . TimeHelper::timeSinceHumanString($this->getAnalysisRequestedAt()) . ")";
		}
		if($status === UserStatusProperty::STATUS_ERROR){
			$status .= " (failed " . TimeHelper::timeSinceHumanString($this->getAnalysisStartedAt()) . ")";
		}
		return $status;
	}
	public function calculateProperty(string $attribute): float{
		/** @var IsCalculated $p */
		$p = $this->getPropertyModel($attribute);
		return $p->calculate($this);
	}
	/**
	 * @return IsEditable[]
	 */
	abstract public function getInvalidSourceData(): array;
	public function getInvalidSourceDataHtml(): string{
		$invalid = $this->getInvalidSourceData();
		if(!$invalid){
			return "";
		}
		return HtmlHelper::renderView(view('invalid-source-data', [
			'a' => $this,
			'invalid' => $invalid,
		]));
	}
	abstract public function getVariableSettingsLink(): string;
	abstract public function getDBModel(): DBModel;
	abstract public function getUrl(array $params = []): string;
	public function getAnalysisRequestedAt(): ?string{
		return $this->l()->analysis_requested_at;
	}
	public function setAnalysisRequestedAt(string $analysisRequestedAt): void{
		$this->setAttribute(User::FIELD_ANALYSIS_REQUESTED_AT, $analysisRequestedAt);
	}
	public function getAnalysisStartedAt(): ?string{
		return $this->l()->analysis_started_at;
	}
	/**
	 * @return string
	 */
	public function getAnalysisEndedAt(): ?string{
		return $this->l()->analysis_ended_at;
	}
	abstract public function getTitleAttribute(): string;
	public function validateOrReAnalyze(): void{
		try {
			$this->validateAttributes();
		} catch (InvalidAttributeException $e) {
			$this->logError("Re-analyzing because: " . $e->getMessage());
			try {
				$this->analyzeFullyAndSave(__FUNCTION__);
			} catch (\Throwable $e) {
				le("$this: values not valid even after re-correlation! " . $e->getMessage());
			}
		}
	}
	protected function addSolutionLinks(): void{
		SolutionButton::add("Analyze " . $this->getTitleAttribute(), $this->getAnalyzeUrl());
		parent::addSolutionLinks();
	}
	private function assertDBModelValMatchesLaravelValue(string $key){
		if(isset($this->laravelModel)){
			$val = $this->getAttribute($key);
			if(!$val){
				le("Could not getAttribute($key)" . __METHOD__);
			}
			$lVal = $this->l()->getAttribute($key);
			if(!$lVal){
				le("Could not get l()->getAttribute($key)" . __METHOD__);
			}
			Compare::validateSame($val, $lVal, "$key lVal !== val on " . __METHOD__);
		}
	}
}
