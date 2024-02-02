<?php
/*
*  GNU General Public License v3.0
*  Contributors: ADD YOUR NAME HERE, Mike P. Sinn
 */

namespace App\Traits;
use App\Exceptions\AlreadyAnalyzedException;
use App\Exceptions\AlreadyAnalyzingException;
use App\Exceptions\DuplicateFailedAnalysisException;
use App\Exceptions\ModelValidationException;
use App\Exceptions\NotEnoughDataException;
use App\Exceptions\StupidVariableNameException;
use App\Exceptions\TooSlowToAnalyzeException;
use App\Logging\GlobalLogMeta;
use App\Logging\QMLog;
use App\Models\UserVariableRelationship;
use App\Models\User;
use App\Astral\Actions\AnalyzeAction;
use App\Astral\Actions\PHPUnitAction;
use App\Astral\Lenses\FailedAnalysesLens;
use App\Astral\Metrics\AnalysisProgressPartition;
use App\Astral\UserBaseAstralResource;
use App\Properties\Base\BaseAnalysisEndedAtProperty;
use App\Properties\Base\BaseAnalysisStartedAtProperty;
use App\Properties\Base\BaseInternalErrorMessageProperty;
use App\Properties\Base\BaseStatusProperty;
use App\Properties\Base\BaseUserErrorMessageProperty;
use App\Slim\Middleware\QMAuth;
use App\Slim\Model\DBModel;
use App\Slim\Model\GoogleAnalyticsEvent;
use App\Slim\View\Request\QMRequest;
use App\Types\ObjectHelper;
use App\Utils\AppMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Actions\Actionable;
use App\Fields\DateTime;
use App\Fields\Field;
use App\Fields\ID;
use App\Fields\Text;
trait AnalyzableTrait {
	use TestableTrait, Actionable, ChartableTrait;
	public function test(): void {
		$m = $this->getDBModel();
		$m->test();
	}
	/**
	 * @param string $reason
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyze(string $reason){
		$m = $this->getAnalyzable();
		try {
			$m->analyzeFullyAndSave($reason);
		} catch (AlreadyAnalyzedException | AlreadyAnalyzingException | DuplicateFailedAnalysisException | ModelValidationException | StupidVariableNameException $e) {
			le($e);
		}
	}
	/**
	 * @return QMAnalyzableTrait
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	public function getAnalyzable(){
		return $this->getDBModel();
	}
	/**
	 * @param string $reason
	 * @return void
	 * @throws NotEnoughDataException
	 * @throws TooSlowToAnalyzeException
	 */
	public function analyzeFullyAndPostIfNecessary(string $reason){
		try {
			$this->getDBModel()->analyzeFullyAndSave($reason);
		} catch (AlreadyAnalyzedException | StupidVariableNameException | ModelValidationException | DuplicateFailedAnalysisException | AlreadyAnalyzingException $e) {
			le($e);
		}
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
	/**
	 * @return bool
	 */
	public function needToAnalyze(): bool{
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
		$settingsModified = $this->getAnalysisSettingsModifiedAt();
		if(strtotime($settingsModified) > strtotime($analysisEndedAt)){
			$this->logInfo("Analyzing because the settings were modified $settingsModified and was last analyzed $analysisEndedAt");
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
	 * @return QMAnalyzableTrait|DBModel
	 * @noinspection PhpMissingReturnTypeInspection
	 */
	abstract public function getDBModel();
	/**
	 * @return Builder|static
	 */
	public static function whereAnalysisEndedInLastXHours(int $hours){
		return static::where(User::FIELD_ANALYSIS_ENDED_AT, "<", db_date(time() - $hours * 3600));
	}
	/**
	 * @param int $hours
	 * @return int
	 */
	public static function analysisEndedInLastXHours(int $hours): int{
		$analyzedInLast24 = static::whereAnalysisEndedInLastXHours($hours)->count();
		QMLog::info($analyzedInLast24 . " " . static::getClassNameTitle() . " Analysis Ended In Last $hours Hours ");
		return $analyzedInLast24;
	}
	/**
	 * @return Builder|static
	 */
	public static function whereAnalysisStartedInLastXHours(int $hours){
		return static::where(User::FIELD_ANALYSIS_STARTED_AT, "<", db_date(time() - $hours * 3600));
	}
	/**
	 * @param int $hours
	 * @return int
	 */
	public static function analysisStartedInLastXHours(int $hours): int{
		$analyzedInLast24 = static::whereAnalysisStartedInLastXHours($hours)->count();
		QMLog::info($analyzedInLast24 . " " . static::getClassNameTitle() . " Analysis Started In Last $hours Hours ");
		return $analyzedInLast24;
	}
	public function getUrls(array $params = []): array{
		return [
			'View' => $this->getUrl($params),
			'Analyze' => $this->getAnalyzeUrl($params),
			"PHPUnit Test" => $this->getPHPUnitTestUrl(),
		];
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
	 * Get the cards available for the request.
	 * @param Request $request
	 * @return array
	 */
	public function getCards(Request $request): array{
		$cards = parent::getCards($request);
		$cards[] = new AnalysisProgressPartition(UserVariableRelationship::class);
		return $cards;
	}
	/**
	 * Get the lenses available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getLenses(Request $request): array{
		return [
			new FailedAnalysesLens($this),
		];
	}
	/**
	 * Get the actions available for the resource.
	 * @param Request $request
	 * @return array
	 */
	public function getActions(Request $request): array{
		$actions = [
			new AnalyzeAction($request),
		];
		if(QMAuth::isAdmin()){
			$actions[] = new PHPUnitAction($request);
		}
		return $actions;
	}
	/**
	 * @param $request
	 * @return DateTime|Field
	 */
	public function analysisStarted($request): DateTime{
		return BaseAnalysisStartedAtProperty::field(null, null);
	}
	/**
	 * @param $request
	 * @return DateTime|Field
	 */
	public function analysisEnded($request): DateTime{
		return BaseAnalysisEndedAtProperty::field(null, null);
	}
	/**
	 * @param $request
	 * @return Text|Field
	 */
	public function internalError($request): Text{
		return BaseInternalErrorMessageProperty::field(null, null);
	}
	/**
	 * @param $request
	 * @return Text|Field
	 */
	public function userError($request): Text{
		return BaseUserErrorMessageProperty::field(null, null);
	}
	/**
	 * @param array $fields
	 * @param $request
	 * @return array
	 */
	public function analysisFields(array $fields, $request): array{
		$fields[] = $this->status($request);
		$fields[] = $this->analysisStarted($request);
		$fields[] = $this->analysisEnded($request);
		$fields[] = $this->internalError($request);
		$fields[] = $this->userError($request);
		$fields[] = ID::forModel($this)->onlyOnDetail();
		$u = QMAuth::getQMUser();
		if($u->hasPatients()){
			if($this->hasUserIdAttribute()){
				$fields[] = UserBaseAstralResource::belongsTo();
			}
		}
		return $fields;
	}
	/**
	 * @param $request
	 * @return Field
	 */
	public function status($request = null): Field{
		return BaseStatusProperty::field(null, null);
	}
	/**
	 * @param string $reason
	 */
	protected function logStartOfAnalysis(string $reason): void{
		$since = $this->getTimeSinceAnalysisEndedAt();
		$m = "Analyzing because: $reason Last analysis: $since";
		if(!AppMode::isAnyKindOfUnitTest() || AppMode::isStagingUnitTesting()){
			$m .= "\nPHPUnit Analysis => " . $this->getPHPUnitTestUrl() . "\n";
		}
		GlobalLogMeta::addCustomGlobalMetaData($this->__toString() . " PHPUnit Test", $this->getPHPUnitTestUrl());
		GlobalLogMeta::addCustomGlobalMetaData($this->__toString() . " AnalyzeUrl", $this->getAnalyzeUrl());
		$m .= "\nBrowser Analysis @ " . $this->getDebugUrl() . "\n";
		if($this->lastAnalysisInLastHour()){
			$m = "Last analysis was $since!\n$m";
			$this->logErrorOrInfoIfTesting($m);
		} else{
			$this->logInfoWithoutObfuscation($m);
		}
		GoogleAnalyticsEvent::logEventToGoogleAnalytics(static::TABLE, 'analyzed-' . static::TABLE, 1,
		                                                ObjectHelper::get($this, 'userId'), $this->clientId, $this);
	}
}
